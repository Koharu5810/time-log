{{-- 管理者ダッシュボード --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}" />
@endsection

@section('sub-title', \Carbon\Carbon::create($date)->translatedFormat('Y年m月d日の勤怠'))

@section('content')
    <div class="dashboard-container">

        <div class="month-selector">
        {{-- 前日リンク --}}
            <a href="{{ route('admin.dashboard', [
                'year' => \Carbon\Carbon::create($date)->subDay()->year,
                'month' => \Carbon\Carbon::create($date)->subDay()->month,
                'day' => \Carbon\Carbon::create($date)->subDay()->day
            ]) }}">← 前日</a>
        {{-- 日付表示 --}}
            <div class="current-month">
                <img class="calendar" src="{{ asset('images/icons/calendar.png') }}" alt="日付" />
                <span>{{ \Carbon\Carbon::create($date)->format('Y/m/d') }}</span>
            </div>
        {{-- 次日リンク --}}
            <a href="{{ route('admin.dashboard', [
                'year' => \Carbon\Carbon::create($date)->addDay()->year,
                'month' => \Carbon\Carbon::create($date)->addDay()->month,
                'day' => \Carbon\Carbon::create($date)->addDay()->day
            ]) }}">翌日 →</a>
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
                                <td>
                                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                                </td>
                                <td>
                                    {{ $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '' }}
                                </td>
                                <td>
                                    {{ $attendance->total_break_time ? gmdate('H:i', $attendance->total_break_time * 60) : '' }}
                                </td>
                                <td>
                                    @if ($attendance->clock_in && $attendance->clock_end)
                                        {{ gmdate('H:i', (($attendance->duration_in_minutes ?? 0) - ($attendance->total_break_time ?? 0)) * 60) }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-button">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

@endsection
