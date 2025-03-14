<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTimeCorrectRequest;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;
    protected $correctionRequest;
    protected $breakTimeRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // 固定の管理者アカウントを作成
        $this->admin = Admin::where('email', 'admin1@test.com')->first();

        if (!$this->admin) {
            $this->admin = Admin::create([
                'name' => '管理者',
                'email' => 'admin1@test.com',
                'password' => bcrypt('password'),
            ]);
        }
        // 固定のユーザーと勤怠データを作成
        $this->user = User::create([
            'name' => 'テストユーザー',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2025-03-14',
            'clock_in' => '09:00:00',
            'clock_end' => '18:00:00',
        ]);

        // 固定の修正申請データを作成
        $this->correctionRequest = AttendanceCorrectRequest::create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'request_status' => '承認待ち',
            'requested_clock_in' => '09:23:00',
            'requested_clock_end' => '18:22:00',
            'remarks' => '打刻漏れ'
        ]);

        // 休憩時間データ（ある場合のみ）を作成
        $this->breakTimeRequest = BreakTimeCorrectRequest::create([
            'att_correct_id' => $this->correctionRequest->id,
            'requested_break_time_start' => '10:22:00',
            'requested_break_time_end' => '10:57:00',
        ]);
    }

    private function loginAsAdmin()
    {
        // $admin = Admin::where('email', 'admin1@test.com')->first();
        // $this->actingAs($admin, 'admin');
        $this->actingAs($this->admin, 'admin');
    }

// 承認待ちの修正申請がすべて表示されている
    public function test_admin_can_view_all_pending_corrections()
    {
        $this->loginAsAdmin();

        // 修正申請一覧ページ（承認待ち）を開く
        $response = $this->get(route('request.list', ['tab' => 'pending']));
        $response->assertStatus(200);
        $response->assertSeeText('承認待ち');

        // 承認待ちの修正申請が全て表示されるか確認
        $pendingRequests = AttendanceCorrectRequest::where('request_status', '承認待ち')->get();
        foreach ($pendingRequests as $request) {
            $response->assertSee($request->user->name);
            $response->assertSee(Carbon::parse($request->attendance->work_date)->format('Y/m/d'));
        }
    }
// 承認済みの修正申請がすべて表示されている
    public function test_admin_can_view_all_approved_corrections()
    {
        $this->loginAsAdmin();

        // 修正申請一覧ページ（承認済み）を開く
        $response = $this->get(route('request.list', ['tab' => 'approved']));
        $response->assertStatus(200);
        $response->assertSeeText('承認済み');

        // 承認済みの修正申請が全て表示されるか確認
        $approvedRequests = AttendanceCorrectRequest::where('request_status', '承認済み')->get();
        foreach ($approvedRequests as $request) {
            $response->assertSee($request->user->name);
            $response->assertSee(Carbon::parse($request->attendance->work_date)->format('Y/m/d'));
        }
    }
// 修正申請の詳細内容が正しく表示されている
    public function test_admin_can_view_correction_request_details()
    {
        $this->loginAsAdmin();

        // 承認待ちの修正申請を1つ取得
        // $request = AttendanceCorrectRequest::where('request_status', '承認待ち')->first();
        // $attendance = $request->attendance;

        // 修正申請詳細ページを開く
        // $response = $this->get(route('show.request.approve', ['attendance_correct_request' => $request->id]));
        $response = $this->get(route('show.request.approve', ['attendance_correct_request' => $this->correctionRequest->id]));
        $response->assertStatus(200);

        // 申請内容が正しく表示されているか確認
        $response->assertSee($this->user->name);
        $response->assertSee(Carbon::parse($this->attendance->work_date)->format('Y年'));
        $response->assertSee(Carbon::parse($this->attendance->work_date)->format('n月j日'));
        $response->assertSee(Carbon::parse($this->correctionRequest->requested_clock_in)->format('H:i'));
        $response->assertSee(Carbon::parse($this->correctionRequest->requested_clock_end)->format('H:i'));

        $breakTimes = $this->attendance->breakTimes;
        if ($breakTimes->isNotEmpty()) {
            // foreach ($breakTimes as $index => $break) {
            foreach ($breakTimes as $break) {
                $response->assertSee(Carbon::parse($break->break_time_start)->format('H:i'));
                $response->assertSee(Carbon::parse($break->break_time_end)->format('H:i'));
            }
        } else {
            // 休憩がない場合は休憩の行が表示されないことを確認
            $response->assertDontSee('休憩');
        }

        $response->assertSee($this->correctionRequest->remarks);
        $response->assertSeeText('承認'); // 承認ボタンがあることを確認
    }
// 修正申請の承認処理が正しく行われる
    public function test_admin_can_approve_correction_request()
    {
        $this->loginAsAdmin();

        // 承認待ちの修正申請を1つ取得
        // $request = AttendanceCorrectRequest::where('request_status', '承認待ち')->first();
        // $attendance = $request->attendance;

        // 修正申請の承認処理を実行
        $response = $this->patch(route('request.approve', ['attendance_correct_request' => $this->correctionRequest->id]));
        // $response->assertRedirect(route('request.list'));

        // 修正申請が承認済みに更新されたか確認
        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $this->correctionRequest->id,
            'request_status' => '承認済み',
            // 'admin_id' => Admin::where('email', 'admin1@test.com')->first()->id,
            'admin_id' => $this->admin->id,
            'approved_at' => Carbon::now(),
        ]);

        // 勤怠情報が修正申請の内容で更新されたか確認
        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'clock_in' => $this->correctionRequest->requested_clock_in,
            'clock_end' => $this->correctionRequest->requested_clock_end,
        ]);

        // 休憩データの確認
        $correctBreakTimes = BreakTimeCorrectRequest::where('att_correct_id', $this->correctionRequest->id)->get();

        if ($correctBreakTimes->isNotEmpty()) {
            foreach ($correctBreakTimes as $correctBreak) {
                $this->assertDatabaseHas('break_times', [
                    'attendance_id' => $this->attendance->id,
                    'break_time_start' => $correctBreak->requested_break_time_start,
                    'break_time_end' => $correctBreak->requested_break_time_end,
                ]);
            }
        } else {
            // 休憩データがない場合、break_timesテーブルに該当のattendance_idのデータがないことを確認
            $this->assertDatabaseMissing('break_times', [
                'attendance_id' => $this->attendance->id,
            ]);
        }

        // 修正申請承認後、リダイレクトされているか確認
        $response->assertRedirect(route('request.list'));
    }
}
