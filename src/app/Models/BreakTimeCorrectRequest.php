<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'break_time_id',
        'attendance_correct_request_id',
        'previous_break_time_start',
        'previous_break_time_end',
        'requested_break_time_start',
        'requested_break_time_end'
    ];

    public function attendanceHistory()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class, 'attendance_history_id');
    }
    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class, 'break_time_id');
    }
}
