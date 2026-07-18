<?php

use App\Models\Clinic;
use App\Models\Vet;
use Tests\Concerns\TruncatesDistributedDatabases;

// sp_clinic_create/sp_vet_create self-commit (see Reporting tests for why),
// so rows (and sp_vet_create's duplicate-email check) leak across test runs.
// TRUNCATE is safe here (unlike AnimalCrudTest) since none of these routes
// open their own nested DB::beginTransaction().
uses(TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->truncate('animals', ['vet', 'clinic']);
});

it('creates a clinic', function () {
    $caretaker = $this->makeCaretaker();

    $response = $this->actingAs($caretaker)->post('/store-clinics', [
        'clinic_name' => 'Happy Paws Clinic',
        'address' => '1 Jalan Test',
        'phone' => '0123456789',
        'latitude' => 3.139,
        'longitude' => 101.6869,
    ]);

    $response->assertSessionHas('success');
    expect(Clinic::where('name', 'Happy Paws Clinic')->exists())->toBeTrue();
});

it('updates a clinic', function () {
    $caretaker = $this->makeCaretaker();
    $clinic = Clinic::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($caretaker)->put("/clinics/{$clinic->id}", [
        'name' => 'New Name',
        'address' => $clinic->address,
        'contactNum' => $clinic->contactNum,
        'latitude' => 3.139,
        'longitude' => 101.6869,
    ]);

    $response->assertSessionHas('success');
    expect($clinic->fresh()->name)->toBe('New Name');
});

it('deletes a clinic', function () {
    $caretaker = $this->makeCaretaker();
    $clinic = Clinic::factory()->create();

    $response = $this->actingAs($caretaker)->delete("/clinics/{$clinic->id}");

    $response->assertSessionHas('success');
    expect(Clinic::find($clinic->id))->toBeNull();
});

it('creates a vet linked to a clinic', function () {
    $caretaker = $this->makeCaretaker();
    $clinic = Clinic::factory()->create();

    $response = $this->actingAs($caretaker)->post('/store-vets', [
        'full_name' => 'Dr. Tan',
        'specialization' => 'Surgery',
        'license_no' => 'LIC-1234',
        'clinicID' => $clinic->id,
        'phone' => '0123456789',
        'email' => 'dr.tan@example.com',
    ]);

    $response->assertSessionHas('success');
    expect(Vet::where('name', 'Dr. Tan')->where('clinicID', $clinic->id)->exists())->toBeTrue();
});

it('rejects a vet with a clinicID that does not exist', function () {
    $caretaker = $this->makeCaretaker();

    $response = $this->actingAs($caretaker)->post('/store-vets', [
        'full_name' => 'Dr. Tan',
        'specialization' => 'Surgery',
        'license_no' => 'LIC-1234',
        'clinicID' => 999999,
        'phone' => '0123456789',
        'email' => 'dr.tan@example.com',
    ]);

    $response->assertSessionHasErrors('clinicID');
});

it('deletes a vet', function () {
    $caretaker = $this->makeCaretaker();
    $vet = Vet::factory()->create();

    $response = $this->actingAs($caretaker)->delete("/vets/{$vet->id}");

    $response->assertSessionHas('success');
    expect(Vet::find($vet->id))->toBeNull();
});
