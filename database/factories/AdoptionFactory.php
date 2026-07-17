<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Booking;

class AdoptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fee' => $this->faker->randomFloat(2, 10, 200),
            'remarks' => $this->faker->sentence(),
            // Booking lives on the same 'booking' connection and has a real
            // FK constraint (bookingID -> booking.id CASCADE), safe to nest.
            'bookingID' => Booking::factory(),
            // transactionID also has a real FK constraint (SET NULL) but the
            // column is nullable and a paid adoption should reference a real
            // transaction only when the test needs one.
            'transactionID' => null,
            // animalID references 'animals' (cross-connection logical FK, no
            // DB constraint) and is NOT NULL, so it needs a value — a raw id
            // is valid since nothing enforces it exists. Pass a real id via
            // ->state(['animalID' => $animal->id]) when the test needs the
            // relationship to resolve.
            'animalID' => $this->faker->numberBetween(1, 100000),
        ];
    }
}
