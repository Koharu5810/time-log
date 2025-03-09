<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use Tests\TestCase;
use Tests\Helpers\TestHelper;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
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

// ステータスが勤務外のユーザー
    public function test_attendance_status_display_for_off_duty_user(): void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $this->createAttendanceStatus($user, '勤務外');

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('勤務外');
    }
// ステータスが出勤中のユーザー
    public function test_attendance_status_display_for_working_user(): void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $this->createAttendanceStatus($user, '出勤中');

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('出勤中');
    }
// ステータスが休憩中のユーザー
    public function test_attendance_status_display_for_breaking_user(): void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $this->createAttendanceStatus($user, '休憩中');

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('休憩中');
    }
}
