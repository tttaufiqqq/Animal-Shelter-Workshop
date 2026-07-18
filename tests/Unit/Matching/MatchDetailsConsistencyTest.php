<?php

// Regression test for Appendix A #13: getMatchDetails() used to claim "Perfect
// size match" whenever preferred_size was 'any', but calculateMatchScore() only
// awarded the 15 size points on an exact match — 'any' scored zero. Fixed by
// making calculateMatchScore() treat 'any' the same as an exact match, so the
// score and the UI-facing detail no longer contradict each other.

it('awards the size points whenever it claims a perfect size match, for an exact match', function () {
    $adopter = adopterProfile(['preferred_size' => 'medium']);
    $profile = animalProfile(['size' => 'medium']);
    $animal = animalStub();

    expect(matchScorer()->getMatchDetails($adopter, $profile, $animal))->toContain('Perfect size match');

    $matching = matchScorer()->calculateMatchScore($adopter, $profile, $animal);
    $sizeMismatched = matchScorer()->calculateMatchScore(adopterProfile(['preferred_size' => 'small']), $profile, $animal);

    expect($matching - $sizeMismatched)->toBe(15.0);
});

it('awards the size points whenever it claims a perfect size match, for "any" preference', function () {
    $adopter = adopterProfile(['preferred_size' => 'any']);
    $profile = animalProfile(['size' => 'large']);
    $animal = animalStub();

    expect(matchScorer()->getMatchDetails($adopter, $profile, $animal))->toContain('Perfect size match');

    $withAny = matchScorer()->calculateMatchScore($adopter, $profile, $animal);
    $sizeMismatched = matchScorer()->calculateMatchScore(adopterProfile(['preferred_size' => 'small']), $profile, $animal);

    expect($withAny - $sizeMismatched)->toBe(15.0);
});

it('does not claim a perfect size match when sizes differ and there is no "any" preference', function () {
    $adopter = adopterProfile(['preferred_size' => 'small']);
    $profile = animalProfile(['size' => 'large']);

    expect(matchScorer()->getMatchDetails($adopter, $profile, animalStub()))->not->toContain('Perfect size match');
});

it('lists "Great with children" only when the adopter has children and the animal is good with them', function () {
    $animal = animalStub();

    $good = matchScorer()->getMatchDetails(adopterProfile(['has_children' => true]), animalProfile(['good_with_kids' => true]), $animal);
    $noKids = matchScorer()->getMatchDetails(adopterProfile(['has_children' => false]), animalProfile(['good_with_kids' => true]), $animal);
    $notGoodWithKids = matchScorer()->getMatchDetails(adopterProfile(['has_children' => true]), animalProfile(['good_with_kids' => false]), $animal);

    expect($good)->toContain('Great with children')
        ->and($noKids)->not->toContain('Great with children')
        ->and($notGoodWithKids)->not->toContain('Great with children');
});

it('lists "Energy level matches your lifestyle" only when the energy match is at least 0.6', function () {
    $animal = animalStub();
    $profile = animalProfile(['energy_level' => 'high']);

    $close = matchScorer()->getMatchDetails(adopterProfile(['activity_level' => 'medium']), $profile, $animal);
    $far = matchScorer()->getMatchDetails(adopterProfile(['activity_level' => 'low']), $profile, $animal);

    expect($close)->toContain('Energy level matches your lifestyle')
        ->and($far)->not->toContain('Energy level matches your lifestyle');
});
