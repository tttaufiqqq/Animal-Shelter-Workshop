<?php

use App\Models\Clinic;
use App\Models\Medical;
use App\Models\Vet;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('animals');
});

afterEach(function () {
    // Medical/Vaccination factories nest Animal::factory(), so 'animal' needs
    // truncating too even though these tests don't assert on it directly.
    $this->truncate('animals', ['vaccination', 'medical', 'animal', 'vet', 'clinic']);
});

function callVetCreate(array $overrides = []): object
{
    $p = array_merge([
        'name' => 'Dr. Vet', 'email' => 'vet@example.test', 'contact_num' => '0123456789',
        'specialization' => 'Surgery', 'license_no' => 'LIC-1', 'clinic_id' => null,
        'user_id' => null, 'user_name' => null, 'user_email' => null,
    ], $overrides);

    DB::connection('animals')->statement(
        'CALL sp_vet_create(?, ?, ?, ?, ?, ?, ?, ?, ?, @o_vet_id, @o_status, @o_message)',
        array_values($p)
    );

    return DB::connection('animals')->selectOne('SELECT @o_vet_id AS vet_id, @o_status AS `status`, @o_message AS `message`');
}

// --- sp_clinic_create / sp_clinic_read / sp_clinic_read_all ---

it('creates and reads back a clinic', function () {
    DB::connection('animals')->statement(
        'CALL sp_clinic_create(?, ?, ?, ?, ?, ?, ?, ?, @o_clinic_id, @o_status, @o_message)',
        ['Main Clinic', '1 Main St', '0123456789', null, null, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_clinic_id AS clinic_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');

    $rows = DB::connection('animals')->select('CALL sp_clinic_read(?)', [$result->clinic_id]);
    expect($rows[0]->name)->toBe('Main Clinic');
});

it('reports the vet count per clinic in sp_clinic_read_all', function () {
    $clinic = Clinic::factory()->create(['name' => 'Counted Clinic']);
    Vet::factory()->create(['clinicID' => $clinic->id]);
    Vet::factory()->create(['clinicID' => $clinic->id]);

    $rows = DB::connection('animals')->select('CALL sp_clinic_read_all()');
    $row = collect($rows)->firstWhere('id', $clinic->id);

    expect((int) $row->vet_count)->toBe(2);
});

it('blocks deleting a clinic that has vets', function () {
    $clinic = Clinic::factory()->create();
    Vet::factory()->create(['clinicID' => $clinic->id]);

    DB::connection('animals')->statement(
        'CALL sp_clinic_delete(?, ?, ?, ?, @o_status, @o_message)',
        [$clinic->id, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toContain('associated veterinarians');
    $this->assertDatabaseHas('clinic', ['id' => $clinic->id], 'animals');
});

it('deletes a clinic that has no vets', function () {
    $clinic = Clinic::factory()->create();

    DB::connection('animals')->statement(
        'CALL sp_clinic_delete(?, ?, ?, ?, @o_status, @o_message)',
        [$clinic->id, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseMissing('clinic', ['id' => $clinic->id], 'animals');
});

// --- sp_vet_create / sp_vet_read_all / sp_vet_delete ---

it('creates a vet and rejects a duplicate email case-insensitively', function () {
    $first = callVetCreate(['email' => 'Dupe@Example.test']);
    expect($first->status)->toBe('success');

    $second = callVetCreate(['email' => 'dupe@example.test']);

    expect($second->status)->toBe('error');
    expect($second->message)->toBe('Email already exists');
});

it('lists vets with their clinic name in sp_vet_read_all', function () {
    $clinic = Clinic::factory()->create(['name' => 'Listed Clinic']);
    $vet = Vet::factory()->create(['clinicID' => $clinic->id, 'name' => 'Listed Vet']);

    $rows = DB::connection('animals')->select('CALL sp_vet_read_all()');
    $row = collect($rows)->firstWhere('id', $vet->id);

    expect($row->clinic_name)->toBe('Listed Clinic');
});

it('blocks deleting a vet with associated medical or vaccination records', function () {
    $vet = Vet::factory()->create();
    Medical::factory()->create(['vetID' => $vet->id]);

    DB::connection('animals')->statement(
        'CALL sp_vet_delete(?, ?, ?, ?, @o_status, @o_message)',
        [$vet->id, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    $this->assertDatabaseHas('vet', ['id' => $vet->id], 'animals');
});

it('deletes a vet with no associated records', function () {
    $vet = Vet::factory()->create();

    DB::connection('animals')->statement(
        'CALL sp_vet_delete(?, ?, ?, ?, @o_status, @o_message)',
        [$vet->id, null, null, null]
    );
    $result = DB::connection('animals')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseMissing('vet', ['id' => $vet->id], 'animals');
});
