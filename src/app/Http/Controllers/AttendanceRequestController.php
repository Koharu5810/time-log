<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTimeCorrectRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceRequestController extends Controller
{
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
                'admin_id' => null,
                'approved_at' => null,
            ]);

            // **3. 勤怠情報を上書き**
            $attendance->update([
                'clock_in' => $request->filled('requested_clock_in')
                    ? Carbon::parse($request->requested_clock_in)->format('H:i:s')
                    : $attendance->clock_in, // 変更があれば更新、なければ維持
                'clock_end' => $request->filled('requested_clock_end')
                    ? Carbon::parse($request->requested_clock_end)->format('H:i:s')
                    : $attendance->clock_end, // 変更があれば更新、なければ維持
                'remarks' => $request->remarks,
                'request_status' => '承認待ち'
            ]);

            // **4. 休憩データを保存**
            if (!empty($request->break_times)) {
                foreach ($request->break_times as $index => $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $breakStart = Carbon::createFromFormat('H:i', $break['start'])->format('H:i:s');
                        $breakEnd = Carbon::createFromFormat('H:i', $break['end'])->format('H:i:s');

                        // **該当の休憩時間を取得**
                        $breakTime = BreakTime::where('attendance_id', $attendance->id)
                            ->orderBy('id')
                            ->get()
                            ->get($index);

                        if ($breakTime) {
                            // **修正前のデータを BreakTimeCorrect に保存**
                            BreakTimeCorrectRequest::create([
                                // 'attendance_correct_request_id' => $attendanceCorrectRequest->id,
                                'att_correct_id' => $attendanceCorrectRequest->id,
                                'break_time_id' => $breakTime->id,
                                'previous_break_time_start' => $breakTime->break_time_start,
                                'previous_break_time_end' => $breakTime->break_time_end,
                                'requested_break_time_start' => $breakStart,
                                'requested_break_time_end' => $breakEnd,
                            ]);

                            // **BreakTime を上書き**
                            $breakTime->update([
                                'break_time_start' => $breakStart,
                                'break_time_end' => $breakEnd,
                            ]);
                        } else {
                            // **新しい休憩時間を作成**
                            $newBreakTime = BreakTime::create([
                                'attendance_id' => $attendance->id,
                                'break_time_start' => $breakStart,
                                'break_time_end' => $breakEnd,
                            ]);

                            // **BreakTimeCorrectRequest に新しい休憩を追加**
                            BreakTimeCorrectRequest::create([
                                // 'attendance_correct_request_id' => $attendanceCorrectRequest->id,
                                'att_correct_id' => $attendanceCorrectRequest->id,
                                'break_time_id' => $newBreakTime->id,
                                'previous_break_time_start' => null,
                                'previous_break_time_end' => null,
                                'requested_break_time_start' => $breakStart,
                                'requested_break_time_end' => $breakEnd,
                            ]);
                        }
                    }
                }
            }
            return redirect()->route('attendance.update', ['id' => $request->attendance_id]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

// 修正勤怠承認（管理者）
    public function approve($id)
    {
        $attendance = Attendance::findOrFail($id);

        // 管理者のみが承認できるように制御
        if (!auth('admin')->user()) {
            abort(403, 'Unauthorized action.');
        }

        $attendance->request_status = '承認済み';
        $attendance->admin_id = auth('admin')->user()->id;
        $attendance->approved_at = Carbon::now();
        $attendance->save();

        return redirect()->back();
    }

// 修正申請一覧画面表示（一般ユーザ）
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
            ->select('attendance_correct_requests.*', 'attendances.work_date', 'attendances.remarks');

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
            'user', 'tab', 'query', 'year', 'month', 'day', 'attendanceRequests'
        ));
    }
}
