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

        <form class="attendance-form">
            <table class="attendance-table">
                <tbody class="table-body">
                    <tr>
                        <th>名前</th>
                        <td colspan="4" class="name-value">{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td class="date-container">
                            {{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('Y年') }}
                        </td>
                        <td class="time-separator"></td>
                        <td class="date-container">
                            {{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('n月j日') }}
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td class="time">
                            <input type="time" class="time-input" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}" required>
                        </td>
                        <td class="time-separator">〜</td>
                        <td class="time">
                            <input type="time" class="time-input" value="{{ $attendance->clock_end ? \Carbon\Carbon::parse($attendance->clock_end)->format('H:i') : '' }}" required>
                        </td>
                        <td></td>
                    </tr>
                    @foreach ($attendance->breakTimes as $index => $break)
                        <tr>
                            <th>{{ $index == 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                            <td>
                                <input type="time" class="time-input" value="{{ $break->break_time_start ? \Carbon\Carbon::parse($break->break_time_start)->format('H:i') : '' }}">
                            </td>
                            <td class="time-separator">〜</td>
                            <td class="time">
                                <input type="time" class="time-input" value="{{ $break->break_time_end ? \Carbon\Carbon::parse($break->break_time_end)->format('H:i') : '' }}">
                            </td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            <textarea placeholder="電車遅延のため"></textarea>
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
