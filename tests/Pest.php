<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

// RefreshDatabase only refreshes the default connection, which this app
// never queries directly (every model/migration pins its own connection —
// see docs/db-architecture.md). UsesDistributedDatabases wraps all five
// named connections in a transaction instead. Unit tests get no DB trait
// at all, so pure-logic tests (matching algorithm, fee calc) stay fast.
pest()->extend(Tests\TestCase::class)
    ->use(Tests\Concerns\UsesDistributedDatabases::class)
    ->use(Tests\Concerns\SeedsMinimalDomain::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

// A sibling of Feature/Unit, not tests/Feature/Procedures: Pest's uses()->in()
// bindings are additive by directory prefix (Pest\Repositories\TestRepository::make()
// applies every registered path that prefixes a file, it doesn't let a more
// specific path override a broader one). Nesting under Feature would apply
// UsesDistributedDatabases' DatabaseTransactions *in addition to* the manual
// truncation these tests need, not instead of it. Stored procedures issue their
// own START TRANSACTION/COMMIT (see TruncatesDistributedDatabases), so this
// suite gets no DB trait here — each test file opts in via uses() explicitly.
pest()->extend(Tests\TestCase::class)
    ->in('Procedures');

// Pest 4 browser tests (Playwright-backed). visit() dispatches through the
// same booted app's HttpKernel in-process (Pest\Browser\Drivers\LaravelHttpServer),
// so it shares this process's .env.testing connections/transactions exactly
// like Feature tests do - never a separate `php artisan serve` with its own env.
pest()->extend(Tests\TestCase::class)
    ->use(Tests\Concerns\UsesDistributedDatabases::class)
    ->use(Tests\Concerns\SeedsMinimalDomain::class)
    ->in('Browser');

// The plan's own CI caveat, confirmed locally: every page loads 4 real external
// CDNs (Tailwind, Font Awesome, Chart.js, etc.) and every request round-trips
// to real Tailscale-hosted databases, and Playwright's default action timeout
// (5s) isn't reliably enough - observed intermittent timeouts around 5-20s
// that are pure network/CDN variance, not app or test bugs (same action
// passes in ~2-4s most runs). Bumped generously for the whole Browser suite.
if (class_exists(\Pest\Browser\Playwright\Playwright::class)) {
    \Pest\Browser\Playwright\Playwright::setTimeout(30_000);
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
