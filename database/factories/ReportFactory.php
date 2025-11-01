<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'report_status' => $this->faker->randomElement(['Pending', 'Resolved', 'In Progress']),
            'description' => $this->faker->paragraph(),
            'user_id' => User::factory(),
        ];
    }
}
