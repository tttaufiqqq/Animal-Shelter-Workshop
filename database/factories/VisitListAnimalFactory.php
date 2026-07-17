<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\VisitList;

class VisitListAnimalFactory extends Factory
{
    public function definition(): array
    {
        return [
            // VisitList lives on the same 'booking' connection and has a real
            // FK constraint (listID -> visit_list.id CASCADE), safe to nest.
            'listID' => VisitList::factory(),
            // animalID references 'animals' (cross-connection logical FK, no
            // DB constraint) and is NOT NULL, so it needs a value — a raw id
            // is valid since nothing enforces it exists. Pass a real id via
            // ->state(['animalID' => $animal->id]) when the test needs the
            // relationship to resolve.
            'animalID' => $this->faker->numberBetween(1, 100000),
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }
}
