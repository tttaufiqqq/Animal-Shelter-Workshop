<?php

it('scores a perfect match as 100', function () {
    $adopter = adopterProfile(['preferred_species' => 'dog', 'preferred_size' => 'medium', 'housing_type' => 'house_with_yard']);
    $profile = animalProfile(['size' => 'medium', 'energy_level' => 'medium', 'good_with_kids' => true, 'good_with_pets' => true]);
    $animal = animalStub(['species' => 'dog']);

    expect(matchScorer()->calculateMatchScore($adopter, $profile, $animal))->toBe(100.0);
});

it('loses 20 points for a species mismatch', function () {
    $profile = animalProfile();
    $animal = animalStub(['species' => 'dog']);

    $matching = matchScorer()->calculateMatchScore(adopterProfile(['preferred_species' => 'dog']), $profile, $animal);
    $mismatched = matchScorer()->calculateMatchScore(adopterProfile(['preferred_species' => 'cat']), $profile, $animal);

    expect($matching - $mismatched)->toBe(20.0);
});

it('treats "both" as matching any species', function (string $species) {
    $adopter = adopterProfile(['preferred_species' => 'both']);
    $profile = animalProfile();
    $animal = animalStub(['species' => $species]);

    $withBoth = matchScorer()->calculateMatchScore($adopter, $profile, $animal);
    $withExactMatch = matchScorer()->calculateMatchScore(adopterProfile(['preferred_species' => $species]), $profile, $animal);

    expect($withBoth)->toBe($withExactMatch);
})->with(['dog', 'cat', 'rabbit']);

it('never scores below 0 or above 100', function () {
    $worstCase = matchScorer()->calculateMatchScore(
        adopterProfile([
            'preferred_species' => 'cat',
            'preferred_size' => 'small',
            'activity_level' => 'low',
            'has_children' => true,
            'has_other_pets' => true,
            'housing_type' => 'apartment',
        ]),
        animalProfile(['size' => 'large', 'energy_level' => 'high', 'good_with_kids' => false, 'good_with_pets' => false]),
        animalStub(['species' => 'dog'])
    );

    expect($worstCase)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);

    $bestCase = matchScorer()->calculateMatchScore(
        adopterProfile(['preferred_species' => 'both', 'preferred_size' => 'medium', 'housing_type' => 'house_with_yard']),
        animalProfile(['size' => 'medium', 'good_with_kids' => true, 'good_with_pets' => true]),
        animalStub()
    );

    expect($bestCase)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
});

it('awards full points for children/pets compatibility when the adopter has none to worry about', function () {
    $withoutChildren = matchScorer()->calculateMatchScore(
        adopterProfile(['has_children' => false]),
        animalProfile(['good_with_kids' => false]),
        animalStub()
    );
    $withChildrenAndGoodMatch = matchScorer()->calculateMatchScore(
        adopterProfile(['has_children' => true]),
        animalProfile(['good_with_kids' => true]),
        animalStub()
    );

    expect($withoutChildren)->toBe($withChildrenAndGoodMatch);
});
