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
        $values = [];
        for ($i = 0; $i < 7; $i++) {
            $values[] = [
                "isActive" => true,
                "startTime" => [
                    "hour" => (string) random_int(0, 8),
                    "minute" => (string) random_int(0, 59),
                ],
                "endTime" => [
                    "hour" => (string) random_int(16, 23),
                    "minute" => (string) random_int(0, 59),
                ],
                "breakDuration" => [
                    "hour" => (string) random_int(0, 1),
                    "minute" => (string) random_int(0, 59),
                ],
            ];
        }

        return [
            'values' => json_encode($values),
        ];
    }
}
