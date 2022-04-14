<?php

namespace App\Listeners;

use App\Events\TimesheetSubmitted;
use App\Mail\TimesheetReceipt;
use App\Services\TimesheetPDFWriter;
use Illuminate\Support\Facades\Mail;

class SendTimesheetReceipt
{
    /**
     * Handle the event.
     *
     * @param  TimesheetSubmitted  $event
     * @return void
     */
    public function handle(TimesheetSubmitted $event)
    {
        $timesheet = $event->timesheet;
        Mail::to($timesheet->user)->send(new TimesheetReceipt($timesheet));
    }
}
