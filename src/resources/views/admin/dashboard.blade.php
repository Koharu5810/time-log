{{-- 管理者ダッシュボード --}}
@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('sub-title', '2025年2月22日の勤怠')
{{-- @section('title', \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('Y/m/d')'の勤怠') --}}

@section('content')
    <div class="body">
    <div class="container">
        <h2 class="content__sub-title">@yield('sub-title')</h2>

        <div class="month-selector">
        {{-- 前日リンク --}}
            <a href="{{ route('admin.dashboard', [
                'year' => \Carbon\Carbon::create($year, $month, $day)->subDay()->year,
                'month' => \Carbon\Carbon::create($year, $month, $day)->subDay()->month,
                'day' => \Carbon\Carbon::create($year, $month, $day)->subDay()->day
            ]) }}">←前日</a>
        {{-- 日付表示 --}}
            <div class="current-month">
                <img class="calendar" src="{{ asset('storage/calendar.png') }}" alt="月" />
                <span>{{ \Carbon\Carbon::create($year, $month, $day)->format('Y/m/d') }}</span>
            </div>
        {{-- 次日リンク --}}
            <a href="{{ route('admin.dashboard', [
                'year' => \Carbon\Carbon::create($year, $month, $day)->subDay()->year,
                'month' => \Carbon\Carbon::create($year, $month, $day)->subDay()->month,
                'day' => \Carbon\Carbon::create($year, $month, $day)->subDay()->day
            ]) }}">翌日→</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($attendances->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">今日の申請はありません。</td>
                        </tr>
                    @else
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->user->name }}</td>
                                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                                <td>{{ $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '' }}</td>
                                <td>{{ $attendance->total_break_time ? gmdate('H:i', $attendance->total_break_time * 60) : '' }}</td>
                                <td>
                                    @if ($attendance->clock_in && $attendance->clock_end)
                                       {{ gmdate('H:i', ($attendance->duration_in_minutes - $attendance->total_break_time) * 60) }}
                                    @else
                                        {{-- データがない場合は空欄 --}}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-btn">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

    <style>
        .body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 20px;
            color: #000;
            width: 100%;
            border: solid 1px yellowgreen
        }

        .container {
            /* max-width: 1000px; */
            margin: 0 auto;
            padding: 20px;
        }

        .month-selector {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }

        .month-selector button {
            border: none;
            background: none;
            cursor: pointer;
            padding: 8px;
            color: #666;
        }

        .month-selector,
        .current-month {
            font-weight: 500;
        }

        .current-month {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .current-month img {
            width: 25px;
            height: auto;
        }
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            background-color: #fff;
            border-collapse: collapse;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        th {
            /* background-color: #f8f9fa; */
            padding: 12px;
            text-align: center;
            border-bottom: 2px solid #dee2e6;
            font-weight: 500;
        }

        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-btn {
            background-color: transparent;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 4px 8px;
        }

        .detail-btn:hover {
            text-decoration: underline;
        }
    </style>

@endsection
