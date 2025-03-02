<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Exception;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = range(1, 9);

        foreach ($users as $userId) {
            $createdCount = 0;
            $daysAgo = 0;
            $correctionDays = []; // 修正申請を入れる日

            // 過去20営業日のうち、3〜5日ランダムに修正対象日を選ぶ
            while (count($correctionDays) < rand(3, 5)) {
                $date = Carbon::now()->subDays($daysAgo);
                if ($date->isWeekend()) {
                    $daysAgo++;
                    continue;
                }
                if ($daysAgo < 20) { // 過去20営業日の範囲
                    $correctionDays[] = $date->format('Y-m-d');
                }
                $daysAgo++;
            }

            // 過去90日分の勤怠データ作成
            $createdCount = 0;
            $daysAgo = 0;

            while ($createdCount < 90) {
                $date = Carbon::now()->subDays($daysAgo);

                if ($date->isWeekend()) {
                    $daysAgo++;
                    continue;
                }

                $clockInHour = rand(7, 10); // 出勤 7:00〜10:00
                $clockInMinute = rand(0, 59);
                $clockIn = Carbon::createFromTime($clockInHour, $clockInMinute, 0);

                // 出勤と退勤時間のランダム設定
                $workHours = rand(4, 10);
                $clockEnd = (clone $clockIn)->addHours($workHours);

              // 休憩回数の決定
                $breakCount = 0;
                if ($workHours < 6) {
                    // 6時間未満勤務なら休憩0回または1回
                    $breakCount = rand(0, 1);
                } else {
                    // 6時間以上勤務なら2回または3回
                    $breakCount = rand(2, 3);
                }

                // 修正申請がある日は備考を入れる
                $remarksList = ['電車遅延のため', '早退のため', '打刻漏れ'];
                $remarks = in_array($date->format('Y-m-d'), $correctionDays) ? $remarksList[array_rand($remarksList)] : null;

                // 勤怠データの作成
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in' => $clockIn->format('H:i:s'),
                    'clock_end' => $clockEnd->format('H:i:s'),
                    'status' => '退勤済',
                ]);

                // 修正申請の作成
                if ($remarks) {
                    AttendanceCorrectRequest::create([
                        'user_id' => $userId,
                        'attendance_id' => $attendance->id,
                        'previous_clock_in' => $attendance->clock_in,
                        'previous_clock_end' => $attendance->clock_end,
                        'requested_clock_in' => Carbon::parse($attendance->clock_in)
                            ->addMinutes(rand(-30, 30)) // 少し前後にズラして修正申請
                            ->format('H:i:s'),
                        'requested_clock_end' => Carbon::parse($attendance->clock_end)
                            ->addMinutes(rand(-30, 30)) // 少し前後にズラして修正申請
                            ->format('H:i:s'),
                        'remarks' => $remarks,
                        'request_status' => '承認待ち',
                        'admin_id' => null,
                        'approved_at' => null,
                    ]);
                }

                $breaks = [];

                if ($breakCount > 0) {
                    // 休憩時間の計算ロジック
                    $timeCursor = clone $clockIn; // 出勤時刻から順番に時間を進める

                    for ($i = 0; $i < $breakCount; $i++) {
                        // 前の休憩から2時間以上間隔を空ける
                        $timeCursor->addHours(rand(2, 3));
                        // 休憩開始
                        $breakStart = clone $timeCursor;
                        // 休憩時間（30〜60分）
                        $breakMinutes = rand(30, 60);
                        // 休憩終了
                        $breakEnd = (clone $breakStart)->addMinutes($breakMinutes);
                        // 退勤時間を超えたら、休憩を終了に合わせる
                        if ($breakEnd->greaterThanOrEqualTo($clockEnd)) {
                            break;
                        }

                        $breaks[] = [
                            'attendance_id' => $attendance->id,
                            'break_time_start' => $breakStart->format('H:i:s'),
                            'break_time_end' => $breakEnd->format('H:i:s'),
                        ];

                        // 次の休憩開始位置を更新
                        $timeCursor = clone $breakEnd;
                    }
                }

                // 休憩データ登録
                foreach ($breaks as $break) {
                    BreakTime::create($break);
                }

                $createdCount++;
                $daysAgo++;
            }
        }

        $statusMap = [
            2 => '出勤中',    // 出勤中（未休憩）
            3 => '休憩中',    // 1回目の休憩中
            4 => '出勤中',    // 1回目の休憩後の出勤中
            5 => '休憩中',    // 2回目の休憩中
            6 => '出勤中',    // 2回目の休憩後の出勤中
            7 => '退勤済',    // 休憩なしで退勤済
            8 => '退勤済',    // 1回の休憩後、退勤済
            9 => '退勤済',    // 2回の休憩後、退勤済
        ];

        foreach ($statusMap as $userId => $status) {
            $workDate = Carbon::today()->format('Y-m-d');

            // 出勤データの作成
            $clockIn = Carbon::createFromTime(rand(7, 12), rand(0, 59)); // 出勤時間
            $attendance = Attendance::create([
                'user_id' => $userId,
                'work_date' => $workDate,
                'clock_in' => $clockIn->format('H:i:s'),
                'clock_end' => null,
                'status' => '出勤中',
            ]);

            // ステータスごとの処理
            switch ($userId) {
                case 2:
                    // 通常の出勤中
                    break;

                case 3:
                    // 1回目の休憩中
                    $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    break;

                case 4:
                    // 1回目の休憩後の出勤中
                    $break = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break);
                    $attendance->update([
                        'status' => '出勤中',
                    ]);
                    break;

                case 5:
                    // 2回目の休憩中
                    $break1 = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break1);
                    $this->startBreakTime($attendance, $break1->break_time_end, '2回目の休憩中');
                    break;

                case 6:
                    // 2回目の休憩後の出勤中
                    $break1 = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break1);

                    $attendance->update([
                        'status' => '出勤中',
                    ]);

                    $break2 = $this->startBreakTime($attendance, $break1->break_time_end, '2回目の休憩中');
                    $this->endBreakTime($attendance, $break2);
                    $attendance->update([
                        'status' => '出勤中',
                    ]);
                    break;

                case 7:
                    // 退勤済
                    $workDuration = rand(120, 300); // 2〜5時間
                    $this->safeClockOut($attendance, $clockIn, $workDuration);

                    $attendance->update([
                        'status' => '出勤中',
                    ]);
                    break;

                case 8:
                    // 1回の休憩を取り退勤
                    $break = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break);

                    $attendance->update([
                        'status' => '出勤中',
                    ]);

                    $workDuration = rand(300, 540); // 5〜9時間
                    $this->safeClockOut($attendance, $attendance->clock_in, $workDuration);
                    break;

                case 9:
                    // 2回の休憩を取り退勤
                    $break1 = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break1);

                    $attendance->update([
                        'status' => '出勤中',
                    ]);

                    $break2 = $this->startBreakTime($attendance, $break1->break_time_end, '2回目の休憩中');
                    $this->endBreakTime($attendance, $break2);

                    $attendance->update([
                        'status' => '出勤中',
                    ]);

                    $workDuration = rand(480, 600); // 8〜10時間
                    $this->safeClockOut($attendance, $attendance->clock_in, $workDuration);
                    break;
            }
        }
    }

    // 休憩開始
    private function startBreakTime($attendance, $start, $breakLabel)
    {
        $breakStart = is_string($start)
            ? Carbon::parse($start)->addMinutes(rand(120, 180))
            : (clone $start)->addMinutes(rand(120, 180));

        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_time_start' => $breakStart->format('H:i:s'),
            'break_time_end' => null,
        ]);

        // 休憩中に更新
        $attendance->update([
            'status' => '休憩中',
        ]);

        return $break; // 休憩終了時間を返す
    }

    // 休憩終了
    private function endBreakTime($attendance, $break)
    {
        $breakEnd = Carbon::parse($break->break_time_start)->addMinutes(rand(15, 60)); // 15〜60分後に終了

        $break->update([
            'break_time_end' => $breakEnd->format('H:i:s')
        ]);

        // 既に「1回目の休憩後出勤ダミーデータ」がある場合は上書きしない
        $currentRemarks = $attendance->remarks;
        if (strpos($currentRemarks, '休憩後出勤') === false) {
            $attendance->update([
                'status' => '出勤中',
            ]);
        }
    }

    // 出勤中のときのみ退勤可
    private function safeClockOut($attendance, $clockIn, $workDuration)
    {
        if ($attendance->status !== '出勤中') {
            // 出勤中以外の場合は退勤できない
            throw new Exception("メンバーID {$attendance->user_id} は「出勤中」ではないため退勤できません。");
        }

        // clockInがnullでないか確認し、Carbonインスタンスに変換
        if (is_string($clockIn)) {
            $clockIn = Carbon::parse($clockIn);
        } elseif (is_null($clockIn)) {
            $clockIn = Carbon::parse($attendance->clock_in);
        }

        $clockOut = (clone $clockIn)->addMinutes($workDuration);
        $attendance->update([
            'clock_end' => $clockOut->format('H:i:s'),
        ]);
    }
}
