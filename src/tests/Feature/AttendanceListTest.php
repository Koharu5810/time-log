<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use Tests\TestCase;
use Tests\Helpers\TestHelper;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

// 勤怠情報がすべて表示されるテスト
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
//勤怠一覧画面に遷移した際に現在の月が表示されるテスト
    public function test_attendance_list_displays_current_month(): void
    {
        $this->seed();

        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */  // 型解析ツールのエラー防止
        $this->actingAs($user);

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        // 現在の月を取得し、ページ内に表示されていることを確認
        $currentMonth = Carbon::now()->format('Y/m');
        $response->assertSee($currentMonth);
    }
}
