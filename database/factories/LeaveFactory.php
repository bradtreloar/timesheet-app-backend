<?php

namespace Database\Factories;

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Leave::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date' => Carbon::yesterday(),
            'reason' => 'absent:sick-day',
            'hours' => (float) random_int(1, 8),
        ];
    }
}
