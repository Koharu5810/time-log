<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

    public function test_admin_can_view_all_users_attendance_list_correctly():void
    {
        // 1. 管理者ユーザーにログインする
        $admin = Admin::where('email', 'admin1@test.com')->first();
        $this->actingAs($admin, 'admin');

        // 2. 勤怠一覧画面を開く
        $date = Carbon::today()->toDateString();
        $attendances = Attendance::whereDate('work_date', $date)->with('user', 'breakTimes')->get();

        $response = $this->get(route('admin.dashboard', [
            'year' => Carbon::today()->year,
            'month' => Carbon::today()->month,
            'day' => Carbon::today()->day,
        ]));

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

}
