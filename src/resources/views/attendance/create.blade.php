{{-- 勤怠登録画面（一般） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}" />
@endsection

@section('content')

    <div class="container">
    {{-- ステータスバッジ --}}
        <span class="status-badge">{{ $attendance->status ?? '勤務外' }}</span>

        <div class="date" id="current-date">{{ now()->translatedFormat('Y年n月j日(D)') }}</div>
        <div class="time" id="current-time">{{ now()->format('H:i') }}</div>

    {{-- ボタン --}}
        <form id="statusForm" method="POST" action="{{ route('attendance.store') }}" class="status-button">
            @csrf
            <input type="hidden" name="status" id="statusInput">

            @if ($attendance && $attendance->status === '退勤済')
                <span class="clock_end-message">お疲れ様でした。</span>
            @elseif ($attendance && $attendance->status === '出勤中')
                <div class="button-group">
                    <button type="button" onclick="updateStatus('退勤')" class="attendance-button">退勤</button>
                    <button type="button" onclick="updateStatus('休憩入')" class="break-button">休憩入</button>
                </div>
            @elseif ($attendance && $attendance->status === '休憩中')
                <button type="button" onclick="updateStatus('休憩戻')" class="break-button">休憩戻</button>
            @else
                @if (!$alreadyClockedIn)
                    <button type="button" onclick="updateStatus('出勤')" class="attendance-button">出勤</button>
                @else
                    <span class="status-message">既に出勤ボタンを押しています</span>
                @endif
            @endif
        </form>
    </div>

    <script>
        function updateTime() {
            const now = new Date();

            // 日付を更新（例: 2024年2月16日(金)）
            const dateStr = now.getFullYear() + '年'
                        + (now.getMonth() + 1) + '月'
                        + now.getDate() + '日('
                        + ['日', '月', '火', '水', '木', '金', '土'][now.getDay()] + ')';

            document.getElementById('current-date').innerText = dateStr;

            // 時間を更新
            const timeStr = now.getHours().toString().padStart(2, '0') + ':'
                        + now.getMinutes().toString().padStart(2, '0');

            document.getElementById('current-time').innerText = timeStr;
        }
        // 1秒ごとに時間を更新
        setInterval(updateTime, 1000);
        // ページ読み込み時に即時実行
        updateTime();

        // ボタンを押すとstatusを送信
        function updateStatus(status) {
            document.getElementById('statusInput').value = status;
            document.getElementById('statusForm').submit();
        }
    </script>

@endsection
