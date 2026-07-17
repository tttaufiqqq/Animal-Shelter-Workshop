<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AdopterProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            // User lives on the same 'users' connection and has a real FK
            // constraint (adopterID -> users.id CASCADE), safe to nest.
            'adopterID' => User::factory(),
            'housing_type' => $this->faker->randomElement(['apartment', 'house_with_yard', 'condo']),
            'has_children' => $this->faker->boolean(),
            'has_other_pets' => $this->faker->boolean(),
            'activity_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'experience' => $this->faker->randomElement(['beginner', 'intermediate', 'expert']),
            'preferred_species' => $this->faker->randomElement(['Dog', 'Cat', 'both']),
            'preferred_size' => $this->faker->randomElement(['small', 'medium', 'large', 'any']),
        ];
    }
}
