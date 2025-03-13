<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // シーダー実行
        $this->admin = Admin::where('email', 'admin1@test.com')->first();
        $this->actingAs($this->admin, 'admin');
    }

    private function createAttendanceStatus()
    {
        $attendance = Attendance::create([
            'user_id' => 1,
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

        return compact('attendance', 'breakTime');
    }
    private function openAdminAttendanceDetailPage($attendance)
    {
        return $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertStatus(200)
            ->assertSee('<form', false);
    }
    private function assertValidationError($attendance, $data, $expectedErrors)
    {
        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors($expectedErrors);
    }

// 出勤時間が退勤時間より後になっている場合バリデーションメッセージ表示
    public function test_validation_error_when_clock_in_is_after_clock_end()
    {
        ['attendance' => $attendance] = $this->createAttendanceStatus();
        $this->openAdminAttendanceDetailPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '18:30',
            'requested_clock_end' => '17:30',
            'remarks' => '早出のため',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['requested_clock_end' => '出勤時間もしくは退勤時間が不適切な値です'];

        $this->assertValidationError($attendance, $data, $expectedErrors);
    }
// 休憩開始時間が退勤時間より後になっている場合バリデーションメッセージ表示
    public function test_validation_error_when_break_time_start_is_after_clock_end()
    {
        ['attendance' => $attendance, 'breakTime' => $break_time] = $this->createAttendanceStatus();
        $this->openAdminAttendanceDetailPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '17:30',
            'break_times' => [
                ['start' => '18:00', 'end' => '18:30'],
            ],
            'remarks' => '早出のため',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['break_times.0.start' => '休憩時間が勤務時間外です'];

        $this->assertValidationError($attendance, $data, $expectedErrors);
    }
// 休憩開始時間が退勤時間より後になっている場合バリデーションメッセージ表示
    public function test_validation_error_when_break_time_end_is_after_clock_end()
    {
        ['attendance' => $attendance, 'breakTime' => $break_time] = $this->createAttendanceStatus();
        $this->openAdminAttendanceDetailPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '17:30',
            'break_times' => [
                ['start' => '17:15', 'end' => '17:45'],
            ],
            'remarks' => '早出のため',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['break_times.0.end' => '休憩時間が勤務時間外です'];

        $this->assertValidationError($attendance, $data, $expectedErrors);
    }
// 備考欄が未入力の場合バリデーションメッセージ表示
    public function test_validation_error_when_remarks_is_missing()
    {
        ['attendance' => $attendance, 'breakTime' => $break_time] = $this->createAttendanceStatus();
        $this->openAdminAttendanceDetailPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '17:30',
            'remarks' => '',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['remarks' => '備考を記入してください'];

        $this->assertValidationError($attendance, $data, $expectedErrors);
    }
}
