<?php

use App\Models\Animal;
use App\Models\Vet;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('animals');
});

afterEach(function () {
    $this->truncate('animals', ['vaccination', 'medical', 'animal', 'vet', 'clinic']);
});

// --- sp_medical_create ---

it('creates a medical record on the happy path', function () {
    $animal = Animal::factory()->create();
    $vet = Vet::factory()->create();

    DB::connection('animals')->statement(
        'CALL sp_medical_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_medical_id, @o_status, @o_message)',
        [$animal->id, 'Checkup', 'Healthy', 'None', null, $vet->id, 50.0, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_medical_id AS medical_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('medical', ['id' => $result->medical_id, 'animalID' => $animal->id], 'animals');
});

it('surfaces the not-found message for a nonexistent animal instead of a generic error', function () {
    $vet = Vet::factory()->create();

    DB::connection('animals')->statement(
        'CALL sp_medical_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_medical_id, @o_status, @o_message)',
        [999999, 'Checkup', 'Healthy', 'None', null, $vet->id, 50.0, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Animal not found');
});

it('rejects a negative cost at the trigger level', function () {
    $animal = Animal::factory()->create();
    $vet = Vet::factory()->create();

    expect(fn () => DB::connection('animals')->table('medical')->insert([
        'animalID' => $animal->id, 'vetID' => $vet->id, 'treatment_type' => 'Checkup',
        'costs' => -10, 'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'Medical costs cannot be negative');
});

// --- sp_vaccination_create ---

it('creates a vaccination record on the happy path', function () {
    $animal = Animal::factory()->create();
    $vet = Vet::factory()->create();

    DB::connection('animals')->statement(
        'CALL sp_vaccination_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_vaccination_id, @o_status, @o_message)',
        [$animal->id, 'Rabies', 'Core', now()->addYear()->toDateString(), null, $vet->id, 30.0, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_vaccination_id AS vaccination_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('vaccination', ['id' => $result->vaccination_id, 'animalID' => $animal->id], 'animals');
});

it('rejects a next_due_date that is today or in the past', function () {
    $animal = Animal::factory()->create();
    $vet = Vet::factory()->create();

    DB::connection('animals')->statement(
        'CALL sp_vaccination_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_vaccination_id, @o_status, @o_message)',
        [$animal->id, 'Rabies', 'Core', now()->toDateString(), null, $vet->id, 30.0, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Next due date must be in the future');
});

it('rejects a past next_due_date at the trigger level when inserted directly', function () {
    $animal = Animal::factory()->create();

    expect(fn () => DB::connection('animals')->table('vaccination')->insert([
        'animalID' => $animal->id, 'name' => 'Rabies',
        'next_due_date' => now()->subDay()->toDateString(),
        'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'Next due date cannot be in the past');
});
