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
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
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
// 休憩は一日に何回も取れる
    public function test_user_can_take_multiple_breaks_in_one_day(): void
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        $attendance = $this->createAttendanceStatus($user, '出勤中');

        $this->post(route('attendance.store'), ['status' => '休憩入']);
        $this->post(route('attendance.store'), ['status' => '休憩戻']);

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('休憩入');
    }
    public function test_user_can_end_break_time(): void
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        $attendance = $this->createAttendanceStatus($user, '出勤中');

        $now = now();

        $this->post(route('attendance.store'), ['status' => '休憩入']);

        $breakTime = BreakTime::where('attendance_id', $attendance->id)->latest()->first();
        $breakStartTime = $breakTime->break_time_start;


        $this->assertDatabaseHas('break_times', [
            'id' => $breakTime->id,
            'attendance_id' => $attendance->id,
            'break_time_start' => $breakStartTime,
        ]);

        $this->post(route('attendance.store'), ['status' => '休憩戻']);

        $breakTime = BreakTime::where('attendance_id', $attendance->id)->latest()->first();
        $breakEndTime = $breakTime->break_time_end;

        $this->assertDatabaseHas('break_times', [
            'id' => $breakTime->id,
            'attendance_id' => $attendance->id,
            'break_time_end' => $breakEndTime,
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('出勤中');
    }
}
