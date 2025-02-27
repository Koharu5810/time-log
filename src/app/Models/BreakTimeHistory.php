<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeHistory extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'attendance_history_id',
    //     'break_time_id',
    //     'previous_break_time_start',
    //     'previous_break_time_end',
    //     'requested_break_time_start',
    //     'requested_break_time_end'
    // ];

    // public function attendanceHistory()
    // {
    //     return $this->belongsTo(AttendanceHistory::class, 'attendance_history_id');
    // }
    // public function breakTime()
    // {
    //     return $this->belongsTo(BreakTime::class, 'break_time_id');
    // }
}
