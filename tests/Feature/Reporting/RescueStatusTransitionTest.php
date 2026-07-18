<?php

use App\Models\Report;
use App\Models\Rescue;
use Tests\Concerns\TruncatesDistributedDatabases;

// sp_rescue_update_status self-commits (see ReportSubmissionTest for why),
// so truncate explicitly rather than relying on transaction rollback.
uses(TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->truncate('reporting', ['rescue', 'report']);
});

it('updates the rescue status for its own caretaker', function () {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create(['report_status' => Report::STATUS_ASSIGNED]);
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_SCHEDULED]);

    $response = $this->actingAs($caretaker)->patch("/rescues/{$rescue->id}/update-status", [
        'status' => Rescue::STATUS_IN_PROGRESS,
    ]);

    $response->assertSessionHas('success');
    expect($rescue->fresh()->status)->toBe(Rescue::STATUS_IN_PROGRESS);
});

it('refuses to update a rescue owned by a different caretaker', function () {
    $owner = $this->makeCaretaker();
    $intruder = $this->makeCaretaker();
    $report = Report::factory()->create();
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $owner->id, 'status' => Rescue::STATUS_SCHEDULED]);

    $response = $this->actingAs($intruder)->patch("/rescues/{$rescue->id}/update-status", [
        'status' => Rescue::STATUS_IN_PROGRESS,
    ]);

    $response->assertSessionHas('error');
    expect($rescue->fresh()->status)->toBe(Rescue::STATUS_SCHEDULED);
});

it('requires remarks when moving to Success or Failed', function (string $status) {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create();
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_IN_PROGRESS]);

    $response = $this->actingAs($caretaker)->patch("/rescues/{$rescue->id}/update-status", [
        'status' => $status,
    ]);

    $response->assertSessionHasErrors('remarks');
    expect($rescue->fresh()->status)->toBe(Rescue::STATUS_IN_PROGRESS);
})->with([Rescue::STATUS_SUCCESS, Rescue::STATUS_FAILED]);

it('does not require remarks when moving to In Progress', function () {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create();
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_SCHEDULED]);

    $response = $this->actingAs($caretaker)->patch("/rescues/{$rescue->id}/update-status", [
        'status' => Rescue::STATUS_IN_PROGRESS,
    ]);

    $response->assertSessionDoesntHaveErrors('remarks');
});

it('redirects to add-animal on a successful rescue completion', function () {
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create();
    $rescue = Rescue::factory()->create(['reportID' => $report->id, 'caretakerID' => $caretaker->id, 'status' => Rescue::STATUS_IN_PROGRESS]);

    $response = $this->actingAs($caretaker)->patch("/rescues/{$rescue->id}/update-status", [
        'status' => Rescue::STATUS_SUCCESS,
        'remarks' => 'Animal safely captured and healthy.',
    ]);

    $response->assertRedirect(route('animal-management.create', ['rescue_id' => $rescue->id]));
});
