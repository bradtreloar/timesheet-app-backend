<?php

namespace Tests\Unit\Models;

use App\Events\TimesheetSubmitted;
use App\Models\Absence;
use App\Models\Leave;
use App\Models\Shift;
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
     * Tests that the timesheet's shift relationship is working.
     */
    public function testShiftsRelationship()
    {
        $this->seed();
        $timesheet = Timesheet::first();
        $shifts = $timesheet->shifts;
        $this->assertCount(1, $shifts);
    }

    /**
     * Timesheet::entries returns an array of shifts, absences and leaves
     * sorted in chronological order.
     */
    public function testgetEntriesAttribute()
    {
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            "user_id" => $user->id,
        ]);
        $entries = [];
        for ($i = 6; $i >= 0; $i--) {
            switch (random_int(0, 2)) {
                case 0:
                    $entries[] = Shift::factory()->create([
                        'timesheet_id' => $timesheet->id,
                        'start' => Carbon::yesterday()->subDays($i),
                        'end' => Carbon::yesterday()->subDays($i)->addHours(8),
                    ]);
                    break;
                case 1:
                    $entries[] = Absence::factory()->create([
                        'timesheet_id' => $timesheet->id,
                        'date' => Carbon::yesterday()->subDays($i),
                    ]);
                    break;
                case 2:
                    $entries[] = Leave::factory()->create([
                        'timesheet_id' => $timesheet->id,
                        'date' => Carbon::yesterday()->subDays($i),
                    ]);
                    break;
            }
        }

        foreach ($timesheet->entries as $index => $entry) {
            $this->assertEquals(
                $entry->id,
                $entries[$index]->id
            );
            $this->assertEquals(
                get_class($entry),
                get_class($entries[$index])
            );
        }
    }
}
