<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

trait CalculatesAdoptionFee
{
    private const SPECIES_BASE_FEES = [
        'dog' => 20,
        'cat' => 10,
    ];

    private const DEFAULT_BASE_FEE = 100;
    private const MEDICAL_RATE = 10;
    private const VACCINATION_RATE = 20;

    public function calculateAdoptionFee($animal, $medicals, $vaccinations): array
    {
        $species = strtolower($animal->species ?? '');
        $baseFee = self::SPECIES_BASE_FEES[$species] ?? self::DEFAULT_BASE_FEE;

        $medicalCount = $medicals->count();
        $medicalFee = $medicalCount * self::MEDICAL_RATE;

        $vaccinationCount = $vaccinations->count();
        $vaccinationFee = $vaccinationCount * self::VACCINATION_RATE;

        return [
            'base_fee' => $baseFee,
            'medical_rate' => self::MEDICAL_RATE,
            'medical_count' => $medicalCount,
            'medical_fee' => $medicalFee,
            'vaccination_rate' => self::VACCINATION_RATE,
            'vaccination_count' => $vaccinationCount,
            'vaccination_fee' => $vaccinationFee,
            'total_fee' => $baseFee + $medicalFee + $vaccinationFee,
        ];
    }
}
