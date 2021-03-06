<?php

namespace Database\Factories;

use App\Models\Preset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PresetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Preset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'values' => $this->faker->preset(),
        ];
    }
}
