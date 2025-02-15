{{-- 勤怠登録画面（一般） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('content')
<div class="body">
    <div class="container">
        <span class="status-badge">{{ $attendance->status ?? '勤務外' }}</span>
        <div class="date">{{ now()->translatedFormat('Y年n月j日(D)') }}</div>
        <div class="time">{{ now()->format('H:i') }}</div>
        <button class="clock-in-btn">出勤</button>
    </div>
</div>

    <style>
        .body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            /* min-height: 100vh; */
            width: 100%;
            border: 1px solid green
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 2rem;
            border: 1px solid orange
        }

        .status-badge {
            background-color: #e0e0e0;
            color: #333;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 0.875rem;
        }

        .date {
            font-size: 1rem;
            color: #333;
            margin-top: 10px;
        }

        .time {
            font-size: 2.5rem;
            font-weight: bold;
            color: #000;
            margin: 10px 0;
        }

        .clock-in-btn {
            background-color: #000;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clock-in-btn:hover {
            background-color: #333;
        }
    </style>

@endsection
