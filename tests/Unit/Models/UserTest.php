<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Tests that the user's name can be fatched in snakecase.
     *
     * @return void
     */
    public function testSnakeCaseName()
    {
        // Replace space with underscore and make text lowercase.
        $user = User::factory()->make([
            'name' => "Zaphod Beeblebrox",
        ]);
        $this->assertEquals("zaphod_beeblebrox", $user->snakecase_name);

        // Remove typewriter's apostrophe.
        $user = User::factory()->make([
            'name' => "Zaphod O'Beeblebrox",
        ]);
        $this->assertEquals("zaphod_obeeblebrox", $user->snakecase_name);

        // Remove typesetter's apostrophe.
        $user = User::factory()->make([
            'name' => "Zaphod O’Beeblebrox",
        ]);
        $this->assertEquals("zaphod_obeeblebrox", $user->snakecase_name);

        // Replace hyphen with underscore.
        $user = User::factory()->make([
            'name' => "Zaphod Beeblebrox-Smith",
        ]);
        $this->assertEquals("zaphod_beeblebrox_smith", $user->snakecase_name);

        // Remove comma.
        $user = User::factory()->make([
            'name' => "Zaphod Beeblebrox, Galactic President",
        ]);
        $this->assertEquals("zaphod_beeblebrox_galactic_president", $user->snakecase_name);
    }
}
