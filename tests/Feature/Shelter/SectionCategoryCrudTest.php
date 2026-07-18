<?php

use App\Models\Category;
use App\Models\Section;
use App\Models\Slot;
use Tests\Concerns\TruncatesDistributedDatabases;

// sp_section_*/sp_category_* self-commit (see Reporting tests for why), so
// truncate explicitly rather than relying on transaction rollback.
uses(TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->truncate('shelter', ['slot', 'section', 'category']);
});

it('creates a section', function () {
    $admin = $this->makeAdmin();

    $response = $this->actingAs($admin)->post('/shelter-management/sections', [
        'name' => 'Wing A',
        'description' => 'North wing, dogs',
    ]);

    $response->assertSessionHas('success');
    expect(Section::where('name', 'Wing A')->exists())->toBeTrue();
});

it('updates a section', function () {
    $admin = $this->makeAdmin();
    $section = Section::factory()->create(['name' => 'Old']);

    $response = $this->actingAs($admin)->put("/shelter-management/sections/{$section->id}", [
        'name' => 'New',
        'description' => $section->description,
    ]);

    $response->assertSessionHas('success');
    expect($section->fresh()->name)->toBe('New');
});

it('deletes an empty section', function () {
    $admin = $this->makeAdmin();
    $section = Section::factory()->create();

    $response = $this->actingAs($admin)->delete("/shelter-management/sections/{$section->id}");

    $response->assertSessionHas('success');
    expect(Section::find($section->id))->toBeNull();
});

it('creates a category', function () {
    $admin = $this->makeAdmin();

    $response = $this->actingAs($admin)->post('/shelter-management/categories', [
        'main' => 'Food',
        'sub' => 'Dry kibble',
    ]);

    $response->assertSessionHas('success');
    expect(Category::where('main', 'Food')->where('sub', 'Dry kibble')->exists())->toBeTrue();
});

it('updates a category', function () {
    $admin = $this->makeAdmin();
    $category = Category::factory()->create(['sub' => 'Old']);

    $response = $this->actingAs($admin)->put("/shelter-management/categories/{$category->id}", [
        'main' => $category->main,
        'sub' => 'New',
    ]);

    $response->assertSessionHas('success');
    expect($category->fresh()->sub)->toBe('New');
});

it('deletes an unused category', function () {
    $admin = $this->makeAdmin();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->delete("/shelter-management/categories/{$category->id}");

    $response->assertSessionHas('success');
    expect(Category::find($category->id))->toBeNull();
});
