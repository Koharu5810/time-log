{{-- 申請一覧画面（一般・管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/request-list.css') }}" />
@endsection

@section('sub-title', '申請一覧')

@section('content')
    <div class="request-list__container">

        <div class="request-list__header">
            <a href="{{ route('request.list', ['tab' => 'pending', 'query' => $query]) }}" class="pending {{ $tab === 'pending' ? 'active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('request.list', ['tab' => 'approved', 'query' => $query]) }}" class="approved {{ $tab === 'approved' ? 'active' : '' }}">
                承認済み
            </a>
        </div>

        <hr class="divider">

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
        {{-- タブの切替表示 --}}
                <tbody>
                    @if ($attendanceRequests->isEmpty())
                        <tr>
                            <td colspan="6">
                                {{ $tab === 'approved' ? '承認済みの申請はありません。' : '承認待ちの申請はありません。' }}
                            </td>
                        </tr>
                    @else
                        @foreach ($attendanceRequests as $request)
                            <tr>
                                <td>{{ $request->request_status }}</td>
                                <td>{{ $request->user->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($request->work_date)->format('Y/m/d') }}</td>
                                <td>{{ $request->remarks }}</td>
                                <td>{{ \Carbon\Carbon::parse($request->updated_at)->format('Y/m/d') }}</td>
                                <td>
                                    @if ($isAdmin)
                                        <a href="{{ route('request.approve', ['attendance_correct_request' => $request->id]) }}" class="detail-button">詳細</a>
                                    @else
                                        <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="detail-button">詳細</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

    </div>
@endsection
