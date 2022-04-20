<?php

namespace Tests\Unit\Events;

use App\Events\TimesheetSubmitted;
use App\Models\Timesheet;
use App\Models\User;
use Tests\TestCase;

class TimesheetSubmittedTest extends TestCase
{
    /**
     * Has timesheet.
     */
    public function testHasTimesheet()
    {
        $user = User::factory()->make();
        $timesheet = Timesheet::factory()->make([
            'user_id' => $user->id,
        ]);
        $event = new TimesheetSubmitted($timesheet);
        $this->assertEquals($timesheet, $event->timesheet);
    }
}
