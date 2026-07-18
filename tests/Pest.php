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
