<?php

namespace Tests\Integration\Models;

use App\Events\TimesheetSubmitted;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TimesheetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Dispatches TimesheetSubmitted event when timesheet submitted_at set.
     */
    public function testDispatchesTimesheetSubmittedEventWhenTimesheetSubmittedAtSet()
    {
        $this->fakeTimesheetEvents();
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            "user_id" => $user->id,
        ]);
        $timesheet->submitted_at = Carbon::now();
        $timesheet->save();
        Event::assertDispatched(TimesheetSubmitted::class);
        Event::assertDispatched(function (TimesheetSubmitted $event) use ($timesheet) {
            return $event->timesheet->id === $timesheet->id;
        });
    }

    /**
     * @todo Timesheet email_sent_at date is set when email is sent.
     */
    public function testTimesheetEmailDateSetWhenMailSent()
    {
        $this->seed();
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            "user_id" => $user->id,
        ]);
        $this->assertNull($timesheet->email_sent_at);
        $timesheet->submitted_at = Carbon::now();
        $timesheet->save();
        $timesheet = Timesheet::find($timesheet->id);
        $this->assertNotNull($timesheet->email_sent_at);
    }

    protected function fakeTimesheetEvents()
    {
        Event::fake([
            TimesheetSubmitted::class
        ]);
    }
}
