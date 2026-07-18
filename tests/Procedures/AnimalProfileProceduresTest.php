<?php

use App\Models\Animal;
use App\Models\AnimalProfile;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('animals');
});

afterEach(function () {
    $this->truncate('animals', ['animal_profile', 'animal']);
});

function callAnimalProfileUpsert(int $animalId, array $overrides = []): object
{
    $p = array_merge([
        'age' => 'adult', 'size' => 'medium', 'energy_level' => 'medium',
        'good_with_kids' => true, 'good_with_pets' => true,
        'temperament' => 'calm', 'medical_needs' => 'none',
        'user_id' => null, 'user_name' => null, 'user_email' => null,
    ], $overrides);

    DB::connection('animals')->statement(
        'CALL sp_animal_profile_upsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_profile_id, @o_status, @o_message)',
        [$animalId, ...array_values($p)]
    );

    return DB::connection('animals')->selectOne('SELECT @o_profile_id AS profile_id, @o_status AS `status`, @o_message AS `message`');
}

// --- sp_animal_profile_upsert (insert and update path) ---

it('inserts a new animal profile', function () {
    $animal = Animal::factory()->create();

    $result = callAnimalProfileUpsert($animal->id, ['size' => 'large']);

    expect($result->status)->toBe('success');
    expect($result->message)->toBe('Animal profile created successfully');
    $this->assertDatabaseHas('animal_profile', ['animalID' => $animal->id, 'size' => 'large'], 'animals');
});

it('updates the existing profile on a second call instead of inserting a duplicate', function () {
    $animal = Animal::factory()->create();
    callAnimalProfileUpsert($animal->id, ['size' => 'small']);

    $result = callAnimalProfileUpsert($animal->id, ['size' => 'large']);

    expect($result->status)->toBe('success');
    expect($result->message)->toBe('Animal profile updated successfully');
    expect(AnimalProfile::where('animalID', $animal->id)->count())->toBe(1);
    $this->assertDatabaseHas('animal_profile', ['animalID' => $animal->id, 'size' => 'large'], 'animals');
});

it('returns an error for a profile upsert on an animal that does not exist', function () {
    $result = callAnimalProfileUpsert(999999);

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Animal not found');
});
