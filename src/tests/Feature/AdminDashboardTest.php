<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Tests\TestCase;
use Carbon\Carbon;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function loginAsAdmin()
    {
        $admin = Admin::where('email', 'admin1@test.com')->first();
        $this->actingAs($admin, 'admin');
    }
    private function getAdminDashboardResponse()
    {
        return $this->get(route('admin.dashboard', [
            'year' => Carbon::today()->year,
            'month' => Carbon::today()->month,
            'day' => Carbon::today()->day,
        ]));
    }

// その日になされた全ユーザの勤怠情報を確認
    public function test_admin_can_view_all_users_attendance_list_correctly():void
    {
        $this->loginAsAdmin();
        $response = $this->getAdminDashboardResponse();

        // 勤怠一覧画面を開く
        $date = Carbon::today()->toDateString();
        $attendances = Attendance::whereDate('work_date', $date)->with('user', 'breakTimes')->get();

        $response->assertStatus(200);
        foreach ($attendances as $attendance) {
            $user = $attendance->user;
            $totalBreakTime = $attendance->total_break_time;
            $workDuration = gmdate('H:i', (($attendance->duration_in_minutes ?? 0) - ($totalBreakTime ?? 0)) * 60);

            $response->assertSee($user->name);
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

            $response->assertSee('詳細');
        }
    }
// 管理者ダッシュボードページに遷移した際、現在の日付が表示される
    public function test_admin_dashboard_shows_current_date()
    {
        $this->loginAsAdmin();
        $response = $this->getAdminDashboardResponse();

        $response->assertSeeText(Carbon::today()->format('Y年m月d日の勤怠'));
        $response->assertSee(Carbon::today()->format('Y/m/d'));
    }
// 前日を押すと前の日の勤怠情報が表示される
    public function test_admin_can_view_previous_day_attendance()
    {
        $this->loginAsAdmin();
        $response = $this->getAdminDashboardResponse();

        // 「前日」ボタンのURLを取得
        $previousDate = Carbon::today()->subDay();
        $previousUrl = route('admin.dashboard', [
            'year' => $previousDate->year,
            'month' => $previousDate->month,
            'day' => $previousDate->day,
        ]);

        $response->assertSee($previousUrl);
        $response->assertSeeText('前日');

        // 「前日」ボタンを押す
        $response = $this->get($previousUrl);

        $response->assertSeeText($previousDate->format('Y年m月d日の勤怠'));
        $response->assertSee($previousDate->format('Y/m/d'));

        $attendances = Attendance::whereDate('work_date', $previousDate)->with('user', 'breakTimes')->get();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->user->name);
        }
    }
}
