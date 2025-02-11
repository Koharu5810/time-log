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
                <a href="{{ route('create') }}">
                    <img class="header__logo" src="{{ asset('storage/logo.svg') }}" alt="ロゴ" />
                </a>
            </div>

    {{-- ボタンコンテナ --}}
            @if (auth()->check() && !request()->is('user/register') && !request()->is('user/login'))
                <div class="header-right">
            {{-- 勤怠ボタン --}}
                    <button class="header__mypage-button"><a href="{{ route('create') }}">勤怠</a></button>
            {{-- 勤怠一覧ボタン --}}
                    <button>勤怠一覧</button>
                    {{-- <button class="header__sell-button"><a href="{{ route('list') }}">勤怠一覧</a></button> --}}
            {{-- 申請ボタン --}}
                    <button>申請</button>
                    {{-- <button class="header__sell-button"><a href="{{ route('request') }}">申請</a></button> --}}
            {{-- ログアウトボタン --}}
                    <form action="{{ route('user.logout') }}" method="POST" class="header__logout">
                        @csrf
                        <button type="submit" class="header__logout-button">ログアウト</button>
                    </form>
                </div>
            @endif
        </div>
    </header>

    <main>
        <div class="content">
            <h1 class="content__title">@yield('title')</h1>
            @yield('content')
        </div>
    </main>
</body>

</html>
