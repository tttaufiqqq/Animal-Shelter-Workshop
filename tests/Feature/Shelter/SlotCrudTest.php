<?php

use App\Models\Animal;
use App\Models\Section;
use App\Models\Slot;
use Tests\Concerns\TruncatesDistributedDatabases;

// sp_slot_create/update/delete self-commit (see Reporting tests for why), so
// truncate explicitly rather than relying on transaction rollback. Safe here
// (unlike AnimalCrudTest) since none of these routes open their own nested
// DB::beginTransaction().
uses(TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->truncate('shelter', ['slot', 'section']);
});

it('creates a slot with status forced to available', function () {
    $admin = $this->makeAdmin();
    $section = Section::factory()->create();

    $response = $this->actingAs($admin)->post('/shelter-management/slots', [
        'name' => 'Slot A1',
        'sectionID' => $section->id,
        'capacity' => 3,
    ]);

    $response->assertSessionHas('success');
    $slot = Slot::where('name', 'Slot A1')->first();
    expect($slot)->not->toBeNull()->and($slot->status)->toBe('available');
});

it('marks a slot occupied once the animal count reaches capacity', function () {
    $admin = $this->makeAdmin();
    $slot = Slot::factory()->create(['capacity' => 1, 'status' => 'available']);
    Animal::factory()->create(['slotID' => $slot->id]);

    $response = $this->actingAs($admin)->put("/shelter-management/slots/{$slot->id}", [
        'name' => $slot->name,
        'sectionID' => $slot->sectionID,
        'capacity' => 1,
    ]);

    $response->assertSessionHas('success');
    expect($slot->fresh()->status)->toBe('occupied');
});

it('lets an update explicitly set a slot to maintenance regardless of occupancy', function () {
    $admin = $this->makeAdmin();
    $slot = Slot::factory()->create(['capacity' => 5, 'status' => 'available']);

    $response = $this->actingAs($admin)->put("/shelter-management/slots/{$slot->id}", [
        'name' => $slot->name,
        'sectionID' => $slot->sectionID,
        'capacity' => 5,
        'status' => 'maintenance',
    ]);

    $response->assertSessionHas('success');
    expect($slot->fresh()->status)->toBe('maintenance');
});

it('refuses to delete a slot that still has animals', function () {
    $admin = $this->makeAdmin();
    $slot = Slot::factory()->create();
    Animal::factory()->create(['slotID' => $slot->id]);

    $response = $this->actingAs($admin)->delete("/shelter-management/slots/{$slot->id}");

    $response->assertSessionHas('error');
    expect(Slot::find($slot->id))->not->toBeNull();
});

it('deletes an empty slot', function () {
    $admin = $this->makeAdmin();
    $slot = Slot::factory()->create();

    $response = $this->actingAs($admin)->delete("/shelter-management/slots/{$slot->id}");

    $response->assertSessionHas('success');
    expect(Slot::find($slot->id))->toBeNull();
});
