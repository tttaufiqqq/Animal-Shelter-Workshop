<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'contactNum' => $this->faker->phoneNumber(),
        ];
    }
}
