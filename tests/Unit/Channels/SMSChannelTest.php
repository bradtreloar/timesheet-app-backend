<?php

namespace Tests\Unit\Channels;

use App\Channels\SMSChannel;
use App\Models\User;
use App\Notifications\Reminder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Channels\SMSChannel
 */
class SMSChannelTest extends TestCase
{
    use WithFaker;

    /**
     * Sends SMS.
     *
     * @covers ::send
     */
    public function testSendsSMS()
    {
        $username = $this->faker->userName;
        $password = $this->faker->password;
        $from = $this->faker->email;
        $message = $this->faker->words(5, true);
        $user = User::factory()->make();
        $request_url = 'https://api.smsbroadcast.com.au/api-adv.php';
        $request_data = [
            'query' => [
                'username' => $username,
                'password' => $password,
                'to' => $user->phone_number,
                'from' => $from,
                'message' => $message,
            ],
        ];
        Config::shouldReceive('get')->once()->with('sms.username')->andReturn($username);
        Config::shouldReceive('get')->once()->with('sms.password')->andReturn($password);
        Config::shouldReceive('get')->once()->with('sms.from')->andReturn($from);
        Http::fake();
        /**
         * @var \App\Contracts\SMSNotification|\Mockery\MockInterface
         *      $mock_reminder
         * */
        $mock_reminder = $this->spy(\App\Notifications\Reminder::class);
        $mock_reminder->allows()->toSMS($user)->andReturn($message);

        $channel = new SMSChannel();
        $channel->send($user, $mock_reminder);

        $mock_reminder->shouldHaveReceived('toSMS')->with($user);
        Http::assertSent(
            function (Request $request) use ($request_url, $request_data) {
                return $request->url() == $request_url &&
                    $request->method() == 'POST' &&
                    $request->data() == $request_data;
            }
        );
    }
}
