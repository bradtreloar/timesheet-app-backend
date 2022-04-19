<?php

namespace App\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests that the leave model works.
     *
     * @return void
     */
    public function testDatabase()
    {
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id
        ]);
        $leave = Leave::factory()->create([
            'timesheet_id' => $timesheet->id
        ]);
        $this->assertInstanceOf(
            Timesheet::class,
            $leave->timesheet()->getResults()
        );
        $this->assertDatabaseCount($leave->getTable(), 1);
        $leave->delete();
        $this->assertDeleted($leave);
    }
}
