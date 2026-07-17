<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Report;
use App\Models\Rescue;

class RescueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(Rescue::getStatuses()),
            'priority' => $this->faker->randomElement(Rescue::getPriorities()),
            // Report lives on the same 'reporting' connection, safe to nest.
            'reportID' => Report::factory(),
            // caretakerID references 'users' (cross-connection logical FK,
            // no DB constraint) — left null; pass a real id via
            // ->state(['caretakerID' => $caretaker->id]) when needed.
            'caretakerID' => null,
        ];
    }
}
