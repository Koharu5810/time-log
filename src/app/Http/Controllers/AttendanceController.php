<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
// 勤怠登録画面表示（一般ユーザ）
    public function index() {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d'); // 今日の日付

        // 本日の勤務データを取得（ない場合は null）
        $attendance = $user->attendances()->where('work_date', $today)->first();

        return view('attendance.create', compact('user', 'attendance'));
    }
    public function store(Request $request) {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d'); // 今日の日付

        // 打刻処理
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => '勤務外'],  // デフォルト値
            ['clock_in' => Carbon::now()],
        );

        $attendance->status = $request->status;
        $attendance->save();

        return redirect()->back();
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
