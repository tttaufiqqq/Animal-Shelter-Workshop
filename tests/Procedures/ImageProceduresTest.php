<?php

use App\Models\Report;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('reporting');
});

afterEach(function () {
    $this->truncate('reporting', ['image', 'rescue', 'report']);
});

function callImageCreate(array $overrides = []): object
{
    $p = array_merge([
        'image_path' => 'reports/photo.jpg', 'report_id' => null, 'animal_id' => null, 'clinic_id' => null,
        'user_id' => null, 'user_name' => null, 'user_email' => null,
    ], $overrides);

    DB::connection('reporting')->statement(
        'CALL sp_image_create(?, ?, ?, ?, ?, ?, ?, @o_image_id, @o_status, @o_message)',
        array_values($p)
    );

    return DB::connection('reporting')->selectOne('SELECT @o_image_id AS image_id, @o_status AS `status`, @o_message AS `message`');
}

it('creates an image attached to a report', function () {
    $report = Report::factory()->create();

    $result = callImageCreate(['report_id' => $report->id]);

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('image', ['id' => $result->image_id, 'reportID' => $report->id], 'reporting');
});

it('rejects an image with no parent (report, animal, or clinic)', function () {
    $result = callImageCreate();

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Image must be associated with a report, animal, or clinic');
});

it('rejects an image with an empty path', function () {
    $report = Report::factory()->create();

    $result = callImageCreate(['report_id' => $report->id, 'image_path' => '']);

    expect($result->status)->toBe('error');
    expect($result->message)->toBe('Image path is required');
});

it('deletes an image and reports its path', function () {
    $report = Report::factory()->create();
    $created = callImageCreate(['report_id' => $report->id, 'image_path' => 'reports/to-delete.jpg']);

    DB::connection('reporting')->statement(
        'CALL sp_image_delete(?, ?, ?, ?, @o_image_path, @o_status, @o_message)',
        [$created->image_id, null, null, null]
    );
    $result = DB::connection('reporting')->selectOne('SELECT @o_image_path AS image_path, @o_status AS `status`, @o_message AS `message`');

    expect($result->status)->toBe('success');
    expect($result->image_path)->toBe('reports/to-delete.jpg');
    $this->assertDatabaseMissing('image', ['id' => $created->image_id], 'reporting');
});

it('rejects a direct insert with no parent at the trigger level', function () {
    expect(fn () => DB::connection('reporting')->table('image')->insert([
        'image_path' => 'x.jpg', 'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'Image must be associated with a report, animal, or clinic');
});

it('rejects a direct insert referencing a nonexistent report at the trigger level', function () {
    expect(fn () => DB::connection('reporting')->table('image')->insert([
        'image_path' => 'x.jpg', 'reportID' => 999999, 'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'Cannot create image: Report does not exist');
});
