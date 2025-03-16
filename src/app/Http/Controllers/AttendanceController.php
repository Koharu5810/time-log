<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
// 勤怠一覧画面表示（一般ユーザは自分の月次勤怠、管理者は任意のスタッフの月次勤怠）
    public function showAttendanceList(Request $request, $id = null) {
        if (auth('admin')->check()) {
            $authUser = auth('admin')->user();
            $staff = $id ? User::findOrFail($id) : null;
        } elseif (auth('web')->check()) {
            // 一般ユーザーの場合
            $authUser = auth('web')->user();
            $staff = null;
        } else {
            abort(403, 'Unauthorized');
        }

        $year = $request->query('year', Carbon::today()->year);
        $month = $request->query('month', Carbon::today()->month);
        $day = $request->query('day', Carbon::today()->day);
        $date = Carbon::create($year, $month, $day)->toDateString();

        // 表示対象のユーザを決定
        if (auth('admin')->check()) {
            // 管理者は対象のスタッフ
            if ($staff) {
                $user = $staff;
            } else {
                return view('attendance.attendance-list');
            }
        } else {
            // 一般ユーザは自分の勤怠データ
            $user = $authUser;
        }

        // 勤怠データ取得
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date', 'asc')
            ->get();

        foreach ($attendances as $attendance) {
            $userId = $attendance->user_id;
        }

        return view('attendance.attendance-list', compact('user', 'staff', 'year', 'month', 'attendances'));
    }
// スタッフ別勤怠リストCSV出力
    public function exportMonthlyAttendance(Request $request, $id) {
        $staff = User::findOrFail($id);

        $year = $request->query('year', Carbon::today()->year);
        $month = $request->query('month', Carbon::today()->month);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $staff->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date', 'asc')
            ->get();

        $response = new StreamedResponse(function () use ($attendances, $staff) {
            $handle = fopen('php://output', 'w');

            // CSVヘッダー
            fputcsv($handle, ['スタッフ名', '勤務日', '出勤時間', '退勤時間', '休憩時間', '勤怠合計時間']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $staff->name,
                    $attendance->work_date,
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance->clock_end ? Carbon::parse($attendance->clock_end)->format('H:i') : '',
                    $attendance->total_break_time ? gmdate('H:i', $attendance->total_break_time * 60) : '',
                    $attendance->clock_in && $attendance->clock_end
                        ? gmdate('H:i', ($attendance->duration_in_minutes - $attendance->total_break_time) * 60)
                        : '',
                ]);
            }

            fclose($handle);
        });

        $fileName = "スタッフ月次勤怠_ID{$staff->id}_{$year}_{$month}.csv";
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");

        return $response;
    }
}
