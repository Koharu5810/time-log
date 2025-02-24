<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
// 勤怠一覧画面表示
    public function showAdminDashBoard(Request $request) {
        $date = Carbon::create(
            $request->query('year', Carbon::today()->year),
            $request->query('month', Carbon::today()->month),
            $request->query('day', Carbon::today()->day)
        )->toDateString();

        $attendances = Attendance::whereDate('work_date', $date)
            ->with(['user', 'breakTimes'])
            ->orderBy('user_id', 'asc')
            ->get();

        return view('admin.dashboard', compact('date', 'attendances'));
    }

// スタッフ一覧画面表示
    public function showStaffList() {
        $admin = Auth::guard('admin')->user();

        $staffList = User::with('attendances')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.staff-list', compact('staffList'));
    }
}
