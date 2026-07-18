<?php

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Section;
use App\Models\Slot;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('shelter');
});

afterEach(function () {
    $this->truncate('shelter', ['inventory', 'slot', 'category', 'section']);
});

// --- sp_section_create / sp_section_delete ---

it('creates a section on the happy path', function () {
    DB::connection('shelter')->statement(
        'CALL sp_section_create(?, ?, ?, ?, ?, @o_section_id, @o_status, @o_message)',
        ['Wing A', 'North wing', null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_section_id AS section_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('section', ['id' => $result->section_id, 'name' => 'Wing A'], 'shelter');
});

it('rejects a section with a missing description', function () {
    DB::connection('shelter')->statement(
        'CALL sp_section_create(?, ?, ?, ?, ?, @o_section_id, @o_status, @o_message)',
        ['Wing A', null, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Section description is required');
});

it('blocks deleting a section that has slots', function () {
    $section = Section::factory()->create();
    Slot::factory()->create(['sectionID' => $section->id]);

    DB::connection('shelter')->statement(
        'CALL sp_section_delete(?, ?, ?, ?, @o_has_slots, @o_status, @o_message)',
        [$section->id, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_has_slots AS has_slots, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect((bool) $result->has_slots)->toBeTrue();
    $this->assertDatabaseHas('section', ['id' => $section->id], 'shelter');
});

it('deletes a section with no slots', function () {
    $section = Section::factory()->create();

    DB::connection('shelter')->statement(
        'CALL sp_section_delete(?, ?, ?, ?, @o_has_slots, @o_status, @o_message)',
        [$section->id, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseMissing('section', ['id' => $section->id], 'shelter');
});

it('sets a slot section to null when its section is deleted directly, bypassing the procedure guard', function () {
    $section = Section::factory()->create();
    $slot = Slot::factory()->create(['sectionID' => $section->id]);

    DB::connection('shelter')->table('section')->where('id', $section->id)->delete();

    $this->assertDatabaseHas('slot', ['id' => $slot->id, 'sectionID' => null], 'shelter');
});

// --- sp_category_create / sp_category_delete ---

it('creates a category on the happy path', function () {
    DB::connection('shelter')->statement(
        'CALL sp_category_create(?, ?, ?, ?, ?, @o_category_id, @o_status, @o_message)',
        ['Food', 'Dry', null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_category_id AS category_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('category', ['id' => $result->category_id, 'main' => 'Food'], 'shelter');
});

it('blocks deleting a category that has inventory items', function () {
    $category = Category::factory()->create();
    Inventory::factory()->create(['categoryID' => $category->id]);

    DB::connection('shelter')->statement(
        'CALL sp_category_delete(?, ?, ?, ?, @o_has_inventories, @o_status, @o_message)',
        [$category->id, null, null, null]
    );
    $result = DB::connection('shelter')->selectOne('SELECT @o_has_inventories AS has_inventories, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect((bool) $result->has_inventories)->toBeTrue();
});

it('sets an inventory category to null when its category is deleted directly, bypassing the procedure guard', function () {
    $category = Category::factory()->create();
    $item = Inventory::factory()->create(['categoryID' => $category->id]);

    DB::connection('shelter')->table('category')->where('id', $category->id)->delete();

    $this->assertDatabaseHas('inventory', ['id' => $item->id, 'categoryID' => null], 'shelter');
});
