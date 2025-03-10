<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use Tests\Helpers\TestHelper;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    private function createAttendanceStatus($user)
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now()->subHours(3)->format('H:i:s'),  // 3時間前に出勤
            'clock_end' => null,
            'status' => '出勤中',
        ]);
    }

    // 退勤処理
    public function test_user_can_clock_out(): void
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        $this->createAttendanceStatus($user, '出勤中');

        $response = $this->get(route('create'));
        $response->assertStatus(200)->assertSee('退勤');

        $response = $this->post(route('attendance.store'), [
            'status' => '退勤',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '退勤済',
            'clock_end' => now()->format('H:i:s'),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('create'));

        $responseAfterRedirect = $this->get(route('create'));
        $responseAfterRedirect->assertStatus(200)->assertSee('退勤済');
    }
}
