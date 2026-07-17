<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Report;

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
            'report_status' => $this->faker->randomElement(Report::getStatuses()),
            'description' => $this->faker->paragraph(),
            // userID references 'users' (cross-connection logical FK, no DB
            // constraint) — left null; pass a real id via
            // ->state(['userID' => $user->id]) when needed.
            'userID' => null,
        ];
    }
}
