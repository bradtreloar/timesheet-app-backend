<?php

namespace Tests\Feature;

use App\Mail\WelcomeMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WelcomeMessageTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Tests that the timesheet submitted notification renders correctly.
     */
    public function testWelcomeMessageRenders()
    {
        $this->seed();
        $user = User::factory()->create();
        $html_output = (new WelcomeMessage($user))->render();
        $this->assertStringContainsString("Welcome", $html_output);
        $this->assertStringContainsString("A user account has been created for you", $html_output);
    }
}
