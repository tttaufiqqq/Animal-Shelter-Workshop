<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Animal;
use App\Models\Report;

class ImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image_path' => $this->faker->imageUrl(640, 480, 'animals', true),
            'animal_id' => Animal::factory(),
            'report_id' => Report::factory(),
        ];
    }
}
