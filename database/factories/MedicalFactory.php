<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Animal;
use App\Models\Vet;

class MedicalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'treatment_type' => $this->faker->randomElement(['Checkup', 'Surgery', 'Deworming']),
            'diagnosis' => $this->faker->sentence(),
            'action' => $this->faker->sentence(),
            'remarks' => $this->faker->optional()->sentence(),
            'costs' => $this->faker->randomFloat(2, 10, 500),
            // Animal and Vet both live on the same 'animals' connection and
            // have real FK constraints, safe to nest.
            'vetID' => Vet::factory(),
            'animalID' => Animal::factory(),
        ];
    }
}
