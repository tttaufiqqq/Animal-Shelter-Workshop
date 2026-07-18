<?php

use App\Models\AdopterProfile;
use Illuminate\Support\Facades\Cache;

it('invalidates the cached matches when the adopter profile is updated', function () {
    $user = $this->makeAdopter();
    AdopterProfile::factory()->create(['adopterID' => $user->id]);
    Cache::put("animal_matches_user_{$user->id}", [['id' => 999, 'name' => 'stale']], 300);

    $this->actingAs($user)->post('/adopter/profile/store', [
        'housing_type' => 'condo',
        'has_children' => false,
        'has_other_pets' => false,
        'activity_level' => 'medium',
        'experience' => 'beginner',
        'preferred_species' => 'dog',
        'preferred_size' => 'any',
    ]);

    expect(Cache::has("animal_matches_user_{$user->id}"))->toBeFalse();
});

it('matches animals regardless of adoption_status casing', function () {
    $user = $this->makeAdopter();
    AdopterProfile::factory()->create(['adopterID' => $user->id]);

    $upper = $this->makeAnimalWithProfile(['adoption_status' => 'Not Adopted']);
    $lower = $this->makeAnimalWithProfile(['adoption_status' => 'not adopted']);
    $adopted = $this->makeAnimalWithProfile(['adoption_status' => 'Adopted']);

    $response = $this->actingAs($user)->getJson('/animal-matches');
    $response->assertOk();

    $ids = collect($response->json('matches'))->pluck('id');

    expect($ids)->toContain($upper->id)
        ->toContain($lower->id)
        ->not->toContain($adopted->id);
});

// Documents a known limitation (plan Appendix, Phase 4.1): getMatches() runs
// ->limit(20) before scoring, then takes the top 5 by score. A genuinely
// better match that happens to sit beyond the first 20 candidates (in
// primary-key order, since the query has no ORDER BY) is never even scored —
// results are "best of the first 20", not a true global best. Not fixed here:
// removing the cap changes the query's cost profile against a live remote
// connection, which is a perf tradeoff, not a one-line correctness fix.
it('only scores the first 20 candidate animals, so a better match beyond that window is never surfaced', function () {
    $user = $this->makeAdopter();
    AdopterProfile::factory()->create([
        'adopterID' => $user->id,
        'preferred_species' => 'dog',
        'preferred_size' => 'medium',
        'activity_level' => 'medium',
        'has_children' => false,
        'has_other_pets' => false,
        'housing_type' => 'condo',
    ]);

    for ($i = 0; $i < 20; $i++) {
        $this->makeAnimalWithProfile(
            ['species' => 'Cat', 'adoption_status' => 'Not Adopted'],
            ['size' => 'large', 'energy_level' => 'high', 'good_with_kids' => false, 'good_with_pets' => false]
        );
    }

    $perfectMatch = $this->makeAnimalWithProfile(
        ['species' => 'Dog', 'adoption_status' => 'Not Adopted'],
        ['size' => 'medium', 'energy_level' => 'medium', 'good_with_kids' => true, 'good_with_pets' => true]
    );

    $response = $this->actingAs($user)->getJson('/animal-matches');
    $response->assertOk();

    $ids = collect($response->json('matches'))->pluck('id');

    expect($ids)->not->toContain($perfectMatch->id);
});
