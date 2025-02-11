{{-- 勤怠登録画面（一般） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('content')

<div class="min-h-screen flex items-center justify-center bg-gray-50">

    <div class="w-full max-w-sm p-6 space-y-8 text-center">
        {{-- 日付表示 --}}
        <div class="text-gray-600 text-sm">
            {{ \Carbon\Carbon::now()->isoFormat('YYYY年M月D日(ddd)') }}
        </div>

        {{-- 時刻表示 --}}
        <div class="text-4xl font-medium">
            {{ \Carbon\Carbon::now()->format('H:i') }}
        </div>

        {{-- 打刻フォーム --}}
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <button type="submit"
                    class="bg-black text-white rounded-md px-8 py-3 text-base hover:bg-gray-800 transition-colors">
                出勤
            </button>
        </form>

        {{-- エラーメッセージ表示 --}}
        @if ($errors->any())
            <div class="text-red-500 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- 成功メッセージ表示 --}}
        @if (session('success'))
            <div class="text-green-500 text-sm">
                {{ session('success') }}
            </div>
        @endif
    </div>
</div>

@endsection
