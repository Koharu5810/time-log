<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTimeCorrectRequest;
use Carbon\Carbon;

class AttendanceRequestController extends Controller
{
// 勤怠詳細画面表示
    public function showAttendanceDetail($id) {
        $attendance = Attendance::with([
            'user',
            'breakTimes',
            'attendanceCorrectRequest.breakTimeCorrectRequests'  // ネストリレーションの取得
        ])->find($id);

        $displayBreakTimes = [];

        foreach ($attendance->breakTimes as $index => $breakTime) {
            $correction = null;
            if ($attendance->attendanceCorrectRequest) {
                $correction = $attendance->attendanceCorrectRequest->breakTimeCorrectRequests
                    ->where('break_time_id', $breakTime->id)
                    ->first();
            }

            $displayBreakTimes[] = [
                'id' => $breakTime->id,
                'index' => $index,
                'is_corrected' => !is_null($correction),
                'start' => $correction ? $correction->requested_break_time_start : $breakTime->break_time_start,
                'end' => $correction ? $correction->requested_break_time_end : $breakTime->break_time_end,
            ];
        }
        return view('attendance.detail', compact('attendance', 'displayBreakTimes'));
    }
// 勤怠詳細画面から修正申請（一般ユーザ・管理者）
    public function updateRequest(AttendanceUpdateRequest $request) {
        try {
            // **1. 勤怠修正リクエストを新規作成**
            $attendance = Attendance::find($request->attendance_id);

            // **2. 勤怠修正履歴を作成**
            $attendanceCorrectRequest = AttendanceCorrectRequest::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'previous_clock_in' => $attendance->clock_in,
                'previous_clock_end' => $attendance->clock_end,
                'requested_clock_in' => Carbon::parse($request->requested_clock_in)->format('H:i:s'),
                'requested_clock_end' => Carbon::parse($request->requested_clock_end)->format('H:i:s'),
                'remarks' => $request->remarks,
                'request_status' => '承認待ち',
                'admin_id' => null,
                'approved_at' => null,
            ]);

            // **3. 休憩データを保存**
            if (!empty($request->break_times)) {
                foreach ($request->break_times as $index => $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $breakStart = Carbon::createFromFormat('H:i', $break['start'])->format('H:i:s');
                        $breakEnd = Carbon::createFromFormat('H:i', $break['end'])->format('H:i:s');

                        if (!empty($break['id'])) {
                            // **該当の休憩時間を取得**
                            $breakTime = BreakTime::find($break['id']);
                        } else {
                            $breakTime = null;
                        }

                        // **修正前のデータを BreakTimeCorrect に保存**
                        BreakTimeCorrectRequest::create([
                            'attendance_id' => $attendance->id,
                            'att_correct_id' => $attendanceCorrectRequest->id,
                            'break_time_id' => $breakTime->id,
                            'previous_break_time_start' => $breakTime->break_time_start,
                            'previous_break_time_end' => $breakTime->break_time_end,
                            'requested_break_time_start' => $breakStart,
                            'requested_break_time_end' => $breakEnd,
                        ]);
                    }
                }
            }
            return redirect()->route('attendance.update', ['id' => $request->attendance_id]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

// 修正申請一覧画面表示（一般ユーザ・管理者）
    public function showRequestList(Request $request) {
        $isAdmin = auth('admin')->check();
        $user = $isAdmin ? auth('admin')->user() : auth('web')->user();

        $tab = $request->query('tab', 'pending');  // デフォルトは承認待ち
        $query = $request->query('query');

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $day = $request->query('day', now()->day);

        $attendanceRequests = AttendanceCorrectRequest::with(['user', 'attendance'])
            ->join('attendances', 'attendance_correct_requests.attendance_id', '=', 'attendances.id')
            ->select('attendance_correct_requests.*', 'attendances.work_date');

        // 一般ユーザーは自分の申請のみ表示
        if (!$isAdmin) {
            $attendanceRequests->where('attendance_correct_requests.user_id', $user->id);
        }

        $status = $tab === 'approved' ? '承認済み' : '承認待ち';
        $attendanceRequests->where('attendance_correct_requests.request_status', $status);
        $attendanceRequests = $attendanceRequests
            ->orderBy('attendances.work_date', 'asc')
            ->get();

        // ルート名（adminとuserで分岐）
        $routeName = $isAdmin ? 'admin.attendance.list' : 'attendance.list';

        $prevMonthParams = [
            'year' => $month == 1 ? $year - 1 : $year,
            'month' => $month == 1 ? 12 : $month - 1,
            'tab' => $tab,
            'query' => $query,
        ];
        if ($isAdmin) {
            $prevMonthParams['id'] = $user->id;
        }

        return view('attendance.request-list', compact(
            'isAdmin', 'user', 'tab', 'query', 'year', 'month', 'day', 'attendanceRequests'
        ));
    }

// 修正承認画面表示（管理者）
    public function showApprove($id)
    {
        $request = AttendanceCorrectRequest::with('attendance')->findOrFail($id);

        $attendance = $request->attendance;

        return view('admin.request-approval', compact('request', 'attendance'));
    }
// 修正勤怠承認（管理者）
    public function approve(AttendanceCorrectRequest $attendance_correct_request)
    {
        // 管理者のみが承認できるように制御
        if (!auth('admin')->check()) {
            abort(403, '管理者のみ実行可能です');
        }
        $admin = auth('admin')->user();

        $attendance_correct_request->update([
            'request_status' => '承認済み',
            'admin_id' => $admin->id,
            'approved_at' => Carbon::now(),
        ]);

        // 実際のattendanceデータを更新
        $attendance = $attendance_correct_request->attendance;
        $attendance->update([
            'clock_in' => $attendance_correct_request->requested_clock_in,
            'clock_end' => $attendance_correct_request->requested_clock_end,
            'remarks' => $attendance_correct_request->remarks,
        ]);

        // 休憩時間の更新処理
        $correctBreakTimes = BreakTimeCorrectRequest::where('att_correct_id', $attendance_correct_request->id)->get();

        foreach ($correctBreakTimes as $correctBreak) {
            $breakTime = BreakTime::find($correctBreak->break_time_id);

            if ($breakTime) {
                $breakTime->update([
                    'break_time_start' => $correctBreak->requested_break_time_start,
                    'break_time_end' => $correctBreak->requested_break_time_end,
                ]);
            } else {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_time_start' => $correctBreak->requested_break_time_start,
                    'break_time_end' => $correctBreak->requested_break_time_end,
                ]);
            }
        }

        return redirect()->route('request.list');
    }
}
