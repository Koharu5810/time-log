<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Attendance;
use App\Models\BreakTime;
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
            $response->assertSee('詳細');
        }
    }
// 勤怠一覧画面に遷移した際に現在の月が表示されるテスト
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
// 前月を押下すると前月の情報が表示
    public function test_attendance_list_displays_previous_month_after_clicking_previous_button(): void
    {
        $this->seed();

        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */  // 型解析ツールのエラー防止
        $this->actingAs($user);

        $currentMonthDisplay = Carbon::now()->format('Y/m');
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($currentMonthDisplay);

        $previousMonthDisplay = Carbon::now()->subMonth()->format('Y/m');

        //「前月」ボタンを押す（前月のURLへリクエスト）
        $response = $this->get(route('attendance.list', ['year' => Carbon::now()->subMonth()->year, 'month' => Carbon::now()->subMonth()->month]));
        $response->assertStatus(200);
        $response->assertSee($previousMonthDisplay);
    }
// 次月を押下すると次月の情報が表示
    public function test_attendance_list_displays_next_month_after_clicking_previous_button(): void
    {
        $this->seed();

        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */  // 型解析ツールのエラー防止
        $this->actingAs($user);

        $currentMonthDisplay = Carbon::now()->format('Y/m');
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($currentMonthDisplay);

        $nextMonthDisplay = Carbon::now()->addMonth()->format('Y/m');

        //「前月」ボタンを押す（前月のURLへリクエスト）
        $response = $this->get(route('attendance.list', ['year' => Carbon::now()->addMonth()->year, 'month' => Carbon::now()->addMonth()->month]));
        $response->assertStatus(200);
        $response->assertSee($nextMonthDisplay);
    }
// 詳細を押すと詳細画面に遷移する
    public function test_can_access_attendance_detail_page(): void
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */  // 型解析ツールのエラー防止
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in'  => Carbon::now()->setHour(9)->setMinute(0)->toTimeString(),
            'clock_end' => Carbon::now()->setHour(18)->setMinute(0)->toTimeString(),
        ]);

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);
        $response->assertSeeText('詳細');
        $response->assertSee(route('attendance.detail', ['id' => $attendance->id]));

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $formattedYear = Carbon::parse($attendance->work_date)->format('Y年');
        $formattedDate = Carbon::parse($attendance->work_date)->format('n月j日');
        // 勤務日が正しく表示されているか確認
        $response->assertSee($formattedYear);
        $response->assertSee($formattedDate);
    }
}
