<?php

use App\Models\Report;
use App\Models\Rescue;
use Tests\Concerns\TruncatesDistributedDatabases;

// sp_rescue_assign_caretaker self-commits (see ReportSubmissionTest for why),
// so truncate explicitly rather than relying on transaction rollback.
uses(TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->truncate('reporting', ['rescue', 'report']);
});

it('assigns a caretaker, creates a rescue, and syncs the report status to Assigned', function () {
    $admin = $this->makeAdmin();
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create(['report_status' => Report::STATUS_PENDING]);

    $response = $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", [
        'caretaker_id' => $caretaker->id,
    ]);

    $response->assertSessionHas('success');
    $rescue = Rescue::where('reportID', $report->id)->first();
    expect($rescue)->not->toBeNull()
        ->and($rescue->caretakerID)->toBe($caretaker->id)
        ->and($rescue->status)->toBe(Rescue::STATUS_SCHEDULED);

    expect($report->fresh()->report_status)->toBe(Report::STATUS_ASSIGNED);
});

it('maps a critical description to critical priority', function () {
    $admin = $this->makeAdmin();
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create(['description' => 'Trapped animal - Immediate rescue needed']);

    $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", [
        'caretaker_id' => $caretaker->id,
    ]);

    $rescue = Rescue::where('reportID', $report->id)->first();
    expect($rescue->priority)->toBe(Rescue::PRIORITY_CRITICAL);
});

it('defaults to normal priority for an unrecognized description', function () {
    $admin = $this->makeAdmin();
    $caretaker = $this->makeCaretaker();
    $report = Report::factory()->create(['description' => 'Something not in the known list']);

    $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", [
        'caretaker_id' => $caretaker->id,
    ]);

    $rescue = Rescue::where('reportID', $report->id)->first();
    expect($rescue->priority)->toBe(Rescue::PRIORITY_NORMAL);
});

it('reassigns rather than duplicating the rescue when a caretaker is already assigned', function () {
    $admin = $this->makeAdmin();
    $first = $this->makeCaretaker();
    $second = $this->makeCaretaker();
    $report = Report::factory()->create();

    $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", ['caretaker_id' => $first->id]);
    $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", ['caretaker_id' => $second->id]);

    expect(Rescue::where('reportID', $report->id)->count())->toBe(1);
    expect(Rescue::where('reportID', $report->id)->first()->caretakerID)->toBe($second->id);
});

it('errors when the caretaker does not exist', function () {
    $admin = $this->makeAdmin();
    $report = Report::factory()->create();

    $response = $this->actingAs($admin)->patch("/reports/{$report->id}/assign-caretaker", [
        'caretaker_id' => 999999,
    ]);

    $response->assertSessionHas('error');
    expect(Rescue::where('reportID', $report->id)->exists())->toBeFalse();
});
