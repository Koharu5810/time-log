<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminController extends Controller
{
// ログイン画面表示（管理者）
    public function showAdminLoginForm() {
        return view('admin.login');
    }
// ログイン処理（管理者）
    public function login(AdminLoginRequest $request) {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, $request->remember)) {
            session(['is_admin_session' => true]);

            return redirect()->route('admin.dashboard');
        }

        session()->flash('admin_error', 'ログイン情報が登録されていません');
        return redirect()->route('admin.login')->withInput();  // 入力値を保持してログイン画面にリダイレクト
    }

// ログアウト処理（管理者）
    public function adminDestroy(Request $request)
    {
        auth()->logout(); // ログアウト処理

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

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
