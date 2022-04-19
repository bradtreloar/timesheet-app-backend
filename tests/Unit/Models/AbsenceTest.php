<?php

namespace Tests\Unit\Models;

use App\Models\Absence;
use App\Models\Timesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Models\Absence
 */
class AbsenceTest extends TestCase
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
        $this->assertEquals(Timesheet::first(), Absence::first()->timesheet);
    }
}
