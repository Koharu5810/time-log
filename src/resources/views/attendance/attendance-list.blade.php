{{-- 勤怠一覧画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/attendance-list.css') }}" />
@endsection

@section('sub-title', auth('admin')->check()
    ? (isset($staff) ? $staff->name . 'さんの勤怠' : '')
    : (auth('web')->check() ? '勤怠一覧' : ''))

@section('content')
    <div class="attendance-list__container">
        @php
            // 認証ユーザー情報を取得
            $authUser = auth('admin')->check() ? auth('admin')->user() : auth('web')->user();

            // ルートの切り替え
            $routeName = auth('admin')->check() ? 'admin.attendance.list' : 'attendance.list';
            $routeParams = [
                'year' => $month == 1 ? $year - 1 : $year,
                'month' => $month == 1 ? 12 : $month - 1
            ];

            // 管理者の場合、IDを追加
            if (auth('admin')->check()) {
                $routeParams['id'] = isset($staff) ? $staff->id : '';
            }
        @endphp

        <div class="month-selector">
            {{-- 前月リンク --}}
            <a href="{{ route($routeName, $routeParams) }}">← 前月</a>

            {{-- 年月表示 --}}
            <div class="current-month">
                <img class="calendar" src="{{ asset('images/icons/calendar.png') }}" alt="月" />
                <span>{{ \Carbon\Carbon::create($year, $month)->format('Y/m') }}</span>
            </div>

            {{-- 次月リンク --}}
            @php
                $routeParams['year'] = ($month == 12) ? $year + 1 : $year;
                $routeParams['month'] = ($month == 12) ? 1 : $month + 1;
            @endphp
            <a href="{{ route($routeName, $routeParams) }}">翌月 →</a>
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
                    @if ($attendances->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">今月の申請はありません。</td>
                        </tr>
                    @else
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('m/d(D)') }}</td>
                                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                                <td>{{ $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '' }}</td>
                                <td>{{ $attendance->total_break_time ? gmdate('H:i', $attendance->total_break_time * 60) : '' }}</td>
                                <td>
                                    @if ($attendance->clock_in && $attendance->clock_end)
                                        {{ gmdate('H:i', ($attendance->duration_in_minutes - $attendance->total_break_time) * 60) }}
                                    @else
                                        {{-- データが揃っていない場合は空欄 --}}
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

