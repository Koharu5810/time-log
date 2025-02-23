<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AttendanceController extends Controller
{
// 勤怠一覧画面表示（一般ユーザは自分の月次勤怠、管理者は任意のスタッフの月次勤怠）
    public function showAttendanceList(Request $request, $id = null) {
        if (auth('admin')->check()) {
            $authUser = auth('admin')->user();
            $staff = $id ? User::findOrFail($id) : null;
        } elseif (auth('web')->check()) {
            // 一般ユーザーの場合
            $authUser = auth('web')->user();
            $staff = null;
        } else {
            abort(403, 'Unauthorized');
        }

        $year = $request->query('year', Carbon::today()->year);
        $month = $request->query('month', Carbon::today()->month);
        $day = $request->query('day', Carbon::today()->day);
        $date = Carbon::create($year, $month, $day)->toDateString();

        // 表示対象のユーザを決定
        if (auth('admin')->check()) {
            // 管理者は対象のスタッフ
            if ($staff) {
                $user = $staff;
            } else {
                return view('attendance.attendance-list');
            }
        } else {
            // 一般ユーザは自分の勤怠データ
            $user = $authUser;
        }

        // 勤怠データ取得
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date', 'asc')
            ->get();
        // 勤怠修正申請データ取得
        $attendanceRequests = AttendanceRequest::whereDate('target_date', $date)
            ->where('user_id', $user->id)
            ->with(['user', 'attendanceBreakTimes'])
            ->orderBy('user_id', 'asc')
            ->get();

        $finalAttendances = collect();

        foreach ($attendances as $attendance) {
            $userId = $attendance->user_id;

            // attendance_requestsに同日の修正勤怠申請がある場合、そちらを優先
            $requestData = $attendanceRequests->firstWhere('user_id', $userId);
            if ($requestData) {
                $attendance->clock_in = $requestData->requested_clock_in ?? $attendance->clock_in;
                $attendance->clock_end = $requestData->requested_clock_end ?? $attendance->clock_end;
                $attendance->total_break_time = $requestData->total_break_time ?? $attendance->total_break_time;
                $attendance->duration_in_minutes = $requestData->duration_in_minutes ?? $attendance->duration_in_minutes;
            }
            $finalAttendances->push($attendance);
        }

        // attendance_requests のデータで attendances にないものを追加
        foreach ($attendanceRequests as $request) {
            if (!$finalAttendances->contains('user_id', $request->user_id)) {
                $finalAttendances->push($request);
            }
        }
        return view('attendance.attendance-list', compact('user', 'staff', 'year', 'month', 'finalAttendances'));
    }

// 勤怠詳細画面表示
    public function showAttendanceDetail($id) {
        $user = Auth::user();

        $attendance = Attendance::with(['user', 'breakTimes'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
        $attendanceRequest = AttendanceRequest::with('attendanceBreakTimes')->where('attendance_id', $id)->first();

        return view('attendance.detail', compact('user', 'attendance', 'attendanceRequest'));
    }
}
