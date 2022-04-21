<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\RemindUsers;
use App\Events\TimesheetDue;
use App\Models\Absence;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Console\Commands\RemindUsers
 */
class RemindUsersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Dispatches TimesheetDue event when user has no entries for current
     * timesheet week.
     *
     * @covers ::handle
     */
    public function testDispatchesTimesheetDueEvent()
    {
        Event::fake();
        $start_of_week = static::getStartOfCurrentTimesheetWeek();
        $user = static::fakeUser();

        $command = new RemindUsers();
        $command->handle();

        Event::assertDispatched(
            function (TimesheetDue $event) use ($user) {
                return $event->user->id == $user->id;
            }
        );
    }

    /**
     * Dispatches no event when user has entries for current timesheet week.
     *
     * @covers ::handle
     */
    public function testDispatchesNoEventWhenCurrentEntriesExist()
    {
        Event::fake();
        $user = static::fakeUser();
        static::fakeShift(
            static::fakeTimesheet($user),
            static::getStartOfCurrentTimesheetWeek(),
        );

        $command = new RemindUsers();
        $command->handle();

        Event::assertNotDispatched(TimesheetDue::class);
    }

    /**
     * Returns true when user has a shift in the current timesheet week.
     *
     * @covers ::userHasCurrentTimesheet
     */
    public function testFindShiftForCurrentTimesheet()
    {
        $start_of_week = static::getStartOfCurrentTimesheetWeek();
        for ($day = 0; $day < 7; $day++) {
            $user = static::fakeUser();
            static::fakeShift(
                static::fakeTimesheet($user),
                $start_of_week->copy()->addDays($day)
            );

            $command = new RemindUsers();
            $this->assertTrue($command->userHasCurrentTimesheet($user));
        }
    }

    /**
     * Returns true when user has an absence in the current timesheet week.
     *
     * @covers ::userHasCurrentTimesheet
     */
    public function testFindAbsenceForCurrentTimesheet()
    {
        $start_of_week = static::getStartOfCurrentTimesheetWeek();
        for ($day = 0; $day < 7; $day++) {
            $user = static::fakeUser();
            static::fakeAbsence(
                static::fakeTimesheet($user),
                $start_of_week->copy()->addDays($day)
            );

            $command = new RemindUsers();
            $this->assertTrue($command->userHasCurrentTimesheet($user));
        }
    }

    /**
     * Returns true when user has a leave in the current timesheet week.
     *
     * @covers ::userHasCurrentTimesheet
     */
    public function testFindLeaveForCurrentTimesheet()
    {
        $start_of_week = static::getStartOfCurrentTimesheetWeek();
        for ($day = 0; $day < 7; $day++) {
            $user = static::fakeUser();
            static::fakeLeave(
                static::fakeTimesheet($user),
                $start_of_week->copy()->addDays($day)
            );

            $command = new RemindUsers();
            $this->assertTrue($command->userHasCurrentTimesheet($user));
        }
    }

    /**
     * Returns false when user has no entries in the current timesheet week.
     *
     * @covers ::userHasCurrentTimesheet
     */
    public function testFindNoEntriesForCurrentTimesheet()
    {
        $start_of_week = static::getStartOfCurrentTimesheetWeek();
        $user = static::fakeUser();
        $timesheet = static::fakeTimesheet($user);
        static::fakeShift($timesheet, $start_of_week->copy()->addDays(7));
        static::fakeShift($timesheet, $start_of_week->copy()->subDays(1));
        static::fakeAbsence($timesheet, $start_of_week->copy()->addDays(7));
        static::fakeAbsence($timesheet, $start_of_week->copy()->subDays(1));
        static::fakeLeave($timesheet, $start_of_week->copy()->addDays(7));
        static::fakeLeave($timesheet, $start_of_week->copy()->subDays(1));

        $command = new RemindUsers();
        $this->assertFalse($command->userHasCurrentTimesheet($user));
    }

    protected static function fakeUser(): User
    {
        return User::factory()->create();
    }

    protected static function fakeTimesheet(User $user): Timesheet
    {
        return Timesheet::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    protected static function fakeShift(Timesheet $timesheet, CarbonInterface $date): Shift
    {
        return Shift::factory()->create([
            'timesheet_id' => $timesheet->id,
            'start' => $date->copy(),
            'end' => $date->copy()->addHours(8),
        ]);
    }

    protected static function fakeAbsence(Timesheet $timesheet, CarbonInterface $date): Absence
    {
        return Absence::factory()->create([
            'timesheet_id' => $timesheet->id,
            'date' => $date->copy(),
        ]);
    }

    protected static function fakeLeave(Timesheet $timesheet, CarbonInterface $date): Leave
    {
        return Leave::factory()->create([
            'timesheet_id' => $timesheet->id,
            'date' => $date->copy(),
        ]);
    }

    protected static function getStartOfCurrentTimesheetWeek()
    {
        $today = Carbon::now()->startOfDay();
        $start_of_week = Carbon::now()->startOfWeek();
        if ($start_of_week->diffInDays($today) == 0) {
            $start_of_week = $start_of_week->subDays(7);
        }
        return $start_of_week;
    }
}
