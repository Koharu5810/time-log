{{-- 修正申請承認画面（管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request-approval.css') }}" />
@endsection

@section('sub-title', '勤怠詳細')

@section('content')
<div class="body">
    <div class="container">
        <h2 class="content__sub-title">@yield('sub-title')</h2>

        <form method="POST" action="{{ route('request.approve', ['attendance_correct_request' => $request->id]) }}" class="attendance-form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            <table class="attendance-table">
                <tbody class="table-body">
            {{-- 名前 --}}
                    <tr>
                        <th>名前</th>
                        <td colspan="4" class="name-value">
                            <input
                                type="hidden"
                                name="user_id"
                                value="{{ $attendance->user_id }}"
                            />
                            {{ $attendance->user->name }}
                        </td>
                    </tr>
            {{-- 日付 --}}
                    <tr>
                        <th>日付</th>
                        <td class="date-container">
                            <input
                                type="hidden"
                                name="work_date"
                                value="{{ old('work_date', \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d')) }}"
                            />
                            {{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('Y年') }}
                        </td>
                        <td class="time-separator"></td>
                        <td class="date-container">
                            {{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('n月j日') }}
                        </td>
                        <td></td>
                    </tr>
            {{-- 出勤・退勤 --}}
                    <tr>
                        <th>出勤・退勤</th>
                        <td class="time">
                            {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                        </td>
                        <td class="time-separator">〜</td>
                        <td class="time">
                            {{ \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') }}
                        </td>
                        <td></td>
                    </tr>
            {{-- 休憩 --}}
                    @foreach ($attendance->breakTimes as $index => $break)
                        <tr>
                            <th>{{ $index == 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                            <td class="time">
                                {{ $break->break_time_start ?\Carbon\Carbon::parse($break->break_time_start)->format('H:i') : '' }}
                            </td>
                            <td class="time-separator">〜</td>
                            <td class="time">
                                {{ $break->break_time_end ?\Carbon\Carbon::parse($break->break_time_end)->format('H:i') : '' }}
                            </td>
                            <td></td>
                        </tr>
                    @endforeach
            {{-- 備考 --}}
                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            {{ $attendance->remarks }}
                        </td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="button-container">
                            @if ($request->request_status === '承認待ち')
                                <button type="submit" class="approve-button">承認</button>
                            @else
                                <span>承認済み</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

@endsection
