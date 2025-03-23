<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // シーダーデータを実行
        Carbon::setLocale('ja'); // 言語設定を日本語にする
    }

    private function loginAsAdmin()
    {
        $admin = Admin::where('email', 'admin1@test.com')->first();
        $this->actingAs($admin, 'admin');
    }
    private function getAdminAttendanceResponse($user)
    {
        return $this->get(route('admin.attendance.list', [
            'id' => $user->id,
            'year' => Carbon::today()->year,
            'month' => Carbon::today()->month,
            'day' => Carbon::today()->day,
        ]));
    }

// 管理者ユーザが全一般ユーザの氏名・メールアドレスを確認できる
    public function test_admin_can_view_all_users_info()
    {
        $this->loginAsAdmin();
        $response = $this->get(route('staff.list'));
        $response->assertStatus(200);

        $users = User::all();
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
            $response->assertSeeText('詳細');
        }
    }
// ユーザの勤怠情報が正しく表示される
    public function test_admin_can_view_staff_attendance()
    {
        $this->loginAsAdmin();
        $user = User::first();
        $response = $this->getAdminAttendanceResponse($user);

        $response->assertStatus(200);

        $currentMonth = Carbon::now()->month;
        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('work_date', $currentMonth)
            ->get();

        foreach ($attendances as $attendance) {
            $user = $attendance->user;
            $totalBreakTime = $attendance->total_break_time;
            $workDuration = gmdate('H:i', (($attendance->duration_in_minutes ?? 0) - ($totalBreakTime ?? 0)) * 60);

            $formattedDate = Carbon::parse($attendance->work_date)->translatedFormat('m/d(D)');
            if (Carbon::parse($attendance->work_date)->month === $currentMonth) {
                $response->assertSee($formattedDate);
            }

            $response->assertSee(Carbon::parse($attendance->work_date)->translatedFormat('m/d(D)'));
            $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));

            // 退勤時間がある場合のみチェック
            if ($attendance->clock_end) {
                $response->assertSee(Carbon::parse($attendance->clock_end)->format('H:i'));
            } else {
                $response->assertDontSee('00:00'); // 退勤していない場合の対応
            }

            // 休憩時間がある場合のみチェック
            if ($totalBreakTime > 0) {
            $response->assertSee(gmdate('H:i', $totalBreakTime * 60));
            } else {
                $response->assertDontSee('00:00');
            }

            // 勤務合計時間がある場合のみチェック
            if ($attendance->clock_end) {
                $response->assertSee($workDuration);
            }

            $response->assertSeeText('詳細');
        }
    }
// 前月を押した際に表示月の前月の情報が表示される
    public function test_admin_can_view_previous_month_attendance()
    {
        $this->loginAsAdmin();
        $user = User::first();
        $response = $this->getAdminAttendanceResponse($user);

        $response->assertStatus(200);
        $response->assertSeeText('前月');

        // 「前月」のボタンのURLを取得
        $previousMonth = Carbon::now()->subMonth();
        $previousUrl = route('admin.attendance.list', [
            'id' => $user->id,
            'year' => $previousMonth->year,
            'month' => $previousMonth->month,
        ]);

        // 「前月」ボタンを押す
        $response = $this->get($previousUrl);
        $response->assertStatus(200);
        $response->assertSeeText($user->name . 'さんの勤怠');
        $response->assertSee($previousMonth->format('Y/m'));

        // 前月の勤怠が表示されているか確認
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $previousMonth->year)
            ->whereMonth('work_date', $previousMonth->month)
            ->with('user', 'breakTimes')
            ->get();

        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->work_date)->translatedFormat('m/d(D)'));
            $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));

            if ($attendance->clock_end) {
                $response->assertSee(Carbon::parse($attendance->clock_end)->format('H:i'));
            } else {
                $response->assertDontSee('00:00');
            }

            if ($attendance->total_break_time > 0) {
                $response->assertSee(gmdate('H:i', $attendance->total_break_time * 60));
            } else {
                $response->assertDontSee('00:00');
            }

            if ($attendance->clock_end) {
                $workDuration = gmdate('H:i', (($attendance->duration_in_minutes ?? 0) - ($attendance->total_break_time ?? 0)) * 60);
                $response->assertSee($workDuration);
            }

            $response->assertSeeText('詳細');
        }
    }
// 翌月を押した際に表示月の次月の情報が表示される
    public function test_admin_can_view_next_month_attendance()
    {
        $this->loginAsAdmin();
        $user = User::first();
        $response = $this->getAdminAttendanceResponse($user);

        $response->assertStatus(200);
        $response->assertSeeText('翌月');

        // 「翌月」のボタンのURLを取得
        $nextMonth = Carbon::now()->addMonth();
        $nextUrl = route('admin.attendance.list', [
            'id' => $user->id,
            'year' => $nextMonth->year,
            'month' => $nextMonth->month,
        ]);

        // 「翌月」ボタンを押す
        $response = $this->get($nextUrl);
        $response->assertStatus(200);
        $response->assertSeeText($user->name . 'さんの勤怠');
        $response->assertSee($nextMonth->format('Y/m'));

        // 翌月の勤怠が表示されているか確認
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $nextMonth->year)
            ->whereMonth('work_date', $nextMonth->month)
            ->with('user', 'breakTimes')
            ->get();

        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->work_date)->translatedFormat('m/d(D)'));
            $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));

            if ($attendance->clock_end) {
                $response->assertSee(Carbon::parse($attendance->clock_end)->format('H:i'));
            } else {
                $response->assertDontSee('00:00');
            }

            if ($attendance->total_break_time > 0) {
                $response->assertSee(gmdate('H:i', $attendance->total_break_time * 60));
            } else {
                $response->assertDontSee('00:00');
            }

            if ($attendance->clock_end) {
                $workDuration = gmdate('H:i', (($attendance->duration_in_minutes ?? 0) - ($attendance->total_break_time ?? 0)) * 60);
                $response->assertSee($workDuration);
            }

            $response->assertSeeText('詳細');
        }
    }
// 詳細を押すとその日の勤怠詳細画面に遷移する
    public function test_admin_can_view_attendance_details()
    {
        $this->loginAsAdmin();
        $user = User::first();
        $attendance = Attendance::where('user_id', $user->id)->first();

        $response = $this->get(route('admin.attendance.list', ['id' => $user->id]));
        $response->assertStatus(200);
        $response->assertSeeText('詳細');

        $detailUrl = route('attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        // 「詳細」ボタンを押す
        $response = $this->get($detailUrl);
        $response->assertStatus(200);

        $response->assertSee(Carbon::parse($attendance->work_date)->format('Y年'));
        $response->assertSee(Carbon::parse($attendance->work_date)->format('n月j日'));
    }
}
