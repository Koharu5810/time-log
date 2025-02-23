<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AttendanceRequestController extends Controller
{

// 勤怠詳細画面から修正申請（一般ユーザ）
    public function updateRequest(AttendanceUpdateRequest $request) {

        $clockIn = Carbon::createFromFormat('H:i', $request->requested_clock_in)->format('H:i:s');
        $clockEnd = Carbon::createFromFormat('H:i', $request->requested_clock_end)->format('H:i:s');

        try {
            // **1. 勤怠修正リクエストを新規作成**
            $attendanceRequest = AttendanceRequest::create([
                'user_id' => $request->user_id,
                'attendance_id' => $request->attendance_id,
                'target_date' => $request->target_date,
                'request_type' => '修正',
                'requested_clock_in' => $clockIn,
                'requested_clock_end' => $clockEnd,
                'requested_remarks' => $request->requested_remarks,
                'status' => '承認待ち',
                'admin_id' => null,
                'approved_at' => null,
            ]);

            // **2. 修正リクエストの休憩データを保存**
            if (!empty($request->requested_break_times)) {
                foreach ($request->requested_break_times as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        AttendanceRequestBreak::create([
                            'attendance_request_id' => $attendanceRequest->id,
                            'requested_break_time_start' => Carbon::createFromFormat('H:i', $break['start'])->format('H:i:s'),
                            'requested_break_time_end' => Carbon::createFromFormat('H:i', $break['end'])->format('H:i:s'),
                        ]);
                    }
                }
            }

            return redirect()->route('attendance.edit', ['id' => $request->attendance_id]);

        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

// 申請一覧画面表示（一般ユーザ）
    public function showRequestList(Request $request) {
        $user = Auth::user();

        $tab = $request->query('tab', 'pending');  // デフォルトは承認待ち
        $query = $request->query('query');

        if ($tab === 'approved') {
            // 承認済みリストを取得
            $attendanceRequests = AttendanceRequest::with(['user', 'attendance'])
                ->where('status', '承認済み')
                ->orderBy('target_date', 'asc')
                ->get();
        } else {
            // 承認待ちリストを取得
            $attendanceRequests = AttendanceRequest::with(['user', 'attendance'])
                ->where('status', '承認待ち')
                ->orderBy('target_date', 'asc')
                ->get();
        }

        return view('attendance.request-list', compact('user', 'attendanceRequests', 'tab', 'query'));
    }
}
