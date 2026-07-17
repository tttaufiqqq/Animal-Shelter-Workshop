<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnimalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'species' => $this->faker->randomElement(['Dog', 'Cat', 'Rabbit', 'Bird']),
            'name' => $this->faker->firstName(),
            'health_details' => $this->faker->sentence(),
            'age' => $this->faker->randomElement(['Puppy', 'Kitten', 'Adult', 'Senior']),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Unknown']),
            'weight' => $this->faker->randomFloat(2, 1, 40),
            'adoption_status' => $this->faker->randomElement(['Available', 'Adopted', 'Pending']),
            // rescueID (reporting) and slotID (shelter) are logical, cross-connection
            // FKs with no DB-level constraint (docs/foreign-keys.md). Left null by
            // default so this factory never implicitly writes to another connection;
            // pass a real id via ->state(['rescueID' => $rescue->id]) when a test
            // needs the relationship populated.
            'rescueID' => null,
            'slotID' => null,
        ];
    }
}
