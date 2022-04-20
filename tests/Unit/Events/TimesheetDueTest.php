<?php

namespace Tests\Unit\Events;

use App\Events\TimesheetDue;
use App\Models\User;
use Tests\TestCase;

class TimesheetDueTest extends TestCase
{
    /**
     * Has user.
     */
    public function testHasUser()
    {
        $user = User::factory()->make();
        $event = new TimesheetDue($user);
        $this->assertEquals($user, $event->user);
    }
}
