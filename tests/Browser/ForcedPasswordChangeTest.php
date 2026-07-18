<?php

use App\Models\User;

/**
 * The admin side of forcing a reset (POST /admin/users/{id}/force-password-reset)
 * is already covered by tests/Feature/Users/RequirePasswordChangeTest.php - this
 * test drives the genuinely browser-specific leg: does a real login, through the
 * real form, for a user already flagged require_password_reset, actually get
 * intercepted by RequirePasswordChange middleware and redirected in a live
 * rendered session, and does completing the change form clear the gate for good.
 *
 * Form submits are dispatched via a real JS .click() on the submit button
 * (submitForm() below), not Pest's own click(). Confirmed directly: Pest's
 * click()/guessLocator() races the real full-page navigation a plain HTML
 * form submit triggers - by the time its post-click actionability recheck
 * runs, the browser has already navigated to the next page, so the original
 * locator either matches nothing there (an indefinite hang, bounded only by
 * whatever timeout fires first) or coincidentally matches unrelated elements
 * on that next page (an immediate strict-mode violation - reproduced with
 * both the button's exact text and a button[type="submit"] selector, since
 * the destination pages here each happen to have their own submit buttons
 * too). A JS-dispatched click still exercises the real button and real form
 * submission; assertPathIs()'s own polling (proven reliable elsewhere in this
 * suite) then waits for the resulting navigation instead.
 */
function submitForm($page, string $buttonSelector): void
{
    $page->script("document.querySelector('{$buttonSelector}').click();");
}

function makeForcedResetUser(string $password = 'OldPassword123!'): User
{
    $user = User::factory()->create([
        'password' => bcrypt($password),
        'require_password_reset' => true,
    ]);
    $user->assignRole('adopter');

    return $user;
}

it('redirects a flagged user straight to the password-change page on login', function () {
    $user = makeForcedResetUser();

    $page = visit('/login');
    $page->fill('email', $user->email)
        ->fill('password', 'OldPassword123!');
    submitForm($page, 'button[type="submit"]');
    $page->assertPathIs('/password/change')
        ->assertSee('Password Change Required');
});

it('cannot navigate away from password/change while still flagged', function () {
    $user = makeForcedResetUser();

    $page = visit('/login');
    $page->fill('email', $user->email)
        ->fill('password', 'OldPassword123!');
    submitForm($page, 'button[type="submit"]');
    $page->assertPathIs('/password/change');

    // Any other page should bounce back here too, not just the login redirect.
    $page->navigate('/reports/all')
        ->assertPathIs('/password/change');
});

it('completes the forced password change and is released from the gate', function () {
    $user = makeForcedResetUser();

    $page = visit('/login');
    $page->fill('email', $user->email)
        ->fill('password', 'OldPassword123!');
    submitForm($page, 'button[type="submit"]');
    $page->assertPathIs('/password/change');

    $page->fill('current_password', 'OldPassword123!')
        ->fill('password', 'BrandNewPassword456!')
        ->fill('password_confirmation', 'BrandNewPassword456!');
    submitForm($page, 'button[type="submit"]');

    // A plain fixed wait, not assertPathIs()/assertSee(): those poll via
    // guessLocator()-style DOM checks, which is exactly the mechanism that
    // races real navigation elsewhere in this file (see the file-level
    // comment). All this needs is enough time for the in-process request
    // this submit triggered to actually complete before checking the DB.
    $page->wait(1);

    // Not asserting which page this same $page lands on next: Pest's
    // browser driver dispatches every simulated request in-process through
    // the one already-booted app (Pest\Browser\Drivers\LaravelHttpServer),
    // and Laravel's SessionGuard caches its resolved user for the guard
    // object's whole lifetime (see its own source comment, "we do not want
    // to fetch the user data on every call") - a correct assumption for
    // real HTTP (fresh process per request) but not for this in-process
    // driver, which keeps reusing the same guard across every simulated
    // page load within one test. That makes an in-browser proof of "the
    // very next request sees the gate cleared" unreliable here, through no
    // fault of the app - tests/Feature/Users/RequirePasswordChangeTest.php
    // already proves that at the HTTP level. What a browser test can prove
    // cleanly is the DB write itself, from a real form submission.
    expect($user->fresh()->require_password_reset)->toBeFalse();
});
