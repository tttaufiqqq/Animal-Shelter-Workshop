<?php

use App\Models\Rescue;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('reporting');
});

afterEach(function () {
    $this->truncate('reporting', ['image', 'rescue', 'report']);
});

function callReportCreate(array $overrides = []): object
{
    $p = array_merge([
        'latitude' => 1.234, 'longitude' => 103.5, 'address' => '1 Main St', 'city' => 'KL',
        'state' => 'WP', 'report_status' => null, 'description' => 'Stray dog spotted',
        'user_id' => null, 'user_name' => null, 'user_email' => null,
    ], $overrides);

    DB::connection('reporting')->statement(
        'CALL sp_report_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_report_id, @o_status, @o_message)',
        array_values($p)
    );

    return DB::connection('reporting')->selectOne('SELECT @o_report_id AS report_id, @o_status AS `status`, @o_message AS `message`');
}

it('creates a report and defaults status to Pending', function () {
    $result = callReportCreate();

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('report', ['id' => $result->report_id, 'report_status' => 'Pending'], 'reporting');
});

it('rejects a report with a missing description', function () {
    $result = callReportCreate(['description' => null]);

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Description is required');
});

it('reads a report by id', function () {
    $result = callReportCreate(['address' => '42 Rescue Rd']);

    $rows = DB::connection('reporting')->select('CALL sp_report_read(?)', [$result->report_id]);

    expect($rows[0]->address)->toBe('42 Rescue Rd');
});

it('updates a report status', function () {
    $result = callReportCreate();

    DB::connection('reporting')->statement(
        'CALL sp_report_update_status(?, ?, ?, ?, ?, @o_status, @o_message)',
        [$result->report_id, 'Rejected', null, null, null]
    );
    $updateResult = DB::connection('reporting')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($updateResult->status)->toBe('success');
    $this->assertDatabaseHas('report', ['id' => $result->report_id, 'report_status' => 'Rejected'], 'reporting');
});

it('blocks deleting a report that has an associated rescue', function () {
    $rescue = Rescue::factory()->create();

    DB::connection('reporting')->statement(
        'CALL sp_report_delete(?, ?, ?, ?, @o_has_rescue, @o_status, @o_message)',
        [$rescue->reportID, null, null, null]
    );
    $result = DB::connection('reporting')->selectOne('SELECT @o_has_rescue AS has_rescue, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('error');
    expect((bool) $result->has_rescue)->toBeTrue();
    $this->assertDatabaseHas('report', ['id' => $rescue->reportID], 'reporting');
});

it('deletes a report that has no rescue', function () {
    $created = callReportCreate();

    DB::connection('reporting')->statement(
        'CALL sp_report_delete(?, ?, ?, ?, @o_has_rescue, @o_status, @o_message)',
        [$created->report_id, null, null, null]
    );
    $result = DB::connection('reporting')->selectOne('SELECT @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    $this->assertDatabaseMissing('report', ['id' => $created->report_id], 'reporting');
});
