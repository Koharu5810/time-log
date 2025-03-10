<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use Tests\TestCase;
use Tests\Helpers\TestHelper;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_all_attendance_records(): void
    {
        $this->seed();

        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $attendances = Attendance::where('user_id', $user->id)->get();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date);
            $response->assertSee($attendance->clock_in);
            $response->assertSee($attendance->clock_end ?? ''); // 退勤前はnullの場合がある
            $response->assertSee($attendance->total_break_time); // 休憩時間 (分)
            // 勤務合計時間 (勤務時間 - 休憩時間) を計算し、表示されていることを確認
            $work_time = $attendance->duration_in_minutes - $attendance->total_break_time;
            $response->assertSee($work_time);
        }
    }
}
