<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_date' => $this->faker->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'appointment_time' => $this->faker->time('H:i:s'),
            'status' => 'Pending',
            'remarks' => $this->faker->optional()->sentence(),
            // userID references 'users' (cross-connection logical FK, no DB
            // constraint) — nullable, so left null by default; pass a real
            // id via ->state(['userID' => $user->id]) when needed.
            'userID' => null,
        ];
    }
}
