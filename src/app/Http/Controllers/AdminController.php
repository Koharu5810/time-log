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
    // public function login(AdminLoginRequest $request) {
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, $request->remember)) {
            return redirect()->route('admin.dashboard');
        }

        return back();
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
