<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
