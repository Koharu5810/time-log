<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
// ログイン画面表示（管理者ユーザー）
    public function showAdminLoginForm() {
        return view('admin.login');
    }
// ログイン処理（管理者ユーザー）
    public function login(AdminLoginRequest $request) {
        $user = Admin::firstWhere('email', $request->email);

        // パスワードが一致するか確認
        if ($user && Hash::check($request->password, $user->password)) {
            auth()->login($user);  // 認証成功

            // 認証成功後勤怠登録画面にリダイレクト
            // return redirect()->route('create');
        }

        if (Auth::guard('admin')->attempt($request->remember)) {
            return redirect()->route('admin.dashboard'); // 管理者専用ダッシュボードへ
        }
    }

// ログアウト処理（管理者ユーザー）
    // public function adminDestroy(Request $request)
    // {
    //     auth()->logout(); // ログアウト処理

    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return redirect()->route('admin.login');
    // }
}
