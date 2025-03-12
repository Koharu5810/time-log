<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTime;
use Tests\TestCase;
use Tests\Helpers\TestHelper;
use Carbon\Carbon;


class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $attendance;

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

        return compact('attendance', 'breakTime');
    }
    private function openLoginPage($attendance)
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
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        ['attendance' => $attendance] = $this->createAttendanceStatus($user);

        $this->openLoginPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '18:30',
            'requested_clock_end' => '17:30',
            'remarks' => '早出のため',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['requested_clock_end' => '出勤時間もしくは退勤時間が不適切な値です'];

        $this->assertValidationError($attendance, $data, $expectedErrors);

        // 修正リクエストがデータベースに保存されていないことを確認
        $this->assertDatabaseMissing('attendance_correct_requests', [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => Carbon::parse('18:30')->format('H:i:s'),
            'requested_clock_end' => Carbon::parse('17:30')->format('H:i:s'),
            'remarks' => '早出のため',
            'request_status' => '承認待ち',
        ]);

        // $response = $this->post(route('attendance.update'), $data);
        // $response->assertStatus(302);

        // $this->assertDatabaseHas('attendance_correct_requests', [
        //     'user_id' => ,
        //     'attendance_id' => ,
        //     'previous_clock_in' => ,
        //     'previous_clock_end' => ,
        //     'attendance_id' => $this->attendance->id,
        //     'requested_clock_in' => '18:30:00',
        //     'requested_clock_end' => '17:30:00',
        //     'remarks' => '早出のため',
        //     'request_status' => '承認待ち',
        //     'admin_id' => null,
        //     'approved_at' => null,
        // ]);

        // BreakTimeCorrectRequest::create([
        //     'break_time_id' => ,
        //     'att_correct_id' => ,
        //     'previous_break_time_start' => ,
        //     'previous_break_time_end' => ,
        //     'requested_break_time_start' => ,
        //     'requested_break_time_end' => ,
        // ]);
    }
// 休憩開始時間が退勤時間より後になっている場合バリデーションメッセージ表示
    public function test_validation_error_when_break_time_start_is_after_clock_end()
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        ['attendance' => $attendance, 'breakTime' => $break_time] = $this->createAttendanceStatus($user);

        $this->openLoginPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '17:30',
            'break_times' => [
                [
                    'start' => '18:00',
                    'end' => '18:30'
                ],
            ],
            'remarks' => '早出のため',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['break_times.0.start' => '休憩時間が勤務時間外です'];

        $this->assertValidationError($attendance, $data, $expectedErrors);

        // 修正リクエストがデータベースに保存されていないことを確認
        $this->assertDatabaseMissing('break_time_correct_requests', [
            'break_time_id' => $break_time->id,
            'att_correct_id' => $attendance->id,
            'previous_break_time_start' => Carbon::parse('18:00')->format('H:i:s'),
            'previous_break_time_end' => Carbon::parse('18:30')->format('H:i:s'),
        ]);
    }
// 休憩開始時間が退勤時間より後になっている場合バリデーションメッセージ表示
    public function test_validation_error_when_break_time_end_is_after_clock_end()
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        ['attendance' => $attendance, 'breakTime' => $break_time] = $this->createAttendanceStatus($user);

        $this->openLoginPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '17:30',
            'break_times' => [
                [
                    'start' => '17:15',
                    'end' => '17:45'
                ],
            ],
            'remarks' => '早出のため',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['break_times.0.end' => '休憩時間が勤務時間外です'];

        $this->assertValidationError($attendance, $data, $expectedErrors);

        // 修正リクエストがデータベースに保存されていないことを確認
        $this->assertDatabaseMissing('break_time_correct_requests', [
            'break_time_id' => $break_time->id,
            'att_correct_id' => $attendance->id,
            'previous_break_time_start' => Carbon::parse('17:15')->format('H:i:s'),
            'previous_break_time_end' => Carbon::parse('17:45')->format('H:i:s'),
        ]);
    }
// 備考欄が未入力の場合バリデーションメッセージ表示
    public function test_validation_error_when_remarks_is_missing()
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        $this->actingAs($user);

        ['attendance' => $attendance, 'breakTime' => $break_time] = $this->createAttendanceStatus($user);

        $this->openLoginPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '17:30',
            'remarks' => '',
            'request_status' => '承認待ち',
        ];
        $expectedErrors = ['remarks' => '備考を記入してください'];

        $this->assertValidationError($attendance, $data, $expectedErrors);

        // 修正リクエストがデータベースに保存されていないことを確認
        $this->assertDatabaseMissing('attendance_correct_requests', [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => Carbon::parse('18:30')->format('H:i:s'),
            'requested_clock_end' => Carbon::parse('17:30')->format('H:i:s'),
            'remarks' => '',
            'request_status' => '承認待ち',
        ]);
    }
}
