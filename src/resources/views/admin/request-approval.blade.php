{{-- 修正申請承認画面（管理者） --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request-approval.css') }}" />
@endsection

@section('sub-title', '勤怠詳細')

@section('content')
    <form method="POST" action="{{ route('request.approve', ['attendance_correct_request' => $request->id]) }}" class="approval-form">
        @csrf
        @method('PATCH')
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

        <table class="approval-table">
            <tbody>
        {{-- 名前 --}}
                <tr>
                    <td class="table__label">名前</td>
                    <td class="table__input">
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
                    <td class="table__label">日付</td>
                    <td class="table__input">
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
                        {{ \Carbon\Carbon::parse($attendance->attendanceCorrectRequest->requested_clock_in)->format('H:i') }}

                        <span>〜</span>

                        {{ \Carbon\Carbon::parse($attendance->attendanceCorrectRequest->requested_clock_end)->format('H:i') }}
                    </td>
                </tr>
        {{-- 休憩 --}}
                @if (!empty($displayBreakTimes))
                    @foreach ($displayBreakTimes as $break)
                        <tr>
                            <td class="table__label">{{ $break['index'] == 0 ? '休憩' : '休憩' . ($break['index'] + 1) }}</td>

                            <td class="table__input">
                                {{ \Carbon\Carbon::parse($break['start'])->format('H:i') }}

                                <span>〜</span>

                                {{ \Carbon\Carbon::parse($break['end'])->format('H:i') }}
                            </td>
                        </tr>
                    @endforeach
                @endif
        {{-- 備考 --}}
                <tr>
                    <td class="table__label">備考</td>
                    <td class="table__input">{{ $attendance->attendanceCorrectRequest->remarks }}</td>
                </tr>
            </tbody>

    {{-- 修正ボタン --}}
            <tfoot class="tfoot">
                <tr>
                    <td>  </td>  {{-- colspanが効かないため --}}
                    <td class="button-container">
                        @if ($request->request_status === '承認待ち')
                            <button type="submit" class="approve-button">承認</button>
                        @else
                            <div class="approved-message">承認済み</div>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
@endsection
