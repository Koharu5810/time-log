<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use App\Models\BreakTime;
use Tests\Helpers\TestHelper;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
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
            'clock_in' => $status === '勤務外' ? null : now()->subHours(3)->format('H:i:s'),  // 3時間前に出勤
            'clock_end' => null,
            'status' => $status,
        ]);
    }

// 出勤処理
    public function test_user_can_clock_in(): void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $this->createAttendanceStatus($user, '勤務外');

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('出勤');

        $response = $this->post(route('attendance.store'), [
            'status' => '出勤',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
            'clock_in' => now()->format('H:i:s'),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('create'));
    }
// 出勤は1日1回のみ可能
    public function test_user_cannot_clock_in_again_after_clocking_in():void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $this->createAttendanceStatus($user, '退勤済');

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertDontSee('出勤');
    }
}
