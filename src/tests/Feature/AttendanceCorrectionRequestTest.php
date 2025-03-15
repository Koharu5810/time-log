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

    private $admin;
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

// 修正申請が正常に実行されることを確認
    public function test_correction_request_is_executed_successfully()
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        ['attendance' => $attendance] = $this->createAttendanceStatus($user);

        $this->openLoginPage($attendance);

        $data = [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '08:30',
            'requested_clock_end' => '17:30',
            'remarks' => '早出勤務',
        ];

        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), $data);

        // 修正申請がDBに保存されているか確認
        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'previous_clock_in' => $attendance->clock_in,
            'previous_clock_end' => $attendance->clock_end,
            'requested_clock_in' => '08:30:00',
            'requested_clock_end' => '17:30:00',
            'request_status' => '承認待ち',
            'admin_id' => null,
            'approved_at' => null,
        ]);

        $request = AttendanceCorrectRequest::where('attendance_id', $attendance->id)->first();

        // 管理者が承認
        $admin = TestHelper::adminLogin();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('request.list', ['attendance_correct_request' => $request->id, 'tab' => 'pending']));
        $response->assertStatus(200);

        // 管理者申請一覧画面
        $response->assertSee('承認待ち')
                ->assertSeeText($user->name)
                ->assertSee(\Carbon\Carbon::parse($attendance->work_date)->format('Y/m/d'))
                ->assertSeeText('早出勤務')
                ->assertSee(\Carbon\Carbon::now()->format('Y/m/d'));

        // 管理者承認画面
        $response = $this->get(route('show.request.approve', ['attendance_correct_request' => $request->id]));
        $response->assertStatus(200);
        $response->assertSeeText('詳細');

        $carbonDate = Carbon::parse($attendance->work_date);
        $yearPart = $carbonDate->translatedFormat('Y年');
        $monthDayPart = $carbonDate->translatedFormat('n月j日');

        $response->assertSee($user->name)
                ->assertSeeText($yearPart)
                ->assertSeeText($monthDayPart)
                ->assertSeeText('早出勤務')
                ->assertSeeText('承認');

        // 出勤・退勤時間の確認
        $response->assertSeeText(Carbon::parse($attendance->attendanceCorrectRequest->requested_clock_in)->format('H:i'))
                ->assertSeeText(Carbon::parse($attendance->attendanceCorrectRequest->requested_clock_end)->format('H:i'));
    }
// 「承認待ち」タブにユーザーの申請が全て表示されることを確認
    public function test_pending_requests_are_displayed_correctly()
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        ['attendance' => $attendance1] = $this->createAttendanceStatus($user);
        ['attendance' => $attendance2] = $this->createAttendanceStatus($user);

        // 修正申請を作成
        AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'previous_clock_in' => $attendance1->clock_in,
            'previous_clock_end' => $attendance1->clock_end,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '18:30',
            'remarks' => '電車遅延',
            'request_status' => '承認待ち',
            'admin_id' => null,
            'approved_at' => null,
        ]);
        AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance2->id,
            'previous_clock_in' => $attendance2->clock_in,
            'previous_clock_end' => $attendance2->clock_end,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '18:30',
            'remarks' => '電車遅延',
            'request_status' => '承認待ち',
            'admin_id' => null,
            'approved_at' => null,
        ]);

        // 申請一覧画面で「承認待ち」タブを開く
        $response = $this->get(route('request.list', ['tab' => 'pending']));

        // 2件の申請が表示されていることを確認
        $response->assertSee('承認待ち')
                ->assertSee($attendance1->id)
                ->assertSee($attendance2->id);
    }
// 「承認済み」タブに管理者が承認した申請が表示されることを確認
    public function test_approved_requests_are_displayed_correctly()
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        ['attendance' => $attendance] = $this->createAttendanceStatus($user);

        // 修正申請作成
        $request = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'previous_clock_in' => $attendance->clock_in,
            'previous_clock_end' => $attendance->clock_end,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '18:30',
            'remarks' => '電車遅延',
            'request_status' => '承認待ち',
            'admin_id' => null,
            'approved_at' => null,
        ]);

        // 管理者が承認
        $admin = TestHelper::adminLogin();
        $this->actingAs($admin, 'admin');

        $this->patch(route('request.approve', ['attendance_correct_request' => $request->id]));

        // 申請一覧の「承認済み」タブを開く
        $response = $this->get(route('request.list', ['tab' => 'approved']));

        // 承認済みの申請が表示されることを確認
        $response->assertSee('承認済み')
                ->assertSeeText($user->name)
                ->assertSee(\Carbon\Carbon::parse($attendance->work_date)->format('Y/m/d'))
                ->assertSeeText('電車遅延')
                ->assertSee(\Carbon\Carbon::parse($attendance->attendanceCorrectRequest->first()->created_at)->format('Y/m/d'));
    }
// 「詳細」ボタンを押すと申請詳細画面に遷移することを確認
    public function test_clicking_details_redirects_to_request_detail_page()
    {
        $user = TestHelper::userLogin();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        ['attendance' => $attendance] = $this->createAttendanceStatus($user);

        // 修正申請作成
        $request = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'previous_clock_in' => $attendance->clock_in,
            'previous_clock_end' => $attendance->clock_end,
            'requested_clock_in' => '09:30',
            'requested_clock_end' => '18:30',
            'remarks' => '電車遅延',
            'request_status' => '承認待ち',
            'admin_id' => null,
            'approved_at' => null,
        ]);

        // 申請一覧画面を開く
        $response = $this->get(route('request.list'));

        // 一般ユーザーの場合の詳細リンク
        $expectedUrl = route('attendance.detail', ['id' => $attendance->id]);
        $response->assertSeeText('詳細');

        // 詳細画面に遷移できるかテスト
        $detailResponse = $this->get($expectedUrl);
        $detailResponse->assertStatus(200)
                    ->assertSee($attendance->id)
                    ->assertSee('承認待ち');
    }
}
