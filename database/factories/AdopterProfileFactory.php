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
            // Values must match validate_adopter_profile() (2025_12_31_000003) —
            // it rejects anything outside these exact sets.
            'housing_type' => $this->faker->randomElement(['condo', 'landed', 'apartment', 'hdb']),
            'has_children' => $this->faker->boolean(),
            'has_other_pets' => $this->faker->boolean(),
            'activity_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'experience' => $this->faker->randomElement(['beginner', 'intermediate', 'expert']),
            'preferred_species' => $this->faker->randomElement(['cat', 'dog', 'both']),
            'preferred_size' => $this->faker->randomElement(['small', 'medium', 'large', 'any']),
        ];
    }
}
