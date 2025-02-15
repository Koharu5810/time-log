<?php

namespace App\Listeners;

use App\Models\Attendance;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;

class CreateAttendanceAfterRegistration
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // 勤務日（work_date）のみを登録
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->format('Y-m-d'), // 今日の日付
        ]);
    }
}
