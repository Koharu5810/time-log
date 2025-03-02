{{-- スタッフ一覧画面（管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-list.css') }}" />
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

@endsection
