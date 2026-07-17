<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Slot;
use App\Models\Category;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_name' => $this->faker->words(2, true),
            'quantity' => $this->faker->numberBetween(0, 100),
            'brand' => $this->faker->company(),
            'weight' => $this->faker->randomFloat(2, 0.1, 50),
            'status' => $this->faker->randomElement(['in_stock', 'low_stock', 'out_of_stock']),
            // Slot and Category both live on the same 'shelter' connection, safe to nest.
            'slotID' => Slot::factory(),
            'categoryID' => Category::factory(),
        ];
    }
}
