<?php

use App\Models\Animal;
use App\Models\VisitList;
use Illuminate\Support\Facades\DB;

it('adds an animal to a newly created visit list', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();

    $response = $this->actingAs($user)->post("/visit-list/add/{$animal->id}");

    $response->assertSessionHas('success');
    $visitList = VisitList::where('userID', $user->id)->first();
    expect($visitList)->not->toBeNull();

    $exists = DB::connection('booking')->table('visit_list_animal')
        ->where('listID', $visitList->id)->where('animalID', $animal->id)->exists();
    expect($exists)->toBeTrue();
});

it('reuses the existing visit list on a second add rather than creating another one', function () {
    $user = $this->makeAdopter();
    $first = Animal::factory()->create();
    $second = Animal::factory()->create();

    $this->actingAs($user)->post("/visit-list/add/{$first->id}");
    $this->actingAs($user)->post("/visit-list/add/{$second->id}");

    expect(VisitList::where('userID', $user->id)->count())->toBe(1);
});

it('does not add the same animal twice', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $this->makeVisitListFor($user, [$animal]);

    $response = $this->actingAs($user)->post("/visit-list/add/{$animal->id}");

    $response->assertSessionHas('error', 'This animal is already in your visit list.');
    $visitList = VisitList::where('userID', $user->id)->first();
    $count = DB::connection('booking')->table('visit_list_animal')
        ->where('listID', $visitList->id)->where('animalID', $animal->id)->count();
    expect($count)->toBe(1);
});

it('errors when adding an animal that does not exist', function () {
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->post('/visit-list/add/999999');

    $response->assertSessionHas('error');
    expect(VisitList::where('userID', $user->id)->exists())->toBeFalse();
});

it('removes an animal from the visit list', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $visitList = $this->makeVisitListFor($user, [$animal]);

    $response = $this->actingAs($user)->delete("/visit-list/remove/{$animal->id}");

    $response->assertSessionHas('success');
    $exists = DB::connection('booking')->table('visit_list_animal')
        ->where('listID', $visitList->id)->where('animalID', $animal->id)->exists();
    expect($exists)->toBeFalse();
});

it('errors when removing from a visit list that does not exist', function () {
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->delete('/visit-list/remove/1');

    $response->assertSessionHas('error', 'Visit list not found.');
});

// Regression: indexList() used to pass compact('animals'), but the view
// (booking-adoption/visit-list.blade.php) reads $animalList — every other
// caller of that partial (ViewsAnimals::index/show) already passes it under
// that name. Rendering GET /visit-list directly crashed with "Undefined
// variable $animalList" until fixed.
it('lists only the animals on the current users own visit list', function () {
    $user = $this->makeAdopter();
    $otherUser = $this->makeAdopter();
    $mine = Animal::factory()->create(['name' => 'Mine']);
    $theirs = Animal::factory()->create(['name' => 'Theirs']);
    $this->makeVisitListFor($user, [$mine]);
    $this->makeVisitListFor($otherUser, [$theirs]);

    $response = $this->actingAs($user)->get('/visit-list');

    $response->assertOk();
    $animalList = $response->original->getData()['animalList'];
    expect($animalList->pluck('id')->toArray())->toBe([$mine->id]);
});
