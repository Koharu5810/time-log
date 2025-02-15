<?php

namespace App\Listeners;

use App\Models\Attendance;
use App\Events\Registerd;
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
    public function handle(Registerd $event): void
    {
        //
    }
}
