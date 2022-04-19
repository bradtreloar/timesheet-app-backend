<?php

namespace Tests\Unit\Models;

use App\Models\Preset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Models\User
 */
class UserTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Has timesheets relationship
     * 
     * @covers ::timesheets
     */
    public function testHasTimesheetsRelationship()
    {
        $this->seed();
        $this->assertCount(1, User::first()->timesheets);
    }
    
    /**
     * Has presets relationship
     * 
     * @covers ::presets
     */
    public function testHasPresetsRelationship()
    {
        $this->seed();
        $this->assertCount(1, User::first()->presets);
    }
    
    /**
     * Gets default preset
     * 
     * @covers ::defaultPreset
     */
    public function testGetsDefaultPreset()
    {
        $this->seed();
        $this->assertEquals(Preset::first(), User::first()->defaultPreset);
    }

    /**
     * Converts user's name to snake case.
     *
     * @covers ::snakecase_name
     */
    public function testConvertNameToSnakeCase()
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
