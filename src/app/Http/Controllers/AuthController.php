<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRegisterRequest;
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

        return redirect()->route('attendance.create');
    }

// ログイン画面表示
    public function showUsersLoginForm() {
        return view('user.login');
    }
// ログイン処理
    // public function login(LoginRequest $request) {
    //     $user = User::where('name', $request->name)
    //         ->orWhere('email', $request->username)  // ユーザ名またはメールアドレスで認証
    //         ->first();

    //     // パスワードが一致するか確認
    //     if ($user && Hash::check($request->password, $user->password)) {
    //         auth()->login($user);  // 認証成功

    //         // 認証成功後商品一覧画面にリダイレクト
    //         return redirect()->route('home');
    //     }

    //     session()->flash('auth_error', 'ログイン情報が登録されていません');
    //     return redirect()->route('login')->withInput();  // 入力値を保持してログイン画面にリダイレクト
    // }
}
