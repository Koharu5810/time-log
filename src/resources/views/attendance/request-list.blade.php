{{-- 申請一覧画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('sub-title', '申請一覧')

@section('content')
<div class="body">
    <div class="container">
        <h2 class="content__sub-title">@yield('sub-title')</h2>
        <div class="flex items-center gap-8 px-4 py-2 text-sm text-gray-600">
            <span>承認待ち</span>
            <span>承認済み</span>
        </div>
        <div className="h-px bg-gray-200" /></div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach($attendances as $attendance) --}}
                        <tr>
                            {{-- <td>{{ $attendance->date->format('m/d') }}</td>
                            <td>{{ $attendance->start_time->format('H:i') }}</td>
                            <td>{{ $attendance->end_time->format('H:i') }}</td>
                            <td>{{ $attendance->break_time->format('H:i') }}</td>
                            <td>{{ $attendance->working_hours->format('H:i') }}</td> --}}
                            <td>承認待ち</td>
                            <td>西 怜奈</td>
                            <td>2023/06/01</td>
                            <td>遅延のため</td>
                            <td>2023/06/02</td>
                            <td><button class="detail-btn"><a href="{{ route("request.detail") }}">詳細</a></button></td>
                        </tr>
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

    <style>

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

        .month-selector .current-month {
            font-weight: 500;
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
