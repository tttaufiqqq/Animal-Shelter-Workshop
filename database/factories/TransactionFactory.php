<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => $this->faker->randomElement(['Success', 'Failed']),
            'remarks' => $this->faker->sentence(),
            'type' => 'FPX Online Banking',
            'bill_code' => strtoupper($this->faker->bothify('BILL-########')),
            'reference_no' => 'BOOKING-'.$this->faker->numberBetween(1, 100000).'-'.$this->faker->unixTime(),
            // userID references 'users' (cross-connection logical FK, no DB
            // constraint) — nullable, so left null by default; pass a real
            // id via ->state(['userID' => $user->id]) when needed.
            'userID' => null,
        ];
    }
}
