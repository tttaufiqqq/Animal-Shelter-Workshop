<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Animal;
use App\Models\Vet;

class VaccinationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->word(),
            'date' => $this->faker->date(),
            'next_due_date' => $this->faker->optional()->date(),
            'remarks' => $this->faker->sentence(),
            'animal_id' => Animal::factory(),
            'vet_id' => Vet::factory(),
        ];
    }
}
