{{-- 会員登録画面 --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/register.css') }}" />
@endsection

@section('title', '会員登録')

@section('content')
    <form method="post" action="{{ route('user.register') }}" class="register-container">
    @csrf
{{-- ユーザー名 --}}
        <div class="form__group">
            <label for="name">名前</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form__group-input" />
            <div class="error-message">
                @error('name')
                    {{ $message }}
                @enderror
            </div>
        </div>
{{-- メールアドレス --}}
        <div class="form__group">
            <label for="username">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form__group-input" />
            <div class="error-message">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>
{{-- パスワード --}}
        <div class="form__group">
            <label for="password">パスワード</label>
            <input type="password" name="password" class="form__group-input" />
            <div class="error-message">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>
{{-- パスワード確認 --}}
        <div class="form__group">
            <label for="password_confirmation">パスワード確認</label>
            <input type="password" name="password_confirmation" class="form__group-input" />
            <div class="error-message">
                @error('password_confirmation')
                    {{ $message }}
                @enderror
            </div>
        </div>
{{-- 登録ボタン --}}
        <button class="register-form__button form__black-button">登録する</button>
    </form>
{{-- ログイン案内 --}}
    <a href="/user/login" class="login-link blue-button">ログインはこちら</a>
@endsection
