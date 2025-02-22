<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
// 勤怠登録画面表示（一般ユーザ）
    public function index() {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d'); // 今日の日付

        // 本日の勤務データを取得（ない場合は null）
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('work_date', $today)
                                ->first();

        $alreadyClockedIn = $attendance && $attendance->clock_in !== null;

        return view('attendance.create', compact('user', 'attendance', 'alreadyClockedIn'));
    }
    public function store(Request $request) {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');

        // 打刻処理
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['clock_in' => null, 'clock_end' => null, 'status' => '勤務外'],  // デフォルト値
        );
        // すでに出勤済みなら「出勤」を禁止
        if ($request->status === '出勤中' && $attendance->clock_in !== null) {
            return redirect()->back()->with('error', 'すでに出勤済みです。');
        }

        // ステータスごとの処理を定義
        $statusActions = [
            '出勤'   => [$this, 'handleClockIn'],
            '休憩入' => [$this, 'handleBreakStart'],
            '休憩戻' => [$this, 'handleBreakEnd'],
            '退勤'   => [$this, 'handleClockEnd']
        ];

        if (isset($statusActions[$request->status])) {
            call_user_func($statusActions[$request->status], $attendance, $now);
        }

        return redirect()->back();
    }
// 勤怠ボタン共通処理
    private function handleClockIn($attendance, $now) {
        $attendance->update(['clock_in' => $now, 'status' => '出勤中']);
    }
    private function handleBreakStart($attendance, $now) {
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_time_start' => $now,
        ]);
        $attendance->update(['status' => '休憩中']);
    }
    private function handleBreakEnd($attendance, $now) {
        $break = $attendance->breakTimes()->whereNull('break_time_end')->latest()->first();
        if ($break) {
            $break->update(['break_time_end' => $now]);
        }
        $attendance->update(['status' => '出勤中']);
    }
    private function handleClockEnd($attendance, $now) {
        $attendance->update(['clock_end' => $now, 'status' => '退勤済']);
    }

// 勤怠一覧画面表示（一般ユーザ）
    public function showUserAttendanceList(Request $request) {
        $user = Auth::user();
        $year = $request->query('year', Carbon::today()->year);
        $month = $request->query('month', Carbon::today()->month);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date', 'asc')
            ->get();

        return view('attendance.attendance-list', compact('user', 'year', 'month', 'attendances'));
    }

// 勤怠詳細画面表示（一般ユーザ）
    public function showAttendanceDetail($id) {
        $user = Auth::user();

        $attendance = Attendance::with(['user', 'breakTimes'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
        $attendanceRequest = AttendanceRequest::with('breakTimes')->where('attendance_id', $id)->first();

        return view('attendance.detail', compact('user', 'attendance', 'attendanceRequest'));
    }
// 勤怠詳細画面から修正申請（一般ユーザ）
    public function updateRequest(AttendanceUpdateRequest $request) {

        $clockIn = Carbon::createFromFormat('H:i', $request->requested_clock_in)->format('H:i:s');
        $clockEnd = Carbon::createFromFormat('H:i', $request->requested_clock_end)->format('H:i:s');

        try {
            // **1. 勤怠修正リクエストを新規作成**
            $attendanceRequest = AttendanceRequest::create([
                'user_id' => $request->user_id,
                'attendance_id' => $request->attendance_id,
                'target_date' => $request->target_date,
                'request_type' => '修正',
                'requested_clock_in' => $clockIn,
                'requested_clock_end' => $clockEnd,
                'requested_remarks' => $request->requested_remarks,
                'status' => '承認待ち',
                'admin_id' => null,
                'approved_at' => null,
            ]);

            // **2. 修正リクエストの休憩データを保存**
            if (!empty($request->requested_break_times)) {
                foreach ($request->requested_break_times as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        AttendanceRequestBreak::create([
                            'attendance_request_id' => $attendanceRequest->id,
                            'requested_break_time_start' => Carbon::createFromFormat('H:i', $break['start'])->format('H:i:s'),
                            'requested_break_time_end' => Carbon::createFromFormat('H:i', $break['end'])->format('H:i:s'),
                        ]);
                    }
                }
            }

            return redirect()->route('attendance.edit', ['id' => $request->attendance_id]);

        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

// 申請一覧画面表示（一般ユーザ）
    public function showRequestList() {
        return view('attendance.request-list');
    }
}
