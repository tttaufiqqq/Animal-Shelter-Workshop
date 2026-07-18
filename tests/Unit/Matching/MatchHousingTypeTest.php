<?php

it('scores apartment + a small or medium, non-high-energy animal as an ideal fit', function (string $size) {
    $profile = animalProfile(['size' => $size, 'energy_level' => 'low']);

    expect(matchScorer()->matchHousingType('apartment', $profile))->toBe(15);
})->with(['small', 'medium']);

it('scores apartment + a large or high-energy animal as a poor fit', function (array $overrides) {
    $profile = animalProfile($overrides);

    expect(matchScorer()->matchHousingType('apartment', $profile))->toBe(5);
})->with([
    'large animal' => [['size' => 'large', 'energy_level' => 'low']],
    'high-energy animal' => [['size' => 'small', 'energy_level' => 'high']],
]);

it('scores house_with_yard as the maximum regardless of the animal profile', function () {
    $spacious = matchScorer()->matchHousingType('house_with_yard', animalProfile(['size' => 'small', 'energy_level' => 'low']));
    $demanding = matchScorer()->matchHousingType('house_with_yard', animalProfile(['size' => 'large', 'energy_level' => 'high']));

    expect($spacious)->toBe(15)->and($demanding)->toBe(15);
});

it('scores any other housing type as neutral', function (string $housingType) {
    expect(matchScorer()->matchHousingType($housingType, animalProfile()))->toBe(10);
})->with(['condo', 'landed', 'hdb']);
