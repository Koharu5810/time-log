{{-- 勤怠詳細画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('sub-title', '勤怠詳細')

@section('content')
<div class="body">
    <div class="container">
        <h2 class="content__sub-title">@yield('sub-title')</h2>

        <form method="POST" action="{{ route('attendance.update', ['id' => $attendance->id]) }}" class="attendance-form">
        @csrf
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
                                name="target_date"
                                value="{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}"
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
                            <input
                                type="text"
                                class="time-input" name="requested_clock_in"
                                value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}"
                            />
                        </td>
                        <td class="time-separator">〜</td>
                        <td class="time">
                            <input
                                type="text"
                                class="time-input"
                                name="requested_clock_end"
                                value="{{ $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '' }}"
                            />
                        </td>
                        <td></td>
                    </tr>
                    @if ($errors->has('requested_clock_in') || $errors->has('requested_clock_end'))
                        <tr>
                            <th></th>
                            <td colspan="4">
                                <div class="error-message">
                                    @error('requested_clock_in')
                                        {{ $message }}
                                    @enderror
                                    @error('requested_clock_end')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    @endif
            {{-- 休憩 --}}
                    @foreach ($attendance->breakTimes as $index => $break)
                        <tr>
                            <th>{{ $index == 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                            <td>
                                <input
                                    type="text"
                                    class="time-input"
                                    name="requested_break_times[{{ $index }}][start]"
                                    value="{{ $break->break_time_start ? \Carbon\Carbon::parse($break->break_time_start)->format('H:i') : '' }}"
                                />
                            </td>
                            <td class="time-separator">〜</td>
                            <td class="time">
                                <input
                                    type="text"
                                    class="time-input"
                                    name="requested_break_times[{{ $index }}][end]"
                                    value="{{ $break->break_time_end ? \Carbon\Carbon::parse($break->break_time_end)->format('H:i') : '' }}"
                                />
                            </td>
                            <td></td>
                        </tr>
                        @if ($errors->has('requested_break_times.*.start') || $errors->has('requested_break_times.*.end'))
                            <tr>
                                <th></th>
                                <td colspan="4">
                                    <div class="error-message">
                                        @error("requested_break_times.$index.start")
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
            {{-- 備考 --}}
                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            <textarea name="requested_remarks" placeholder="電車遅延のため">
                                {{ old('requested_remarks') }}
                            </textarea>
                            @if ($errors->has('requested_remarks'))
                                <div class="error-message">
                                    @error('requested_remarks')
                                        {{ $message }}
                                    @enderror
                                </div>
                            @endif
                        </td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="button-container">
                            <button type="submit" class="edit-button">修正</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

<style>
    .body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding: 20px;
        color: #000;
        width: 80%;
        border: solid 1px yellowgreen
    }

    .attendance-form {
        margin-bottom: 20px;
    }

    .attendance-table {
        width: 100%;
        margin: auto;
        border-collapse: collapse;
    }
    .table-body {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .attendance-table th,
    .attendance-table td {
        padding: 16px 24px;
        border-bottom: 1px solid #eee;
    }

    .attendance-table th {
        text-align: left;
        width: 120px;
        color: #666;
        font-size: 0.9rem;
        font-weight: normal;
        vertical-align: top;
    }

    .time-input {
        width: 80px;
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .time-separator {
        margin: 0 8px;
        color: #666;
    }

    .name-value {
        color: #333;
        padding: 4px 0;
    }

    .date-container {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .date-input {
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    textarea {
        width: 100%;
        height: 80px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
        font-family: inherit;
        font-size: 0.9rem;
    }

    .button-container {
        text-align: right;
    }

    .edit-button {
        background-color: #000;
        color: white;
        border: none;
        padding: 8px 24px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background-color 0.2s;
    }

    .edit-button:hover {
        background-color: #333;
    }
</style>

@endsection
