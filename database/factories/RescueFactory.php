<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Report;
use App\Models\User;

class RescueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['Ongoing', 'Success', 'Failed']),
            'report_id' => Report::factory(),
            'caretaker_id' => User::factory(),
        ];
    }
}
