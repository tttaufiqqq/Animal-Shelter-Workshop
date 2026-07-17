<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VisitListFactory extends Factory
{
    public function definition(): array
    {
        return [
            // userID references 'users' (cross-connection logical FK, no DB
            // constraint) but the column is NOT NULL and unique (one visit
            // list per user) — a unique raw id satisfies both without
            // crossing connections. Pass a real id via
            // ->state(['userID' => $user->id]) when needed.
            'userID' => $this->faker->unique()->numberBetween(1, 1000000),
        ];
    }
}
