{{-- ログイン画面（管理者） --}}
@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('title', '管理者ログイン')

@section('content')
    <h1 class="content__title">@yield('title')</h1>

    <form method="POST" action="{{ route('admin.login') }}" class="login-container">
    @csrf
{{-- メールアドレス --}}
        <div class="form__group">
            <label for="email">メールアドレス</label>
            <input type="text" name="email" value="{{ old('email') }}" class="form__group-input" />
            <div class="error-message">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>
{{-- パスワード --}}
        <div class="form__group">
            <label for="password">パスワード</label>
            <input type="password" name="password"  class="form__group-input" />
            <div class="error-message">
                @error('password')
                    {{ $message }}
                @enderror
                @if (session('admin_error'))
                    {{ session('admin_error') }}
                @endif
            </div>
        </div>
{{-- ログインボタン --}}
        <button class="login-form__button form__black-button">管理者ログインする</button>
    </form>
@endsection
