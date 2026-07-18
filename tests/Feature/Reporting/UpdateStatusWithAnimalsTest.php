<?php

use App\Models\Animal;
use App\Models\Report;
use App\Models\Rescue;
use Illuminate\Support\Facades\Config;

function updateStatusWithAnimalsPayload(array $animals): array
{
    return [
        'status' => 'Success',
        'remarks' => 'Animal safely captured and in good health.',
        'animals' => json_encode($animals),
    ];
}

function rescuedAnimalData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Rex',
        'species' => 'Dog',
        'gender' => 'Male',
        'age' => 'Adult',
        'weight' => 12.5,
        'health_details' => 'Healthy, no visible injuries.',
        'adoption_status' => 'Not Adopted',
        'rescueID' => null,
    ], $overrides);
}

it('creates an animal per entry and completes the rescue and report', function () {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create(['report_status' => Report::STATUS_IN_PROGRESS]);
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_IN_PROGRESS]);

    $response = $this->actingAs($caretaker)->patchJson("/rescues/{$rescue->id}/update-status-with-animals", updateStatusWithAnimalsPayload([
        rescuedAnimalData(['rescueID' => $rescue->id, 'name' => 'Rex']),
        rescuedAnimalData(['rescueID' => $rescue->id, 'name' => 'Bella', 'species' => 'Cat']),
    ]));

    $response->assertOk()->assertJson(['success' => true]);

    expect($rescue->fresh()->status)->toBe(Rescue::STATUS_SUCCESS)
        ->and($report->fresh()->report_status)->toBe(Report::STATUS_COMPLETED);

    $created = Animal::whereIn('name', ['Rex', 'Bella'])->get();
    expect($created)->toHaveCount(2);
});

it('refuses to complete a rescue owned by a different caretaker', function () {
    $owner = $this->makeCaretaker();
    $intruder = $this->makeCaretaker();
    $report = Report::factory()->create();
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $owner->id, 'status' => Rescue::STATUS_IN_PROGRESS]);

    $response = $this->actingAs($intruder)->patchJson("/rescues/{$rescue->id}/update-status-with-animals", updateStatusWithAnimalsPayload([
        rescuedAnimalData(['rescueID' => $rescue->id]),
    ]));

    $response->assertStatus(404);
    expect($rescue->fresh()->status)->toBe(Rescue::STATUS_IN_PROGRESS);
    expect(Animal::where('rescueID', $rescue->id)->exists())->toBeFalse();
});

it('requires at least one animal', function () {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create();
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_IN_PROGRESS]);

    $response = $this->actingAs($caretaker)->patchJson("/rescues/{$rescue->id}/update-status-with-animals", updateStatusWithAnimalsPayload([]));

    $response->assertStatus(500);
    expect($rescue->fresh()->status)->toBe(Rescue::STATUS_IN_PROGRESS);
});

it('returns 503 when the shelter database is offline', function () {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create();
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_IN_PROGRESS]);

    Config::set('database.connections.shelter.host', '127.0.0.1');
    Config::set('database.connections.shelter.port', 1);

    $response = $this->actingAs($caretaker)->patchJson("/rescues/{$rescue->id}/update-status-with-animals", updateStatusWithAnimalsPayload([
        rescuedAnimalData(['rescueID' => $rescue->id]),
    ]));

    $response->assertStatus(503);
});

it('rolls back the rescue and report status when an animal insert fails partway through', function () {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create(['report_status' => Report::STATUS_IN_PROGRESS]);
    // trg_rescue_after_insert (Phase 2) syncs report_status to Assigned the
    // moment a rescue row is inserted, regardless of what it was set to above
    // — that's the real starting point rollback must return to, not the
    // value the factory requested a moment earlier.
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_IN_PROGRESS]);
    $reportStatusBeforeRequest = $report->fresh()->report_status;

    $response = $this->actingAs($caretaker)->patchJson("/rescues/{$rescue->id}/update-status-with-animals", updateStatusWithAnimalsPayload([
        rescuedAnimalData(['rescueID' => $rescue->id, 'name' => 'Good Animal']),
        // decimal(8,2) overflow — forces a real QueryException partway through the loop.
        rescuedAnimalData(['rescueID' => $rescue->id, 'name' => 'Bad Animal', 'weight' => 123456789]),
    ]));

    $response->assertStatus(500);

    expect($rescue->fresh()->status)->toBe(Rescue::STATUS_IN_PROGRESS)
        ->and($report->fresh()->report_status)->toBe($reportStatusBeforeRequest)
        ->and(Animal::whereIn('name', ['Good Animal', 'Bad Animal'])->exists())->toBeFalse();
});
