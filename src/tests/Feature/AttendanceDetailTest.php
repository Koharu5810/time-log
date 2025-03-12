<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use App\Models\BreakTime;
use Tests\TestCase;
use Tests\Helpers\TestHelper;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendanceStatus($user)
    {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_end' => '18:00:00',
        ]);

        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_time_start' => '12:00:00',
            'break_time_end' => '13:00:00',
        ]);

        $attendance->breakTimes()->save($breakTime);

        return $attendance;
    }

// 勤怠詳細画面の名前欄がログインユーザの氏名
    public function test_attendance_detail_displays_logged_in_user_name(): void
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        $attendance = $this->createAttendanceStatus($user);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSee($user->name);
    }
}
