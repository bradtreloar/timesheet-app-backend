<?php

namespace Tests\Integration\Listeners;

use App\Events\UserCreated;
use App\Mail\WelcomeMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendWelcomeMessageTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testSendsWelcomeMessageToUser()
    {
        Mail::fake();
        $user = User::factory()->make();
        UserCreated::dispatch($user);
        Mail::assertSent(function (WelcomeMessage $mail) use ($user) {
            return $mail->user->id === $user->id &&
                $mail->hasTo($user);
        });
    }
}
