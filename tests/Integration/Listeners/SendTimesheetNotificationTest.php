<?php

namespace App\Listeners;

use App\Events\TimesheetSubmitted;
use App\Mail\TimesheetNotification;
use App\Models\Setting;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendTimesheetNotificationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Sends receipt mail to timesheet recipients when event dispatched.
     */
    public function testSendsReceiptMailToTimesheetRecipientsWhenEventDispatch()
    {
        Mail::fake();
        $this->seed();
        $user = User::first();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
            'submitted_at' => Carbon::now(),
        ]);

        TimesheetSubmitted::dispatch($timesheet);

        $recipientsSetting = Setting::where('name', 'timesheetRecipients')->first();
        $recipients = explode(",", $recipientsSetting->value);
        foreach ($recipients as $recipient) {
            Mail::assertSent(function (TimesheetNotification $mail) use ($timesheet, $recipient) {
                return $mail->timesheet->id === $timesheet->id &&
                    $mail->hasTo($recipient);
            });
        }
    }
}
