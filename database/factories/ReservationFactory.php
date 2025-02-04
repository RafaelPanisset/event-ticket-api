<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    
    protected $model = Reservation::class;

    public function definition()
    {
        return [
            'event_id'       => Event::factory(),
            'customer_email' => $this->faker->unique()->safeEmail,
            'customer_name'  => $this->faker->name,
            'tickets_count'  => $this->faker->numberBetween(1, 5),
        ];
    }
}
