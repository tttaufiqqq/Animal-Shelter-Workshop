<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Animal;

class AnimalProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Animal lives on the same 'animals' connection and has a real
            // FK constraint (animalID -> animal.id CASCADE), safe to nest.
            'animalID' => Animal::factory(),
            // Values match the Rule::in() sets validated in
            // ManagesMatching::storeOrUpdate() (app/Http/Controllers/Concerns/
            // AnimalManagement/ManagesMatching.php).
            'age' => $this->faker->randomElement(['kitten', 'puppy', 'adult', 'senior']),
            'size' => $this->faker->randomElement(['small', 'medium', 'large']),
            'energy_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'good_with_kids' => $this->faker->boolean(),
            'good_with_pets' => $this->faker->boolean(),
            'temperament' => $this->faker->randomElement(['calm', 'active', 'shy', 'friendly', 'independent']),
            'medical_needs' => $this->faker->randomElement(['none', 'minor', 'moderate', 'special']),
        ];
    }
}
