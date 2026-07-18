<?php

use App\Models\Animal;
use App\Models\Slot;

it('assigns a slot and marks it occupied once it reaches capacity', function () {
    $caretaker = $this->makeCaretaker();
    $animal = Animal::factory()->create();
    $slot = Slot::factory()->create(['status' => 'available', 'capacity' => 1]);

    $response = $this->actingAs($caretaker)->post("/animals/{$animal->id}/assign-slot", ['slot_id' => $slot->id]);

    $response->assertSessionHas('success');
    expect($animal->fresh()->slotID)->toBe($slot->id);
    expect($slot->fresh()->status)->toBe('occupied');
});

it('frees the previous slot when an animal is reassigned to a new one', function () {
    $caretaker = $this->makeCaretaker();
    $oldSlot = Slot::factory()->create(['status' => 'occupied', 'capacity' => 1]);
    $newSlot = Slot::factory()->create(['status' => 'available', 'capacity' => 1]);
    $animal = Animal::factory()->create(['slotID' => $oldSlot->id]);

    $response = $this->actingAs($caretaker)->post("/animals/{$animal->id}/assign-slot", ['slot_id' => $newSlot->id]);

    $response->assertSessionHas('success');
    expect($oldSlot->fresh()->status)->toBe('available');
    expect($newSlot->fresh()->status)->toBe('occupied');
});

it('rejects a slot id that does not exist', function () {
    $caretaker = $this->makeCaretaker();
    $animal = Animal::factory()->create();

    $response = $this->actingAs($caretaker)->post("/animals/{$animal->id}/assign-slot", ['slot_id' => 999999]);

    $response->assertSessionHasErrors('slot_id');
    expect($animal->fresh()->slotID)->toBeNull();
});
