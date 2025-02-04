<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'date' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'availability' => $this->faker->numberBetween(5, 100),
        ];
    }
}
