<?php

use App\Models\Report;
use Tests\Concerns\FakesCloudinary;

uses(FakesCloudinary::class);

/**
 * Only the "blocked without a location" case is covered here as a browser
 * test. A full successful submission requires uploading a file, which needs
 * a multipart/form-data POST - and this Pest version's in-process HTTP driver
 * (Pest\Browser\Drivers\LaravelHttpServer) only parses
 * application/x-www-form-urlencoded bodies; for multipart requests it passes
 * hardcoded empty arrays for both parameters and files (see its own source,
 * lines ~246-252: `[], // @TODO files...`, `[], // @TODO server variables...`).
 * Confirmed directly, three ways: the DOM state (address/city/state/lat/lng/
 * the attached file) was verified correct via assertScript() right up to the
 * moment of the real click, yet the server always reported every field as
 * missing - a hard, current limitation of the plugin, not an app bug or a
 * test mistake. The successful end-to-end submission (including the file
 * upload and the real POST endpoint) is already covered at the HTTP-client
 * level by tests/Feature/Reporting/ReportSubmissionTest.php.
 *
 * The button that opens the modal is clicked via the explicit CSS attribute
 * selector [onclick="openReportModal()"], not by its visible text ("Submit
 * Stray Animal Report") - that text is duplicated verbatim as the modal's own
 * (initially hidden) <h2> title in modal-header.blade.php, so Pest's
 * text-based click() never resolves to a single element and hangs
 * indefinitely (confirmed directly: it's specifically this one click that
 * hangs - a plain click() on another page, and calling openReportModal()
 * itself via assertScript(), both resolve in a few seconds). An explicit
 * selector sidesteps the ambiguity.
 */
function fakeReportImagePath(): string
{
    $path = storage_path('framework/testing/fixture-report-image.jpg');
    if (! file_exists($path)) {
        $image = imagecreatetruecolor(20, 20);
        imagejpeg($image, $path);
        imagedestroy($image);
    }

    return $path;
}

/**
 * Locator::attach() sends Playwright's setInputFiles with a `localPaths`
 * param, which the protocol rejects unless the connecting client is "local"
 * (confirmed directly: "localPaths are not allowed when the client is not
 * local", every time, regardless of path) - a limitation of how this Pest
 * version's client connects (over a plain websocket to `playwright
 * run-server`, not recognised as a trusted local client), not something
 * fixable from test code. Building a real File object client-side via
 * DataTransfer and assigning it to the input's FileList is the standard
 * browser-automation workaround for exactly this restriction.
 */
function attachFakeImageViaDataTransfer($page, string $inputSelector): void
{
    $base64 = base64_encode(file_get_contents(fakeReportImagePath()));

    $page->assertScript(<<<JS
    function() {
        const bytes = Uint8Array.from(atob('{$base64}'), c => c.charCodeAt(0));
        const file = new File([bytes], 'fixture-report-image.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        const input = document.querySelector('{$inputSelector}');
        input.files = dataTransfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        return input.files.length;
    }
    JS, 1);
}

beforeEach(function () {
    Report::query()->delete();
});

it('blocks submission via native validation when no location was pinned', function () {
    $this->fakeCloudinary();
    $user = $this->makeAdopter();
    $this->actingAs($user);

    $page = visit('/');
    $page->click('[onclick="openReportModal()"]')
        ->assertVisible('#reportModal')
        ->select('description', 'Healthy stray - Needs rescue');
    attachFakeImageViaDataTransfer($page, '#imageInput');
    $page->click('Submit Report');

    // address/city/state are empty and HTML `required`, so the browser's own
    // constraint validation blocks the submit event before form-submit.blade.php's
    // fetch() handler ever runs - confirmed directly (a native "Please fill
    // out this field" tooltip on #addressInput, not the custom JS alert).
    $page->assertScript("document.getElementById('addressInput').validity.valid", false);
    expect(Report::count())->toBe(0);
});
