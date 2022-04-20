<?php

namespace Tests\Unit\Events;

use App\Events\UserCreated;
use App\Models\User;
use Tests\TestCase;

class UserCreatedTest extends TestCase
{
    /**
     * Has user.
     */
    public function testHasUser()
    {
        $user = User::factory()->make();
        $event = new UserCreated($user);
        $this->assertEquals($user, $event->user);
    }
}
