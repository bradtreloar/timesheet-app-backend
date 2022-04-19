<?php

namespace Tests\Unit\Models;

use App\Models\Shift;
use App\Models\Timesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Models\Shift
 */
class ShiftTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Has timesheet relationship.
     *
     * @covers ::timesheet
     */
    public function testHasTimesheetRelationship()
    {
        $this->seed();
        $this->assertEquals(Timesheet::first(), Shift::first()->timesheet);
    }

    /**
     * Gets start date as entry date.
     *
     * @covers ::getDateAttribute
     */
    public function testGetDate()
    {
        $this->seed();
        $shift = Shift::first();
        $this->assertEquals($shift->start, $shift->date);
    }

    /**
     * Gets hours.
     *
     * @covers ::getHoursAttribute
     */
    public function testGetHours()
    {
        $this->seed();
        $this->assertGreaterThan(0, (float) Shift::first()->hours);
    }
}
