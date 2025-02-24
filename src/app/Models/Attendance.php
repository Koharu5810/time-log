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
        'clock_end',
        'status',
        'remarks',
        'request_status',
        'admin_id',
        'approved_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function history()
    {
        return $this->hasMany(AttendanceHistory::class, 'attendance_id');
    }

    // 勤務時間の計算
    public function getDurationInMinutesAttribute()
    {
        if ($this->clock_in && $this->clock_end) {
            $start = Carbon::parse($this->clock_in);
            $end = Carbon::parse($this->clock_end);
            // $start = Carbon::today()->setTimeFromTimeString($this->clock_in);
            // $end = Carbon::today()->setTimeFromTimeString($this->clock_end);
            return $start->diffInMinutes($end);
        }
        return 0; // 両方の値が揃っていない場合は0を返す
    }
    // 休憩時間の計算
    public function getTotalBreakTimeAttribute()
    {
        return $this->breakTimes->sum(function ($break) {
            if ($break->break_time_start && $break->break_time_end) {
                return Carbon::parse($break->break_time_start)->diffInMinutes(Carbon::parse($break->break_time_end));
            }
            return 0;
        });
        // return $this->breakTimes->sum('duration'); // BreakTimeモデルの getDurationAttribute()を利用
    }
    // 特定の日付の勤怠情報を取得
    public function getAttendanceByDate($date)
    {
        return Attendance::whereDate('work_date', $date)
                        ->with('user')
                        ->get();
    }
}
