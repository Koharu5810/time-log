{{-- 勤怠一覧画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('sub-title', '勤怠一覧')

@section('content')
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>実働</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->format('m/d') }}</td>
                <td>{{ $attendance->start_time->format('H:i') }}</td>
                <td>{{ $attendance->end_time->format('H:i') }}</td>
                <td>{{ $attendance->break_time->format('H:i') }}</td>
                <td>{{ $attendance->working_hours->format('H:i') }}</td>
                <td>{{ $attendance->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- <div class="pagination">
        {{ $attendances->links() }}
    </div> --}}

    <style>
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .attendance-table th {
            background-color: #f5f5f5;
        }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
@endsection

