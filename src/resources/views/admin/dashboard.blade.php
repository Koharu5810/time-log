{{-- 管理者ダッシュボード --}}
@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('title', '管理者勤怠一覧')

@section('content')
    <h1 class="content__title">@yield('title')</h1>

@endsection

