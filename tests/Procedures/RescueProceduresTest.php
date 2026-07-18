<?php

use App\Models\Report;
use App\Models\Rescue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('reporting');
});

afterEach(function () {
    $this->truncate('reporting', ['image', 'rescue', 'report']);
});

function callAssignCaretaker(int $reportId, int $caretakerId): object
{
    DB::connection('reporting')->statement(
        'CALL sp_rescue_assign_caretaker(?, ?, ?, ?, ?, @o_rescue_id, @o_is_reassignment, @o_old_caretaker_id, @o_status, @o_message)',
        [$reportId, $caretakerId, null, null, null]
    );

    return DB::connection('reporting')->selectOne(
        'SELECT @o_rescue_id AS rescue_id, @o_is_reassignment AS is_reassignment, '.
        '@o_old_caretaker_id AS old_caretaker_id, @o_status AS `status`, @o_message AS `message`'
    );
}

// --- sp_rescue_create / trg_rescue_after_insert ---

it('creates a rescue and auto-syncs the report status to Assigned', function () {
    $report = Report::factory()->create(['report_status' => 'Pending']);

    DB::connection('reporting')->statement(
        'CALL sp_rescue_create(?, ?, ?, ?, ?, ?, @o_rescue_id, @o_status, @o_message)',
        [$report->id, null, 'high', null, null, null]
    );
    $result = DB::connection('reporting')->selectOne('SELECT @o_rescue_id AS rescue_id, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('report', ['id' => $report->id, 'report_status' => 'Assigned'], 'reporting');
});

it('rejects creating a rescue for a report that does not exist', function () {
    DB::connection('reporting')->statement(
        'CALL sp_rescue_create(?, ?, ?, ?, ?, ?, @o_rescue_id, @o_status, @o_message)',
        [999999, null, 'normal', null, null, null]
    );
    $result = DB::connection('reporting')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Report not found');
});

it('rejects inserting a rescue directly for a nonexistent report at the trigger level', function () {
    expect(fn () => DB::connection('reporting')->table('rescue')->insert([
        'reportID' => 999999, 'status' => 'Scheduled', 'priority' => 'normal',
        'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'Report does not exist');
});

// --- sp_rescue_assign_caretaker ---

it('assigns a caretaker to an unassigned report', function () {
    $report = Report::factory()->create();

    $result = callAssignCaretaker($report->id, 501);

    expect($result->status)->toBe('success');
    expect((bool) $result->is_reassignment)->toBeFalse();
    $this->assertDatabaseHas('rescue', ['reportID' => $report->id, 'caretakerID' => 501], 'reporting');
});

it('reassigns a report already assigned to a different caretaker', function () {
    $report = Report::factory()->create();
    callAssignCaretaker($report->id, 501);

    $result = callAssignCaretaker($report->id, 502);

    expect($result->status)->toBe('success');
    expect((bool) $result->is_reassignment)->toBeTrue();
    expect((int) $result->old_caretaker_id)->toBe(501);
    $this->assertDatabaseHas('rescue', ['reportID' => $report->id, 'caretakerID' => 502], 'reporting');
});

it('rejects reassigning a report to the same caretaker it already has', function () {
    $report = Report::factory()->create();
    callAssignCaretaker($report->id, 501);

    $result = callAssignCaretaker($report->id, 501);

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('This report is already assigned to the same caretaker');
});

// --- trg_rescue_before_update ---

it('blocks updating a rescue that is already Success or Failed', function () {
    $rescue = Rescue::factory()->create(['status' => 'Success', 'priority' => 'normal']);

    expect(fn () => $rescue->update(['status' => 'In Progress']))
        ->toThrow(QueryException::class, 'Cannot update completed rescue');
});

it('requires remarks when marking a rescue Success or Failed', function () {
    $rescue = Rescue::factory()->create(['status' => 'Scheduled', 'remarks' => null]);

    expect(fn () => $rescue->update(['status' => 'Success', 'remarks' => null]))
        ->toThrow(QueryException::class, 'Remarks are required');
});
