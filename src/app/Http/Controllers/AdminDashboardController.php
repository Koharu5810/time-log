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
    public function showAdminDashBoard(Request $request) {
        $admin = Auth::guard('admin')->user();
        $year = $request->query('year', Carbon::today()->year);
        $month = $request->query('month', Carbon::today()->month);
        $day = $request->query('day', Carbon::today()->day);

        $date = $request->input('date', Carbon::today()->toDateString()); // 日付指定がなければ今日
        $attendances = Attendance::whereDate('clock_in', $date)
            ->with(['user', 'breakTimes'])
            ->orderBy('user_id', 'asc')
            ->get();

        return view('admin.dashboard', compact('admin', 'year', 'month', 'day', 'attendances', 'date'));
    }
}
