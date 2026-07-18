<?php

use App\Models\Report;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\FakesCloudinary;
use Tests\Concerns\TruncatesDistributedDatabases;

// sp_report_create/sp_image_create self-commit (START TRANSACTION...COMMIT
// inside the procedure — see Phase 2 finding in handoff), which commits
// UsesDistributedDatabases' wrapping transaction early and makes its rollback
// a no-op. Truncate explicitly before each test instead of relying on it.
uses(FakesCloudinary::class, TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->truncate('reporting', ['image', 'rescue', 'report']);
});

function reportPayload(array $overrides = []): array
{
    return array_merge([
        'latitude' => 3.1390,
        'longitude' => 101.6869,
        'address' => '123 Jalan Test',
        'city' => 'Kuala Lumpur',
        'state' => 'Selangor',
        'description' => 'Sick animal - Needs medical attention',
        'images' => [UploadedFile::fake()->image('stray.jpg')],
    ], $overrides);
}

it('rejects a report with no location selected', function () {
    $this->fakeCloudinary();
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->post('/reports', reportPayload(['latitude' => null, 'longitude' => null]));

    $response->assertSessionHasErrors(['latitude', 'longitude']);
    expect(Report::count())->toBe(0);
});

it('rejects a report with no images', function () {
    $this->fakeCloudinary();
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->post('/reports', reportPayload(['images' => []]));

    $response->assertSessionHasErrors('images');
    expect(Report::count())->toBe(0);
});

it('creates a Pending report and an image row per uploaded file', function () {
    $this->fakeCloudinary();
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->post('/reports', reportPayload([
        'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
    ]));

    $response->assertSessionHas('success');

    $report = Report::first();
    expect($report)->not->toBeNull()
        ->and($report->report_status)->toBe(Report::STATUS_PENDING)
        ->and(Image::where('reportID', $report->id)->count())->toBe(2);
});

it('returns json for an ajax submission', function () {
    $this->fakeCloudinary();
    $user = $this->makeAdopter();

    $response = $this->actingAs($user)->postJson('/reports', reportPayload());

    $response->assertOk()->assertJson(['success' => true]);
});
