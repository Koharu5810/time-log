{{-- 勤怠詳細画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}" />
@endsection

@section('sub-title', '勤怠詳細')

@section('content')
<div class="body">
    <div class="container">
        <h2 class="content__sub-title">@yield('sub-title')</h2>

        <form method="POST" action="{{ route('attendance.update', ['id' => $attendance->id]) }}" class="attendance-form">
            @csrf
            @method('PUT')
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
                            @if ($attendance->attendanceCorrectRequest)
                                {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                            @else
                                <input
                                    type="text"
                                    class="time-input"
                                    name="requested_clock_in"
                                    value="{{ old('requested_clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                                />
                            @endif
                        </td>
                        <td class="time-separator">〜</td>
                        <td class="time">
                            @if ($attendance->attendanceCorrectRequest)
                                {{ \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') }}
                            @else
                                <input
                                    type="text"
                                    class="time-input"
                                    name="requested_clock_end"
                                    value="{{ old('requested_clock_end', $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '') }}"
                                />
                            @endif
                        </td>
                        <td></td>
                    </tr>
                    @if ($errors->has('requested_clock_in') || $errors->has('requested_clock_end'))
                        <tr>
                            <th></th>
                            <td colspan="4">
                                <div class="error-message">
                                    <div>@error('requested_clock_in') {{ $message }} @enderror</div>
                                    <div>@error('requested_clock_end') {{ $message }} @enderror</div>
                                </div>
                            </td>
                        </tr>
                    @endif
            {{-- 休憩 --}}
                    @foreach ($attendance->breakTimes as $index => $break)
                        <tr>
                            <th>{{ $index == 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                            <td class="time">
                                @if ($attendance->attendanceCorrectRequest)
                                    {{ \Carbon\Carbon::parse(optional($break)->break_time_start)->format('H:i') }}
                                @else
                                    <input
                                        type="text"
                                        class="time-input"
                                        name="break_times[{{ $index }}][start]"
                                        value="{{ old('break_times.' . $index . '.start', $break->break_time_start ? \Carbon\Carbon::parse($break->break_time_start)->format('H:i') : '') }}"
                                    />
                                @endif
                            </td>
                            <td class="time-separator">〜</td>
                            <td class="time">
                                @if ($attendance->attendanceCorrectRequest)
                                    {{ \Carbon\Carbon::parse(optional($break)->break_time_end)->format('H:i') }}
                                @else
                                    <input
                                        type="text"
                                        class="time-input"
                                        name="break_times[{{ $index }}][end]"
                                        value="{{ old('break_times.' . $index . '.end', $break->break_time_end ? \Carbon\Carbon::parse($break->break_time_end)->format('H:i') : '') }}"
                                    />
                                @endif
                            </td>
                            <td></td>
                        </tr>
                        @if ($errors->has("break_times.{$index}.start") || $errors->has("break_times.{$index}.end"))
                            <tr>
                                <th></th>
                                <td colspan="4">
                                    <div class="error-message">
                                        <div>
                                            @error("break_times.{$index}.start")
                                                {{ $message }}
                                            @enderror
                                        </div>
                                        <div>
                                            @error("break_times.{$index}.end")
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
            {{-- 備考 --}}
                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            @if ($attendance->attendanceCorrectRequest)
                                {{ $attendance->remarks }}
                            @else
                                <textarea name="remarks" placeholder="電車遅延のため">{{ old('remarks') }}</textarea>
                                @if ($errors->has('remarks'))
                                    <div class="error-message">
                                        @error('remarks') {{ $message }} @enderror
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="button-container">
                            @switch($attendance->attendanceCorrectRequest->request_status ?? '')
                                @case('承認待ち')
                                    <span>*承認待ちのため修正はできません。</span>
                                    @break
                                @case('承認済み')
                                    <span>*すでに修正済みのため、再修正はできません。</span>
                                    @break
                                @default
                                    <button type="submit" class="edit-button">修正</button>
                            @endswitch
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

@endsection
