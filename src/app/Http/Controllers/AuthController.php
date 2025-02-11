<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
// 会員登録画面表示
    public function showRegistrationForm() {
        return view('user.register');
    }
// 会員登録処理
    public function register(UserRegisterRequest $request) {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        // 登録直後にログイン状態にする
        auth()->login($user);

        return redirect()->route('create');
    }

// ログイン画面表示
    public function showUsersLoginForm() {
        return view('user.login');
    }
// ログイン処理
    public function login(UserLoginRequest $request) {
        $user = User::firstWhere('email', $request->email);

        // パスワードが一致するか確認
        if ($user && Hash::check($request->password, $user->password)) {
            auth()->login($user);  // 認証成功

            // 認証成功後勤怠登録画面にリダイレクト
            return redirect()->route('create');
        }

        session()->flash('auth_error', 'ログイン情報が登録されていません');
        return redirect()->route('user.login')->withInput();  // 入力値を保持してログイン画面にリダイレクト
    }

// ログアウト処理（一般ユーザー）
    public function userDestroy(Request $request)
    {
        auth()->logout(); // ログアウト処理

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login');
    }
}
