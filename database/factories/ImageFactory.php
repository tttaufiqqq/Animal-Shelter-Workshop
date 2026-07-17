<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Report;

class ImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image_path' => $this->faker->imageUrl(640, 480, 'animals', true),
            // Report lives on the same 'reporting' connection as Image, so
            // nesting its factory is safe. animalID (animals) and clinicID
            // (animals) are cross-connection logical FKs — left null; pass a
            // real id via ->state(['animalID' => $animal->id]) when needed.
            'reportID' => Report::factory(),
            'animalID' => null,
            'clinicID' => null,
        ];
    }
}
