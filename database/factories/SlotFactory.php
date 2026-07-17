<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Section;

class SlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->bothify('Slot-##'),
            'capacity' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['available', 'occupied']),
            // Section lives on the same 'shelter' connection, safe to nest.
            'sectionID' => Section::factory(),
        ];
    }
}
