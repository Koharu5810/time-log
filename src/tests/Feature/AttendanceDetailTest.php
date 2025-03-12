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

// 名前欄がログインユーザの氏名の表示
    public function test_attendance_detail_displays_logged_in_user_name(): void
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        $attendance = $this->createAttendanceStatus($user);

        // 勤怠データがログインユーザーと紐づいているか確認
        $this->assertEquals($user->id, $attendance->user_id);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSeeText($user->name);
    }
// 選択した日付の表示
    public function test_attendance_detail_displays_logged_in_work_date(): void
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        $attendance = $this->createAttendanceStatus($user);

        $this->assertEquals($user->id, $attendance->user_id);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $carbonDate = Carbon::parse($attendance->work_date);
        $yearPart = $carbonDate->format('Y年');
        $monthDayPart = $carbonDate->format('n月j日');

        // ビューに正しい形式で表示されているか確認
        $response->assertSeeText($yearPart);
        $response->assertSeeText($monthDayPart);
    }
}
