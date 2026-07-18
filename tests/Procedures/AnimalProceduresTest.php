<?php

use App\Models\Animal;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('animals');
});

afterEach(function () {
    $this->truncate('animals', ['animal']);
});

function callAnimalCreate(array $overrides = []): object
{
    $p = array_merge([
        'name' => 'Rex', 'species' => 'Dog', 'health_details' => null, 'age' => 'Adult',
        'gender' => 'Male', 'weight' => 10.5, 'adoption_status' => null,
        'rescue_id' => null, 'slot_id' => null, 'user_id' => null, 'user_name' => null, 'user_email' => null,
    ], $overrides);

    DB::connection('animals')->statement(
        'CALL sp_animal_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_animal_id, @o_status, @o_message)',
        array_values($p)
    );

    return DB::connection('animals')->selectOne('SELECT @o_animal_id AS animal_id, @o_status AS `status`, @o_message AS `message`');
}

// --- sp_animal_create ---

it('creates an animal on the happy path', function () {
    $result = callAnimalCreate();

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('animal', ['id' => $result->animal_id, 'name' => 'Rex'], 'animals');
});

it('defaults adoption_status to Not Adopted when none is given', function () {
    $result = callAnimalCreate(['adoption_status' => null]);

    $this->assertDatabaseHas('animal', ['id' => $result->animal_id, 'adoption_status' => 'Not Adopted'], 'animals');
});

it('surfaces the validation trigger message instead of a generic DB error', function () {
    $result = callAnimalCreate(['name' => null]);

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Animal name is required');
});

// --- sp_animal_read / sp_animal_update / sp_animal_delete ---

it('reads an animal by id', function () {
    $animal = Animal::factory()->create(['name' => 'Fido']);

    $rows = DB::connection('animals')->select('CALL sp_animal_read(?)', [$animal->id]);

    expect($rows)->toHaveCount(1);
    expect($rows[0]->name)->toBe('Fido');
});

it('updates an animal', function () {
    $animal = Animal::factory()->create(['name' => 'Old Name']);

    DB::connection('animals')->statement(
        'CALL sp_animal_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)',
        [$animal->id, 'New Name', 'Dog', null, 'Adult', 'Male', 12.0, null, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('animal', ['id' => $animal->id, 'name' => 'New Name'], 'animals');
});

it('returns an error status when updating an animal that does not exist', function () {
    DB::connection('animals')->statement(
        'CALL sp_animal_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)',
        [999999, 'X', 'Dog', null, 'Adult', 'Male', 1.0, null, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Animal not found');
});

it('deletes an animal and reports its previous slot id', function () {
    $animal = Animal::factory()->create(['name' => 'Gone', 'slotID' => 7]);

    DB::connection('animals')->statement(
        'CALL sp_animal_delete(?, ?, ?, ?, @o_animal_name, @o_slot_id, @o_status, @o_message)',
        [$animal->id, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_animal_name AS animal_name, @o_slot_id AS slot_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    expect((int) $result->slot_id)->toBe(7);
    $this->assertDatabaseMissing('animal', ['id' => $animal->id], 'animals');
});

it('returns an error status when deleting an animal that does not exist', function () {
    DB::connection('animals')->statement(
        'CALL sp_animal_delete(?, ?, ?, ?, @o_animal_name, @o_slot_id, @o_status, @o_message)',
        [999999, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Animal not found');
});

// --- sp_animal_assign_slot ---

it('assigns a slot and reports the previous slot id', function () {
    $animal = Animal::factory()->create(['slotID' => 3]);

    DB::connection('animals')->statement(
        'CALL sp_animal_assign_slot(?, ?, ?, ?, ?, @o_previous_slot_id, @o_status, @o_message)',
        [$animal->id, 9, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_previous_slot_id AS previous_slot_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    expect((int) $result->previous_slot_id)->toBe(3);
    $this->assertDatabaseHas('animal', ['id' => $animal->id, 'slotID' => 9], 'animals');
});

it('returns an error when assigning a slot to an animal that does not exist', function () {
    DB::connection('animals')->statement(
        'CALL sp_animal_assign_slot(?, ?, ?, ?, ?, @o_previous_slot_id, @o_status, @o_message)',
        [999999, 9, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
});

// --- trg_animal_validate (direct inserts, bypassing the procedure) ---

it('defaults gender and adoption_status at the trigger level when explicit nulls are inserted', function () {
    $animal = Animal::create([
        'name' => 'Trigger Defaults', 'species' => 'Cat', 'gender' => null, 'adoption_status' => null,
    ]);

    expect($animal->fresh()->gender)->toBe('Unknown');
    expect($animal->fresh()->adoption_status)->toBe('Not Adopted');
});

it('rejects an invalid gender at the DB column level before the trigger ever runs', function () {
    // Pins actual behaviour: `gender` is a native ENUM('Male','Female','Unknown')
    // column, so MySQL's own strict-mode type check rejects an out-of-range
    // value before trg_animal_validate's friendlier `NOT IN (...)` SIGNAL can
    // fire — that branch of the trigger is unreachable dead code in practice.
    expect(fn () => DB::connection('animals')->table('animal')->insert([
        'name' => 'Bad Gender', 'species' => 'Dog', 'gender' => 'Alien', 'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, "Data truncated for column 'gender'");
});

it('rejects a negative weight directly at the DB level', function () {
    expect(fn () => Animal::create([
        'name' => 'Negative', 'species' => 'Dog', 'weight' => -5,
    ]))->toThrow(QueryException::class, 'Weight cannot be negative');
});
