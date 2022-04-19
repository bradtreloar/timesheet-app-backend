<?php

namespace Tests\Unit\Models;

use App\Models\Leave;
use App\Models\Timesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Models\Leave
 */
class LeaveTest extends TestCase
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
        $this->assertEquals(Timesheet::first(), Leave::first()->timesheet);
    }

    /**
     * Gets hours.
     *
     * @covers ::getHoursAttribute
     */
    public function testGetHours()
    {
        $this->seed();
        $this->assertGreaterThan(0, (float) Leave::first()->hours);
    }
}
