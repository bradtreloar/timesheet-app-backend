<?php

namespace Tests\Integration\Listeners;

use App\Events\TimesheetSubmitted;
use App\Mail\TimesheetReceipt;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendTimesheetReceiptTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Sends receipt mail to user when event dispatched.
     */
    public function testSendsReceiptMailToUserWhenEventDispatch()
    {
        Mail::fake();
        $this->seed();
        $user = User::first();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
            'submitted_at' => Carbon::now(),
        ]);

        TimesheetSubmitted::dispatch($timesheet);

        Mail::assertSent(function (TimesheetReceipt $mail) use ($timesheet) {
            return $mail->timesheet->id === $timesheet->id &&
                $mail->hasTo($timesheet->user);
        });
    }
}
