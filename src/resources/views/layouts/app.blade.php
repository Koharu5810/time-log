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
                {{-- <a href="{{ route('home') }}"> --}}
                    <img class="header__logo" src="{{ asset('storage/logo.svg') }}" alt="ロゴ" />
                {{-- </a> --}}
            </div>

            {{-- ログイン・会員登録画面では非表示 --}}
            @if (!request()->is('register') && !request()->is('login'))
                <div class="header-center">
                </div>
            @endif

    {{-- ボタンコンテナ --}}
            @if (!request()->is('register') && !request()->is('login'))
                <div class="header-right">
            {{-- 勤怠ボタン --}}
                    <button>勤怠</button>
                    {{-- <button class="header__mypage-button"><a href="{{ route('profile.mypage') }}">マイページ</a></button> --}}
            {{-- 勤怠一覧ボタン --}}
                    <button>勤怠一覧</button>
                    {{-- <button class="header__sell-button"><a href="{{ route('sell') }}">出品</a></button> --}}
            {{-- ログアウトボタン --}}
                    <button>ログアウト</button>
                    {{-- <form action="{{ route('logout') }}" method="POST" class="header__logout">
                        @csrf
                        <button type="submit" class="header__logout-button">ログアウト</button>
                    </form> --}}
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
