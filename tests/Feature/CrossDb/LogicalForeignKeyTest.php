<?php

use App\Models\Animal;
use App\Models\Booking;
use App\Models\Rescue;
use App\Models\Report;
use App\Models\VisitList;
use Illuminate\Support\Facades\DB;

/**
 * One test per logical FK in docs/foreign-keys.md, exercising whatever mechanism
 * actually protects that write path today (see docs/foreign-keys.md "Layer 1" —
 * there is no single central validator; each write path checks the specific
 * cross-DB id it accepts).
 *
 * Not every logical FK gets a test here:
 * - report.userID, booking.userID, transaction.userID, visit_list.userID are never
 *   taken from client input at all (always Auth::id()) — covered below by asserting
 *   a client-supplied override is ignored, rather than an "invalid id rejected" test.
 * - image.animalID / image.clinicID are never populated from raw request input on
 *   any reachable path (grep confirms both call sites: report-image upload always
 *   passes null for both; the rescue-to-animal image copy passes the id of an
 *   Animal record the same request just created). There is no way to reach these
 *   fields with an attacker-controlled id, so there is nothing to pin.
 */

// --- animal.rescueID -> reporting.rescue (Laravel exists:reporting.rescue,id) ---

it('rejects creating an animal with a non-existent rescueID', function () {
    $admin = $this->makeAdmin();

    $response = $this->actingAs($admin)->post('/animal/store', [
        'name' => 'Fluffy',
        'weight' => 4.2,
        'species' => 'cat',
        'health_details' => 'Healthy',
        'gender' => 'Female',
        'rescueID' => 999999,
    ]);

    $response->assertSessionHasErrors('rescueID');
    expect(Animal::where('name', 'Fluffy')->exists())->toBeFalse();
});

it('accepts creating an animal with a real rescueID', function () {
    $admin = $this->makeAdmin();
    $rescue = Rescue::factory()->create();

    $response = $this->actingAs($admin)->post('/animal/store', [
        'name' => 'Fluffy',
        'weight' => 4.2,
        'species' => 'cat',
        'health_details' => 'Healthy',
        'gender' => 'Female',
        'rescueID' => $rescue->id,
        // age_category and slotID both omitted deliberately: both are 'nullable' in
        // store()'s validation rules, so a valid request is allowed to skip them.
    ]);

    $response->assertSessionHasNoErrors();
    expect(Animal::where('name', 'Fluffy')->where('rescueID', $rescue->id)->exists())->toBeTrue();
});

// --- animal.slotID -> shelter.slot (Laravel exists:shelter.slot,id) ---

it('rejects assigning an animal to a non-existent slotID', function () {
    $admin = $this->makeAdmin();
    $animal = $this->makeAnimalWithProfile();

    $response = $this->actingAs($admin)->post("/animals/{$animal->id}/assign-slot", [
        'slot_id' => 999999,
    ]);

    $response->assertSessionHasErrors('slot_id');
    expect($animal->fresh()->slotID)->toBeNull();
});

// --- rescue.caretakerID -> users.users (inline User::findOrFail) ---

it('rejects assigning a non-existent caretaker to a rescue report', function () {
    $admin = $this->makeAdmin();
    $report = Report::factory()->create(['report_status' => Report::STATUS_PENDING]);

    $response = $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", [
        'caretaker_id' => 999999,
    ]);

    $response->assertSessionHas('error');
    expect(Rescue::where('reportID', $report->id)->exists())->toBeFalse();
});

it('accepts assigning a real caretaker to a rescue report', function () {
    $admin = $this->makeAdmin();
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create(['report_status' => Report::STATUS_PENDING]);

    $response = $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", [
        'caretaker_id' => $caretaker->id,
    ]);

    $response->assertSessionHas('success');
    expect(Rescue::where('reportID', $report->id)->where('caretakerID', $caretaker->id)->exists())->toBeTrue();
});

// --- animal_booking.animalID -> animals.animal (exists:animals.animal,id) ---
// --- booking.userID -> users.users (always Auth::id(), never client input) ---

it('rejects booking a non-existent animalID', function () {
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->post('/adoption/book', [
        'animalID' => 999999,
        'appointment_date' => now()->addDay()->toDateTimeString(),
        'terms' => true,
    ]);

    $response->assertSessionHasErrors('animalID');
    expect(Booking::where('userID', $user->id)->exists())->toBeFalse();
});

it('ignores a client-supplied userID and always books under the authenticated user', function () {
    $user = $this->makeAdopter();
    $someoneElse = $this->makeAdopter();
    $animal = $this->makeAnimalWithProfile(['adoption_status' => 'Not Adopted']);

    $response = $this->actingAs($user)->post('/adoption/book', [
        'animalID' => $animal->id,
        'appointment_date' => now()->addDay()->toDateTimeString(),
        'terms' => true,
        'userID' => $someoneElse->id, // not a real field on this form — must be ignored
    ]);

    $response->assertSessionHasNoErrors();
    $booking = Booking::where('userID', $user->id)->first();
    expect($booking)->not->toBeNull();
    expect(Booking::where('userID', $someoneElse->id)->exists())->toBeFalse();
});

// --- visit_list_animal.animalID -> animals.animal (inline Animal::find check) ---

it('rejects adding a non-existent animal to the visit list', function () {
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->post('/visit-list/add/999999');

    $response->assertSessionHas('error');
    $visitList = VisitList::where('userID', $user->id)->first();
    expect($visitList)->toBeNull();
});

it('accepts adding a real animal to the visit list', function () {
    $user = $this->makeAdopter();
    $animal = $this->makeAnimalWithProfile();

    $response = $this->actingAs($user)->post("/visit-list/add/{$animal->id}");

    $response->assertSessionHas('success');
    $visitList = VisitList::where('userID', $user->id)->first();
    expect($visitList)->not->toBeNull();
    // Queried the same way addList()/removeList() do (DB::table on the 'booking'
    // connection), not via VisitList::animals() — that Eloquent relation sets
    // setConnection('booking') on a belongsToMany whose base query still starts
    // from Animal's own 'animals' connection, so the pivot join runs against the
    // wrong server and throws "Base table or view not found: visit_list_animal".
    // Confirmed dead: grep finds zero callers of VisitList::animals() or its
    // inverse Animal::visitLists() anywhere in app/ — exactly the same cross-
    // server-join landmine Booking::animals() turned out to be in callback()
    // before this test session's payment fix, just never actually triggered here.
    $inVisitList = DB::connection('booking')->table('visit_list_animal')
        ->where('listID', $visitList->id)
        ->where('animalID', $animal->id)
        ->exists();
    expect($inVisitList)->toBeTrue();
});
