<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Clinic;

class VetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'contactNum' => $this->faker->phoneNumber(),
            'specialization' => $this->faker->word(),
            'license_no' => strtoupper($this->faker->bothify('LIC-####')),
            'clinic_id' => Clinic::factory(),
        ];
    }
}
