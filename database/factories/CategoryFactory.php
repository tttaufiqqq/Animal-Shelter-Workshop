<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'main' => $this->faker->randomElement(['Food', 'Medicine', 'Supplies']),
            'sub' => $this->faker->word(),
        ];
    }
}
