{{-- スタッフ一覧画面（管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/login.css') }}" />
@endsection

@section('sub-title', 'スタッフ一覧')

@section('content')
    <div class="body">
    <div class="container">
        <h2 class="content__sub-title">@yield('sub-title')</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffList as $staff)
                        <tr>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.list', ['id' => $staff->id]) }}" class="detail-btn">詳細</a>
                            </td>
                        </tr>
                    @endforeach
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
