<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'previous_clock_in',
        'previous_clock_end',
        'requested_clock_in',
        'requested_clock_end',
        'request_status',
        'admin_id',
        'approved_at',
    ];
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function breakTimeCorrectRequests()
    {
        return $this->hasMany(BreakTimeCorrectRequest::class, 'att_correct_id');
    }
}
