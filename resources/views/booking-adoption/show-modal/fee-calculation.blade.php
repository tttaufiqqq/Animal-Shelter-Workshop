{{-- bookings/partials/booking-modal.blade.php --}}
{{-- Booking Details Modal with animal selection + Adoption Fee Modal with selected animals only --}}

@php
    $animals = $booking->animals ?? collect();
    $allFeeBreakdowns = [];
    $totalFee = 0;

    // Species-based fee structure (same as controller)
    $speciesBaseFees = [
        'dog' => 20,
        'cat' => 10,
    ];
    $medicalRate = 10;
    $vaccinationRate = 20;

    if ($animals->isNotEmpty()) {
        foreach ($animals as $animal) {
            $species = strtolower($animal->species);
            $baseFee = $speciesBaseFees[$species] ?? 100; // default RM 100

            $medicalCount = $animal->medicals ? $animal->medicals->count() : 0;
            $medicalFee = $medicalCount * $medicalRate;

            $vaccinationCount = $animal->vaccinations ? $animal->vaccinations->count() : 0;
            $vaccinationFee = $vaccinationCount * $vaccinationRate;

            $animalTotal = $baseFee + $medicalFee + $vaccinationFee;

            $allFeeBreakdowns[$animal->id] = [
                'base_fee' => $baseFee,
                'medical_rate' => $medicalRate,
                'medical_count' => $medicalCount,
                'medical_fee' => $medicalFee,
                'vaccination_rate' => $vaccinationRate,
                'vaccination_count' => $vaccinationCount,
                'vaccination_fee' => $vaccinationFee,
                'total_fee' => $animalTotal,
            ];

            $totalFee += $animalTotal;
        }
    }
@endphp
