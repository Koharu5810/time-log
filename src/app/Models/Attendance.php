<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

    // 勤務時間の計算
    public function getDurationInMinutesAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $start = Carbon::parse($this->clock_in);
            $end = Carbon::parse($this->clock_out);
            return $start->diffInMinutes($end);
        }
        return 0; // 両方の値が揃っていない場合は0を返す
    }
    // 休憩時間の合計
    public function getTotalBleakTimeAttribute()
    {
        if ($this->breakTimes->isNotEmpty()) {
            $totalBreakTime = 0;

            foreach ($this->breakTimes as $break) {
                if ($break->break_time_start && $break->break_time_end) {
                    $start = Carbon::parse($this->break_time_start);
                    $end = Carbon::parse($this->break_time_end);
                    $totalBreakTime += $start->diffInMinutes($end);
                }
            }
            return $totalBreakTime;
        }
        return 0; // 休憩データがない場合は0を返す
    }
}
