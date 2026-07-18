<?php

use App\Http\Controllers\Concerns\BookingAdoption\CalculatesAdoptionFee;

function feeCalculator()
{
    return new class {
        use CalculatesAdoptionFee;
    };
}

it('charges the correct base fee per species', function (string $species, int $expectedBase) {
    $animal = (object) ['species' => $species];
    $result = feeCalculator()->calculateAdoptionFee($animal, collect(), collect());

    expect($result['base_fee'])->toBe($expectedBase)
        ->and($result['total_fee'])->toBe($expectedBase);
})->with([
    'dog' => ['dog', 20],
    'cat' => ['cat', 10],
    'rabbit (unknown species defaults to RM100)' => ['rabbit', 100],
    'mixed-case species is matched case-insensitively' => ['Dog', 20],
]);

it('adds RM10 per medical record', function () {
    $animal = (object) ['species' => 'cat'];
    $result = feeCalculator()->calculateAdoptionFee($animal, collect([1, 2, 3]), collect());

    expect($result['medical_count'])->toBe(3)
        ->and($result['medical_fee'])->toBe(30)
        ->and($result['total_fee'])->toBe(10 + 30);
});

it('adds RM20 per vaccination record', function () {
    $animal = (object) ['species' => 'dog'];
    $result = feeCalculator()->calculateAdoptionFee($animal, collect(), collect([1, 2]));

    expect($result['vaccination_count'])->toBe(2)
        ->and($result['vaccination_fee'])->toBe(40)
        ->and($result['total_fee'])->toBe(20 + 40);
});

it('combines base, medical, and vaccination fees for an unknown species', function () {
    $animal = (object) ['species' => 'rabbit'];
    $result = feeCalculator()->calculateAdoptionFee($animal, collect([1]), collect([1, 2]));

    expect($result['total_fee'])->toBe(100 + 10 + 40);
});

it('charges zero extra fee when there are no medical or vaccination records', function () {
    $animal = (object) ['species' => 'dog'];
    $result = feeCalculator()->calculateAdoptionFee($animal, collect(), collect());

    expect($result)->toMatchArray([
        'medical_fee' => 0,
        'vaccination_fee' => 0,
        'total_fee' => 20,
    ]);
});
