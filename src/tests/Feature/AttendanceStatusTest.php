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

    public function test_attendance_status_display_for_off_duty_user(): void
    {
        $user = TestHelper::userLogin();

        $today = Carbon::today()->format('Y-m-d');

        // 勤怠データを「勤務外」で作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => null,
            'clock_end' => null,
            'status' => '勤務外',
        ]);

        // 2. 勤怠打刻画面を開く
        $response = $this->actingAs($user)->get(route('create'));

        // 3. 画面に「勤務外」と表示されていることを確認
        $response->assertStatus(200)
                 ->assertSee('勤務外'); // Bladeテンプレートに「勤務外」があるかチェック

    }
}
