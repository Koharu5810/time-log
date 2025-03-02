{{-- 会員登録画面 --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}" />
@endsection

@section('title', '会員登録')

@section('content')
    <h1 class="content__title">@yield('title')</h1>

    <form method="post" action="{{ route('registration') }}" class="register-container">
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
    <a href="/login" class="login-link blue-button">ログインはこちら</a>
@endsection
