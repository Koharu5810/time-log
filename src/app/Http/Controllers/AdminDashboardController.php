<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
// 勤怠一覧画面表示
    public function showAdminDashBoard(Request $request) {
        $admin = Auth::guard('admin')->user();

        $today = Carbon::today();
        $year = $request->query('year', $today->year);
        $month = $request->query('month', $today->month);
        $day = $request->query('day', $today->day);

        $date = Carbon::create($year, $month, $day)->toDateString();

        $attendances = Attendance::whereDate('work_date', $date)
            ->with(['user', 'breakTimes'])
            ->orderBy('user_id', 'asc')
            ->get();

        $attendanceRequests = AttendanceRequest::whereDate('target_date', $date)
            ->with(['user', 'attendanceBreakTimes'])
            ->orderBy('user_id', 'asc')
            ->get();

        $finalAttendances = collect();

        foreach ($attendances as $attendance) {
            $userId = $attendance->user_id;

            // attendance_requestsに同日の修正勤怠申請がある場合、そちらを優先
            $requestData = $attendanceRequests->firstWhere('user_id', $userId);
            if ($requestData) {
                $finalAttendances->push($requestData);
            } else {
                $finalAttendances->push($attendance);
            }
        }

        // attendance_requests のデータで attendances にないものを追加
        foreach ($attendanceRequests as $request) {
            if (!$finalAttendances->contains('user_id', $request->user_id)) {
                $finalAttendances->push($request);
            }
        }

        return view('admin.dashboard', compact('year', 'month', 'day', 'date', 'finalAttendances'));
    }

// スタッフ一覧画面表示
    public function showStaffList() {
        $admin = Auth::guard('admin')->user();

        $staffList = User::with('attendances')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.staff-list', compact('staffList'));
    }

// スタッフ別勤怠一覧画面表示
    public function showStaffAttendanceList(Request $request) {
        $admin = Auth::guard('admin')->user();
        $user = User::get();
        $year = $request->query('year', Carbon::today()->year);
        $month = $request->query('month', Carbon::today()->month);
        $day = $request->query('day', Carbon::today()->day);

        $date = Carbon::create($year, $month, $day)->toDateString();

        $attendances = Attendance::with('breakTimes')
            ->whereIn('user_id', $user->pluck('id'))
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date', 'asc')
            ->get();
        $attendanceRequests = AttendanceRequest::whereDate('target_date', $date)
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
        return view('attendance.attendance-list', compact('user', 'year', 'month', 'finalAttendances'));
    }
}
