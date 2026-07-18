<?php

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Slot;
use Tests\Concerns\TruncatesDistributedDatabases;

// sp_inventory_* self-commits (see Reporting tests for why), so truncate
// explicitly rather than relying on transaction rollback.
uses(TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->truncate('shelter', ['inventory', 'slot', 'section', 'category']);
});

it('creates an inventory item', function () {
    $admin = $this->makeAdmin();
    $slot = Slot::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->post('/shelter-management/inventory', [
        'slotID' => $slot->id,
        'item_name' => 'Dog Food 5kg',
        'categoryID' => $category->id,
        'quantity' => 10,
        'status' => 'available',
    ]);

    $response->assertSessionHas('success');
    expect(Inventory::where('item_name', 'Dog Food 5kg')->where('slotID', $slot->id)->exists())->toBeTrue();
});

it('updates an inventory item', function () {
    $admin = $this->makeAdmin();
    $category = Category::factory()->create();
    $inventory = Inventory::factory()->create(['categoryID' => $category->id, 'quantity' => 10, 'status' => 'available']);

    $response = $this->actingAs($admin)->put("/shelter-management/inventory/{$inventory->id}", [
        'item_name' => $inventory->item_name,
        'categoryID' => $category->id,
        'quantity' => 0,
        'status' => 'out',
    ]);

    $response->assertSessionHas('success');
    expect($inventory->fresh()->quantity)->toBe(0)
        ->and($inventory->fresh()->status)->toBe('out');
});

it('deletes an inventory item', function () {
    $admin = $this->makeAdmin();
    $inventory = Inventory::factory()->create();

    $response = $this->actingAs($admin)->delete("/shelter-management/inventory/{$inventory->id}");

    $response->assertSessionHas('success');
    expect(Inventory::find($inventory->id))->toBeNull();
});

it('rejects an invalid status value', function () {
    $admin = $this->makeAdmin();
    $slot = Slot::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->post('/shelter-management/inventory', [
        'slotID' => $slot->id,
        'item_name' => 'Dog Food 5kg',
        'categoryID' => $category->id,
        'quantity' => 10,
        'status' => 'not-a-real-status',
    ]);

    $response->assertSessionHasErrors('status');
    expect(Inventory::count())->toBe(0);
});
