<?php

use App\Models\Category;
use App\Models\Section;
use App\Models\Slot;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('shelter');
});

afterEach(function () {
    $this->truncate('shelter', ['inventory', 'slot', 'category', 'section']);
});

// --- sp_slot_create ---

it('creates a slot on the happy path', function () {
    $section = Section::factory()->create();

    DB::connection('shelter')->statement(
        'CALL sp_slot_create(?, ?, ?, ?, ?, ?, ?, @o_slot_id, @o_status, @o_message)',
        ['Slot-1', $section->id, 3, null, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_slot_id AS slot_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('slot', ['id' => $result->slot_id, 'sectionID' => $section->id, 'status' => 'available'], 'shelter');
});

it('rejects a slot referencing a section that does not exist', function () {
    DB::connection('shelter')->statement(
        'CALL sp_slot_create(?, ?, ?, ?, ?, ?, ?, @o_slot_id, @o_status, @o_message)',
        ['Slot-1', 999999, 3, null, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('The selected section does not exist');
});

it('rejects a slot with capacity less than 1', function () {
    $section = Section::factory()->create();

    DB::connection('shelter')->statement(
        'CALL sp_slot_create(?, ?, ?, ?, ?, ?, ?, @o_slot_id, @o_status, @o_message)',
        ['Slot-1', $section->id, 0, null, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Capacity must be at least 1');
});

it('rejects a slot with capacity less than 1 at the trigger level when inserted directly', function () {
    $section = Section::factory()->create();

    expect(fn () => DB::connection('shelter')->table('slot')->insert([
        'name' => 'Direct', 'sectionID' => $section->id, 'capacity' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'Slot capacity must be at least 1');
});

// --- sp_slot_delete ---

it('blocks deleting a slot that has inventory items', function () {
    $slot = Slot::factory()->create();
    $category = Category::factory()->create();
    DB::connection('shelter')->table('inventory')->insert([
        'item_name' => 'Food', 'slotID' => $slot->id, 'categoryID' => $category->id,
        'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    DB::connection('shelter')->statement(
        'CALL sp_slot_delete(?, ?, ?, ?, @o_has_animals, @o_animal_count, @o_status, @o_message)',
        [$slot->id, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toContain('inventory item');
    $this->assertDatabaseHas('slot', ['id' => $slot->id], 'shelter');
});

it('deletes a slot with no inventory items', function () {
    $slot = Slot::factory()->create();

    DB::connection('shelter')->statement(
        'CALL sp_slot_delete(?, ?, ?, ?, @o_has_animals, @o_animal_count, @o_status, @o_message)',
        [$slot->id, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseMissing('slot', ['id' => $slot->id], 'shelter');
});

// --- sp_inventory_create / sp_inventory_delete ---

it('creates an inventory item on the happy path', function () {
    $slot = Slot::factory()->create();
    $category = Category::factory()->create();

    DB::connection('shelter')->statement(
        'CALL sp_inventory_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_inventory_id, @o_status, @o_message)',
        [$slot->id, 'Dog Food', $category->id, 10, 5.0, 'BrandX', null, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_inventory_id AS inventory_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('inventory', ['id' => $result->inventory_id, 'item_name' => 'Dog Food'], 'shelter');
});

it('rejects a negative quantity at the procedure level', function () {
    $slot = Slot::factory()->create();
    $category = Category::factory()->create();

    DB::connection('shelter')->statement(
        'CALL sp_inventory_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_inventory_id, @o_status, @o_message)',
        [$slot->id, 'Dog Food', $category->id, -1, 5.0, 'BrandX', null, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Quantity must be 0 or greater');
});

it('rejects a negative quantity at the trigger level when inserted directly', function () {
    expect(fn () => DB::connection('shelter')->table('inventory')->insert([
        'item_name' => 'Direct', 'quantity' => -5, 'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'Inventory quantity cannot be negative');
});
