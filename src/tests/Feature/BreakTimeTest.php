<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use App\Models\BreakTime;
use Tests\Helpers\TestHelper;
use Tests\TestCase;

class BreakTimeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    private function createAttendanceStatus($user, $status)
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now()->subHours(3)->format('H:i:s'),  // 3時間前に出勤
            'clock_end' => null,
            'status' => '出勤中',
        ]);
    }

// 休憩処理
    public function test_user_can_break_time_start(): void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $attendance = $this->createAttendanceStatus($user, '出勤中');

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('休憩入');

        $response = $this->post(route('attendance.store'), [
            'status' => '休憩入',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_time_start' => now()->format('H:i:s'),
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '休憩中',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('create'));

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('休憩中');
    }
}
