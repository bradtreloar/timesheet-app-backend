<?php

namespace Tests\Integration\Console\Commands;

use App\Console\Commands\RemindUsers;
use App\Events\TimesheetDue;
use App\Models\Shift;
use App\Models\Timesheet;
use App\Models\User;
use App\Notifications\Reminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    public static function getStartOfCurrentTimesheetWeek()
    {
        $today = Carbon::now()->startOfDay();
        $start_of_timesheet_week = Carbon::now()->startOfWeek();
        if ($start_of_timesheet_week->diffInDays($today) == 0) {
            $start_of_timesheet_week = $start_of_timesheet_week->subDays(7);
        }
        return $start_of_timesheet_week;
    }

    public function testUserHasCurrentTimesheet()
    {
        $start_of_timesheet_week = ReminderTest::getStartOfCurrentTimesheetWeek();
        
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
        ]);
        $shift_date = $start_of_timesheet_week->copy()->addDays(1);
        Shift::factory()->create([
            'timesheet_id' => $timesheet->id,
            'start' => $shift_date,
            'end' => $shift_date->copy()->addHours(8),
        ]);
        $command = new RemindUsers();
        $this->assertTrue($command->userHasCurrentTimesheet($user));
    }

    public function testUserNotHasCurrentTimesheet()
    {
        $start_of_timesheet_week = ReminderTest::getStartOfCurrentTimesheetWeek();
        
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
        ]);
        $shift_date = $start_of_timesheet_week->copy()->addDays(8);
        Shift::factory()->create([
            'timesheet_id' => $timesheet->id,
            'start' => $shift_date,
            'end' => $shift_date->copy()->addHours(8),
        ]);
        $command = new RemindUsers();
        $this->assertFalse($command->userHasCurrentTimesheet($user));
        
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
        ]);
        $shift_date = $start_of_timesheet_week->copy()->subDays(1);
        Shift::factory()->create([
            'timesheet_id' => $timesheet->id,
            'start' => $shift_date,
            'end' => $shift_date->copy()->addHours(8),
        ]);
        $command = new RemindUsers();
        $this->assertFalse($command->userHasCurrentTimesheet($user));
    }

    public function testSendNotification()
    {
        Notification::fake();
        $this->seed();
        $user = User::first();
        Event::dispatch(new TimesheetDue($user));
        Notification::assertSentTo($user, Reminder::class);
    }
}
