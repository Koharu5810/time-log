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

// ステータスが勤務外のユーザー
    public function test_attendance_status_display_for_off_duty_user(): void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $today = now()->format('Y-m-d');

        // 勤怠データを「勤務外」で作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => null,
            'clock_end' => null,
            'status' => '勤務外',
        ]);

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('勤務外');
    }
// ステータスが出勤中のユーザー
    public function test_attendance_status_display_for_working_user(): void
    {
        $user = TestHelper::userLogin()->first();
        $this->actingAs($user);

        $today = now()->format('Y-m-d');

        // 勤怠データを「出勤中」で作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => now()->format('H:i:s'),
            'clock_end' => null,
            'status' => '出勤中',
        ]);

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('出勤中');
    }
}
