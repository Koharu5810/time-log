<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // メール認証の通知ページ
    public function notice()
    {
        return view('auth.verify-email');
    }

    // メール認証処理
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('login.show')->with('status', 'すでにメール認証が完了しています');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('verification.notice')->with('status', 'メール認証が完了しました');
    }

    public function check(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('create')->with('status', 'メール認証が完了しました！');
        } else {
            return back()->with('error', 'まだメール認証が完了していません。');
        }
    }

    // メール認証の再送
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', '確認メールを再送しました。');
    }
}
