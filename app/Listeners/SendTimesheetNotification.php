<?php

namespace App\Listeners;

use App\Events\TimesheetSubmitted;
use App\Mail\TimesheetNotification;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendTimesheetNotification
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

        if ($timesheet->email_sent_at == null) {
            $recipientsSetting = Setting::where('name', 'timesheetRecipients')->first();
            $recipients = explode(",", $recipientsSetting->value);
    
            foreach ($recipients as $recipient) {
                Mail::to(trim($recipient))
                    ->send(new TimesheetNotification($timesheet));
            }
    
            $timesheet->email_sent_at = Carbon::now();
        }
    }
}
