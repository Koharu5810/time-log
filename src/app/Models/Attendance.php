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
        if ($this->clock_in && $this->clock_end) {
            $start = Carbon::today()->setTimeFromTimeString($this->clock_in);
            $end = Carbon::today()->setTimeFromTimeString($this->clock_end);
            return $start->diffInMinutes($end);
        }
        return 0; // 両方の値が揃っていない場合は0を返す
    }
    public function getTotalBreakTimeAttribute()
    {
        return $this->breakTimes->sum('duration'); // BreakTimeモデルの getDurationAttribute()を利用
    }
}
