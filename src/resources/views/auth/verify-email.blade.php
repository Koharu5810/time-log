{{-- メール認証画面 --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}" />
@endsection

@section('content')
    <div class="verifyEmail-container" style="margin-top: 140px">

        <div>登録していただいたメールアドレスに認証メールを送付しました。</div>
        <div>メール認証を完了してください。</div>

{{-- メール認証ボタン --}}
        <button class="verifyEmail-button">認証はこちらから</button>

{{-- 再送ボタン --}}
        <a href="" class="resend-button blue-button">認証メールを再送する</a>

    </div>
@endsection
