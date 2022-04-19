<?php

namespace Tests\Feature;

use App\Events\TimesheetSubmitted;
use App\Mail\TimesheetNotification;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TimesheetNotificationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Tests that the timesheet submitted notification renders correctly.
     */
    public function testTimesheetNotificationRenders()
    {
        $this->seed();
        $user = User::first();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
            'submitted_at' => Carbon::now(),
        ]);

        TimesheetSubmitted::dispatch($timesheet);

        $html_output = (new TimesheetNotification($timesheet))->render();
        $this->assertStringContainsString("Timesheet submitted", $html_output);
        $this->assertStringContainsString($user->name, $html_output);
        $this->assertStringContainsString($timesheet->created_at->format("j F Y"), $html_output);
        foreach ($timesheet->shifts as $shift) {
            $this->assertStringContainsString($shift->start->format("D, j M Y"), $html_output);
            $this->assertStringContainsString($shift->start->format("H:i"), $html_output);
            $this->assertStringContainsString($shift->end->format("H:i"), $html_output);
            $this->assertStringContainsString($shift->break_duration, $html_output);
            $this->assertStringContainsString($shift->hours, $html_output);
        }
    }
}
