<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
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

        foreach ($attendances as $attendance) {
            $userId = $attendance->user_id;
        }

        return view('attendance.attendance-list', compact('user', 'staff', 'year', 'month', 'attendances'));
    }

// 勤怠詳細画面表示
    public function showAttendanceDetail($id) {
        $user = Auth::user();

        $attendance = Attendance::with(['user', 'breakTimes'])->find($id);

        return view('attendance.detail', compact('user', 'attendance'));
    }
}
