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

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Charm&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    </head>

    <body>
        <header class="header">
    {{-- ロゴ表示 --}}
            <div class="header-left">
                <img class="header__logo" src="{{ asset('images/icons/logo.svg') }}" alt="ロゴ" />
            </div>

    {{-- ボタンコンテナ（ログイン・会員登録時は非表示） --}}
            @if (auth()->check() &&
                !request()->is('user/register') &&
                !request()->is('user/login') &&
                !request()->is('email/verify') &&
                !request()->is('admin/login'))
                <div class="header-right">

            {{-- 管理者用ヘッダー --}}
                    @if (auth('admin')->check())
                        <div class="header__buttons-row">
                            <a href="{{ route('admin.dashboard') }}" class="header__create-button">勤怠一覧</a>
                        {{-- 勤怠一覧ボタン --}}
                            <a href="{{ route('staff.list') }}" class="header__list-button">スタッフ一覧</a>
                        </div>
                        <div class="header__buttons-row">
                        {{-- 申請一覧ボタン --}}
                            <a href="{{ route('request.list') }}" class="header__request-button">申請一覧</a>
                        {{-- ログアウトボタン --}}
                            <form action="{{ route('admin.logout') }}" method="POST" class="header__logout">
                                @csrf
                                <button type="submit" class="header__logout-button">ログアウト</button>
                            </form>
                        </div>

            {{-- 一般ユーザ用ヘッダー --}}
                    @elseif (auth('web')->check())
                            <div class="header__buttons-row">
                        {{-- 勤怠ボタン --}}
                            <a href="{{ route('create') }}" class="header__create-button">勤怠</a>
                        {{-- 勤怠一覧ボタン --}}
                            <a href="{{ route('attendance.list') }}" class="header__list-button">勤怠一覧</a>
                        </div>
                        <div class="header__buttons-row">
                        {{-- 申請ボタン --}}
                            <a href="{{ route('request.list') }}" class="header__request-button">申請</a>
                        {{-- ログアウトボタン --}}
                            <form action="{{ route('user.logout') }}" method="POST" class="header__logout">
                                @csrf
                                <button type="submit" class="header__logout-button">ログアウト</button>
                            </form>
                        </div>
                    @endif

                </div>
            @endif

        </header>

        <main>
            <div class="content">
                @hasSection('title')
                    <h1 class="title">@yield('title')</h1>
                @endif

                @hasSection('sub-title')
                    <h2 class="sub-title">@yield('sub-title')</h2>
                @endif

                @yield('content')
            </div>
        </main>

    </body>
</html>
