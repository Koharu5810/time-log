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
        <form method="GET" action="{{ route('verification.check') }}">
            <button type="submit" class="verifyEmail-button">認証はこちらから</button>
        </form>

        @if (session('message'))
            <p>{{ session('message') }}</p>
        @endif

{{-- 再送ボタン --}}
        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="resend-button blue-button">確認メールを再送する</button>
        </form>

    </div>
@endsection
