<?php

namespace Tests\Integration\Console\Commands;

use App\Console\Commands\UserCreate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Console\Commands\UserCreate
 */
class RemindUsersTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Creates new user.
     */
    public function testCreatesNewUser()
    {
        Event::fake();
        $name = $this->faker->name();
        $email = $this->faker->email();

        $this->artisan("user:create {$email} \"{$name}\"")
            ->assertExitCode(0);

        $user = User::first();
        $this->assertEquals($user->name, $name);
        $this->assertEquals($user->email, $email);
        $this->assertFalse($user->is_admin);
    }

    /**
     * Creates new admin user.
     */
    public function testCreatesNewAdminUser()
    {
        Event::fake();
        $name = $this->faker->name();
        $email = $this->faker->email();

        $this->artisan("user:create {$email} \"{$name}\"  --admin")
            ->assertExitCode(0);

        $user = User::first();
        $this->assertEquals($user->name, $name);
        $this->assertEquals($user->email, $email);
        $this->assertTrue($user->is_admin);
    }

    /**
     * Fails when name not wrapped in quotes.
     */
    public function testFailsWhenNameNotQuoted()
    {
        Event::fake();
        $name = $this->faker->name();
        $email = $this->faker->email();

        $this->expectException(
            \Symfony\Component\Console\Exception\RuntimeException::class
        );
        $this->artisan("user:create {$email} {$name}");
    }

    /**
     * Fails when email not unique.
     */
    public function testFailsWhenEmailNotUnique()
    {
        Event::fake();
        $name = $this->faker->name();
        $email = $this->faker->email();
        User::factory()->create([
            'email' => $email,
        ]);

        $this->expectException(
            \Symfony\Component\Console\Exception\RuntimeException::class
        );
        $this->artisan("user:create {$email} {$name}");
    }
}
