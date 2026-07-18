<?php

use App\Models\Animal;
use App\Models\AnimalProfile;
use App\Models\Image;
use App\Models\Rescue;
use App\Models\Slot;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\FakesCloudinary;

uses(FakesCloudinary::class);

// sp_animal_create/update/delete self-commit (see Reporting tests for why),
// so rows leak across tests despite UsesDistributedDatabases' wrapping
// transaction. Deliberately a plain DELETE, not TRUNCATE: store()/update()
// each open their own nested DB::beginTransaction() on 'reporting', and
// TRUNCATE is DDL — MySQL implicitly commits the outer wrapping transaction
// out from under Laravel's transaction-depth counter, so a later nested
// beginTransaction()/rollBack() in the same test tries to create/release a
// SAVEPOINT that was never really opened ("SAVEPOINT trans2 does not
// exist"). A plain DELETE is DML — it doesn't trigger an implicit commit, so
// it composes safely with both the outer transaction and any subsequent
// nested one.
beforeEach(function () {
    AnimalProfile::query()->delete();
    Animal::query()->delete();
    Image::query()->delete();
});

function animalPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Rex',
        'weight' => 12.5,
        'species' => 'Dog',
        'health_details' => 'Healthy',
        'age_category' => 'adult',
        'gender' => 'Male',
    ], $overrides);
}

it('rejects a rescueID that does not exist', function () {
    $this->fakeCloudinary();
    $caretaker = $this->makeCaretaker();

    $response = $this->actingAs($caretaker)->post('/animal/store', animalPayload(['rescueID' => 999999]));

    $response->assertSessionHasErrors('rescueID');
    expect(Animal::count())->toBe(0);
});

it('rejects a slotID that does not exist on the shelter connection', function () {
    $this->fakeCloudinary();
    $caretaker = $this->makeCaretaker();
    $rescue = Rescue::factory()->create();

    $response = $this->actingAs($caretaker)->post('/animal/store', animalPayload([
        'rescueID' => $rescue->id,
        'slotID' => 999999,
    ]));

    $response->assertSessionHasErrors('slotID');
    expect(Animal::count())->toBe(0);
});

it('creates an animal with Not Adopted status and stores uploaded images', function () {
    $this->fakeCloudinary();
    $caretaker = $this->makeCaretaker();
    $rescue = Rescue::factory()->create();

    $response = $this->actingAs($caretaker)->post('/animal/store', animalPayload([
        'rescueID' => $rescue->id,
        'images' => [UploadedFile::fake()->image('a.jpg')],
    ]));

    $response->assertSessionHas('success');

    $animal = Animal::where('name', 'Rex')->first();
    expect($animal)->not->toBeNull()
        ->and($animal->adoption_status)->toBe('Not Adopted')
        ->and($animal->rescueID)->toBe($rescue->id)
        ->and(Image::where('animalID', $animal->id)->count())->toBe(1);
});

it('refuses to assign a slot that is not available', function () {
    $this->fakeCloudinary();
    $caretaker = $this->makeCaretaker();
    $rescue = Rescue::factory()->create();
    $slot = Slot::factory()->create(['status' => 'occupied']);

    $response = $this->actingAs($caretaker)->post('/animal/store', animalPayload([
        'rescueID' => $rescue->id,
        'slotID' => $slot->id,
    ]));

    $response->assertSessionHasErrors('slotID');
    expect(Animal::count())->toBe(0);
});

it('updates an animals fields', function () {
    $this->fakeCloudinary();
    $caretaker = $this->makeCaretaker();
    $animal = Animal::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($caretaker)->put("/animal/{$animal->id}", animalPayload(['name' => 'New Name']));

    $response->assertSessionHas('success');
    expect($animal->fresh()->name)->toBe('New Name');
});

it('deletes an animal and frees its slot', function () {
    $this->fakeCloudinary();
    $caretaker = $this->makeCaretaker();
    $slot = Slot::factory()->create(['status' => 'occupied', 'capacity' => 1]);
    $animal = Animal::factory()->create(['slotID' => $slot->id]);

    $response = $this->actingAs($caretaker)->delete("/animal/{$animal->id}");

    $response->assertSessionHas('success');
    expect(Animal::find($animal->id))->toBeNull();
    expect($slot->fresh()->status)->toBe('available');
});
