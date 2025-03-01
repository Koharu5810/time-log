<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_time_id',
        'att_correct_id',
        'previous_break_time_start',
        'previous_break_time_end',
        'requested_break_time_start',
        'requested_break_time_end'
    ];

    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class, 'att_correct_id');
    }
    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class, 'break_time_id');
    }
}
