<?php

use App\Models\Animal;
use App\Models\Medical;
use App\Models\Vaccination;
use App\Models\Vet;

it('creates a medical record for an animal', function () {
    $caretaker = $this->makeCaretaker();
    $animal = Animal::factory()->create();
    $vet = Vet::factory()->create();

    $response = $this->actingAs($caretaker)->post('/medical-records/store', [
        'animalID' => $animal->id,
        'treatment_type' => 'Checkup',
        'diagnosis' => 'Mild ear infection',
        'action' => 'Prescribed antibiotics',
        'vetID' => $vet->id,
        'costs' => 45.5,
    ]);

    $response->assertSessionHas('success');
    expect(Medical::where('animalID', $animal->id)->where('vetID', $vet->id)->exists())->toBeTrue();
});

it('rejects a medical record for an animal that does not exist', function () {
    $caretaker = $this->makeCaretaker();
    $vet = Vet::factory()->create();

    $response = $this->actingAs($caretaker)->post('/medical-records/store', [
        'animalID' => 999999,
        'treatment_type' => 'Checkup',
        'diagnosis' => 'Mild ear infection',
        'action' => 'Prescribed antibiotics',
        'vetID' => $vet->id,
    ]);

    $response->assertSessionHasErrors('animalID');
});

it('creates a vaccination record for an animal', function () {
    $caretaker = $this->makeCaretaker();
    $animal = Animal::factory()->create();
    $vet = Vet::factory()->create();

    $response = $this->actingAs($caretaker)->post('/vaccination-records/store', [
        'animalID' => $animal->id,
        'name' => 'Rabies',
        'type' => 'Core',
        'vetID' => $vet->id,
        'costs' => 20,
    ]);

    $response->assertSessionHas('success');
    expect(Vaccination::where('animalID', $animal->id)->where('name', 'Rabies')->exists())->toBeTrue();
});

it('rejects a vaccination next_due_date that is not in the future', function () {
    $caretaker = $this->makeCaretaker();
    $animal = Animal::factory()->create();
    $vet = Vet::factory()->create();

    $response = $this->actingAs($caretaker)->post('/vaccination-records/store', [
        'animalID' => $animal->id,
        'name' => 'Rabies',
        'type' => 'Core',
        'vetID' => $vet->id,
        'next_due_date' => now()->subDay()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors('next_due_date');
});
