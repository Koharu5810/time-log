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

    {{-- ボタン --}}
        <form id="statusForm" method="POST" action="{{ route('attendance.store') }}" class="status-button">
            @csrf
            <input type="hidden" name="status" id="statusInput">

            @if ($attendance && $attendance->status === '退勤済')
                <span class="">お疲れ様でした。</span>
            @elseif ($attendance && $attendance->status === '出勤中')
                <button type="button" onclick="updateStatus('退勤')" class="clock-in-btn">退勤</button>
                <button type="button" onclick="updateStatus('休憩入')" class="clock-in-btn">休憩入</button>
            @elseif ($attendance && $attendance->status === '休憩中')
                <button type="button" onclick="updateStatus('休憩戻')" class="clock-in-btn">休憩戻</button>
            @else
                @if (!$alreadyClockedIn)
                    <button type="button" onclick="updateStatus('出勤')" class="clock-in-btn">出勤</button>
                @else
                    <span class="status-message">既に出勤ボタンを押しています</span>
                @endif
            @endif
        </form>
    </div>
</div>

<script>
    // ボタンを押すとstatusを送信
    function updateStatus(status) {
        document.getElementById('statusInput').value = status;
        document.getElementById('statusForm').submit();
    }
</script>

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
            width: 100%;
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
