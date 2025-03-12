<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use Tests\TestCase;
use Tests\Helpers\TestHelper;
use Carbon\Carbon;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_users_attendance_list_correctly():void
    {
        $this->seed();

        $admin = TestHelper::adminLogin();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $attendances = Attendance::all();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->user->name);
            $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
            // $response->assertSee(Carbon::parse($attendance->clock_end ?? '')->format('H:i')); // 退勤前はnullの場合がある
            if ($attendance->clock_end !== null) {
            $response->assertSeeText(Carbon::parse($attendance->clock_end)->format('H:i'));
            } else {
                $response->assertDontSeeText('退勤時間'); // 退勤時間が表示されていないことを確認
            }
            // $response->assertSee($attendance->total_break_time); // 休憩時間 (分)
            // 勤務合計時間 (勤務時間 - 休憩時間) を計算し、表示されていることを確認
            // $work_time = $attendance->duration_in_minutes - $attendance->total_break_time;
            // $work_time = ($attendance->duration_in_minutes ?? 0) - ($attendance->total_break_time ?? 0);
            // $response->assertSee($work_time);
            $response->assertSeeText('詳細');
        }
    }


}
