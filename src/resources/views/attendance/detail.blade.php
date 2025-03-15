{{-- 勤怠詳細画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}" />
@endsection

@section('sub-title', '勤怠詳細')

@section('content')
    <form method="POST" action="{{ route('attendance.update', ['id' => $attendance->id]) }}" class="attendance-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

        <table class="attendance-table">
            <tbody class="table-body">
        {{-- 名前 --}}
                <tr>
                    <td class="table__label">名前</td>
                    <td class="table__input name-display">
                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ $attendance->user_id }}"
                        />
                        <div>{{ $attendance->user->name }}</div>
                    </td>
                </tr>
        {{-- 日付 --}}
                <tr>
                    <td class="table__label">日付</td>
                    <td class="table__input work_date-display">
                        <input
                            type="hidden"
                            name="work_date"
                            value="{{ old('work_date', \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d')) }}"
                        />
                        <div>{{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('Y年') }}</div>
                        <div>{{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('n月j日') }}</div>
                    </td>
                </tr>
        {{-- 出勤・退勤 --}}
                <tr>
                    <td class="table__label">出勤・退勤</td>
                    <td class="table__input">
                        <div class="attendance-time-group">
                            <div class="attendance-time-inputs">
                                @if ($attendance->attendanceCorrectRequest)
                                    {{ \Carbon\Carbon::parse($attendance->attendanceCorrectRequest->requested_clock_in)->format('H:i') }}
                                @else
                                    <input
                                        type="text"
                                        name="requested_clock_in"
                                        value="{{ old('requested_clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                                    />
                                @endif

                                <span>〜</span>

                                @if ($attendance->attendanceCorrectRequest)
                                    {{ \Carbon\Carbon::parse($attendance->attendanceCorrectRequest->requested_clock_end)->format('H:i') }}
                                @else
                                    <input
                                        type="text"
                                        name="requested_clock_end"
                                        value="{{ old('requested_clock_end', $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '') }}"
                                    />
                                @endif
                            </div>

                            @if ($errors->has('requested_clock_in') || $errors->has('requested_clock_end'))
                                <div class="error-message">
                                    <div>@error('requested_clock_in') {{ $message }} @enderror</div>
                                    <div>@error('requested_clock_end') {{ $message }} @enderror</div>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
        {{-- 休憩 --}}
                @foreach ($displayBreakTimes as $index => $break)
                    <input type="hidden" name="break_times[{{ $break['index'] }}][id]" value="{{ $break['id'] }}">

                    <tr>
                        <td class="table__label">{{ $index == 0 ? '休憩' : '休憩' . ($index + 1) }}</td>
                        <td class="table__input">
                            <div class="break-time-group">
                                <div class="break-time-inputs">
                                    @if ($break['is_corrected'])
                                        <div>{{ \Carbon\Carbon::parse($break['start'])->format('H:i') }}</div>
                                    @else
                                        <input
                                            type="text"
                                            name="break_times[{{ $index }}][start]"
                                            value="{{ old("break_times.{$break['index']}.start", $break['start'] ? \Carbon\Carbon::parse($break['start'])->format('H:i') : '') }}"
                                        />
                                    @endif

                                    <span>〜</span>

                                    @if ($break['is_corrected'])
                                        {{ \Carbon\Carbon::parse($break['end'])->format('H:i') }}
                                    @else
                                        <input
                                            type="text"
                                            name="break_times[{{ $index }}][end]"
                                            value="{{ old("break_times.{$break['index']}.end", $break['end'] ? \Carbon\Carbon::parse($break['end'])->format('H:i') : '') }}"
                                        />
                                    @endif
                                </div>

                                @if ($errors->has("break_times.{$index}.start") || $errors->has("break_times.{$index}.end"))
                                    <div class="error-message">
                                        <div>@error("break_times.{$index}.start") {{ $message }} @enderror</div>
                                        <div>@error("break_times.{$index}.end"){{ $message }} @enderror</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
        {{-- 備考 --}}
                <tr>
                    <td class="table__label">備考</td>
                    <td class="table__input remarks_textarea">
                        @if ($attendance->attendanceCorrectRequest)
                            {{ $attendance->attendanceCorrectRequest->remarks }}
                        @else
                            <div style="display: flex; flex-direction: column;">
                                <textarea name="remarks" placeholder="電車遅延のため">{{ old('remarks') }}</textarea>
                                @if ($errors->has('remarks'))
                                    <div class="error-message" style="display: block; margin-top: 5px;">
                                        @error('remarks') {{ $message }} @enderror
                                    </div>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            </tbody>

    {{-- 修正ボタン --}}
            <tfoot class="tfoot">
                <tr>
                    <td colspan="2" class="button-container">
                        @switch($attendance->attendanceCorrectRequest->request_status ?? '')
                            @case('承認待ち')
                                <div class="edit-message">*承認待ちのため修正はできません。</div>
                                @break
                            @case('承認済み')
                                <div class="edit-message">*すでに修正済みのため、再修正はできません。</div>
                                @break
                            @default
                                <button type="submit" class="edit-button">修正</button>
                        @endswitch
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>

@endsection
