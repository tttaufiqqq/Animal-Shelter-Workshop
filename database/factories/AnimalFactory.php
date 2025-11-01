<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rescue;
use App\Models\Slot;

class AnimalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'species' => $this->faker->randomElement(['Dog', 'Cat', 'Rabbit', 'Bird']),
            'health_details' => $this->faker->sentence(),
            'age' => $this->faker->numberBetween(1, 15),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Unknown']),
            'adoption_status' => $this->faker->randomElement(['Available', 'Adopted', 'Pending']),
            'arrival_date' => $this->faker->date(),
            'medical_status' => $this->faker->sentence(),
            'rescue_id' => Rescue::factory(),
            'slot_id' => Slot::factory(),
        ];
    }
}
