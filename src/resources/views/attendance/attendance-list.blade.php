{{-- 勤怠一覧画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('sub-title', '勤怠一覧')

@section('content')
    <div class="body">
    <div class="container">
        <h2 class="content__sub-title">@yield('sub-title')</h2>

        <div class="month-selector">
        {{-- 前月リンク --}}
            <a href="{{ route('attendance.list', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}">←前月</a>
        {{-- 年月表示 --}}
            <div class="current-month">
                <img class="calendar" src="{{ asset('storage/calendar.png') }}" alt="月" />
                <span>{{ \Carbon\Carbon::create($year, $month)->format('Y/m') }}</span>
            </div>
        {{-- 次月リンク --}}
            <a href="{{ route('attendance.list', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}">翌月→</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($finalAttendances->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">今月の申請はありません。</td>
                        </tr>
                    @else
                        @foreach ($finalAttendances as $attendance)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('m/d(D)') }}</td>
                                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                                {{-- <td>
                                    {{ $attendance->clock_in
                                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                                        : ($attendance->requested_clock_in
                                            ? \Carbon\Carbon::parse($attendance->requested_clock_in)->format('H:i')
                                            : '') }}
                                </td> --}}
                                <td>{{ $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '' }}</td>
                                {{-- <td>
                                    {{ $attendance->clock_end
                                        ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i')
                                        : ($attendance->requested_clock_end
                                            ? \Carbon\Carbon::parse($attendance->requested_clock_end)->format('H:i')
                                            : '') }}
                                </td> --}}
                                <td>{{ $attendance->total_break_time ? gmdate('H:i', $attendance->total_break_time * 60) : '' }}</td>
                                <td>
                                    @if ($attendance->clock_in && $attendance->clock_end)
                                       {{ gmdate('H:i', ($attendance->duration_in_minutes - $attendance->total_break_time) * 60) }}
                                    @else
                                        {{-- データが揃っていない場合は空欄 --}}
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

