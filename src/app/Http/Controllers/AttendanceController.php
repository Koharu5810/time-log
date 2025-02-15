<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\BreakTime;
use Carbon\Carbon;

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
// 共通処理のメソッド
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
    public function showUserAttendanceList() {
        return view('attendance.attendance-list');
    }
    // public function index()
    // {
    //     $attendances = Attendance::orderBy('date', 'desc')
    //         ->paginate(31);

    //     return view('attendances.index', compact('attendances'));
    // }

// 申請一覧画面表示（一般ユーザ）
    public function showRequestList() {
        return view('attendance.request-list');
    }
// 勤怠詳細画面表示（一般ユーザ）
    public function showAttendanceDetail() {
        return view('attendance.detail');
    }
}
