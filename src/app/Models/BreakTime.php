<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_time_start',
        'break_time_end',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
    public function breakTimeCorrectRequest()
    {
        return $this->hasOne(BreakTimeCorrectRequest::class, 'break_time_id');
    }

    // 休憩時間の計算
    public function getDurationAttribute()
    {
        if (!empty($this->break_time_start) && !empty($this->break_time_end)) {
            $start = Carbon::createFromFormat('H:i:s', $this->break_time_start);
            $end = Carbon::createFromFormat('H:i:s', $this->break_time_end);
            return $start->diffInMinutes($end);
        }
        return 0; // 休憩データがない場合は0を返す
    }
}
