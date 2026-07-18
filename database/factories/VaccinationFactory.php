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
            // A DB trigger rejects a next_due_date in the past — must stay future-dated.
            'next_due_date' => $this->faker->optional()->dateTimeBetween('+1 day', '+1 year'),
            'remarks' => $this->faker->sentence(),
            'weight' => $this->faker->randomFloat(2, 1, 40),
            'costs' => $this->faker->randomFloat(2, 10, 200),
            // Animal and Vet both live on the same 'animals' connection, safe to nest.
            'animalID' => Animal::factory(),
            'vetID' => Vet::factory(),
        ];
    }
}
