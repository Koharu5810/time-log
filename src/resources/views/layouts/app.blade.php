<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>勤怠アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header>
        <div class="header">
        {{-- ロゴ表示 --}}
            <div class="header-left">
                <img class="header__logo" src="{{ asset('images/icons/logo.svg') }}" alt="ロゴ" />
            </div>

    {{-- ボタンコンテナ（ログイン時のみ表示） --}}
            @if (auth()->check() && !request()->is('user/register') && !request()->is('user/login') && !request()->is('admin/login'))
                <div class="header-right">
{{-- {{ dd(auth()->user()->role) }} --}}
            {{-- 管理者用ヘッダー --}}
                    @if (auth('admin')->check())
                        <button class="header__create-button"><a href="{{ route('admin.dashboard') }}">勤怠一覧</a></button>
                    {{-- 勤怠一覧ボタン --}}
                        <button class="header__list-button"><a href="{{ route('staff.list') }}">スタッフ一覧</a></button>
                    {{-- 申請ボタン --}}
                        <button class="header__request-button"><a href="{{ route('request.list') }}">申請一覧</a></button>
                    {{-- ログアウトボタン --}}
                        <form action="{{ route('admin.logout') }}" method="POST" class="header__logout">
                            @csrf
                            <button type="submit" class="header__logout-button">ログアウト</button>
                        </form>

            {{-- 一般ユーザ用ヘッダー --}}
                    @elseif (auth('web')->check())
                    {{-- 勤怠ボタン --}}
                        <button class="header__create-button"><a href="{{ route('create') }}">勤怠</a></button>
                    {{-- 勤怠一覧ボタン --}}
                        <button class="header__list-button"><a href="{{ route('attendance.list') }}">勤怠一覧</a></button>
                    {{-- 申請ボタン --}}
                        <button class="header__request-button"><a href="{{ route('request.list') }}">申請</a></button>
                    {{-- ログアウトボタン --}}
                        <form action="{{ route('user.logout') }}" method="POST" class="header__logout">
                            @csrf
                            <button type="submit" class="header__logout-button">ログアウト</button>
                        </form>

                    @endif
                </div>
            @endif
        </div>
    </header>

    <main>
        <div class="content">
            @yield('content')
        </div>
    </main>
</body>

</html>
