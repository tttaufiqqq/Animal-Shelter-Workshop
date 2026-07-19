<?php

use App\Services\Backup\BackupTargetResolver;

/**
 * The app has 5 Laravel connections but only 3 physical databases —
 * reporting+booking share the MariaDB server, shelter+animals share the
 * MySQL server (docs/03-db-architecture.md). If this grouping ever breaks,
 * db:backup would silently dump the same physical database twice under two
 * names instead of collapsing it — these tests pin the grouping so that
 * regresses loudly instead.
 */
it('collapses the 5 Laravel connections into 3 physical database targets', function () {
    $targets = (new BackupTargetResolver())->targets();

    expect($targets)->toHaveCount(3);
});

it('groups reporting and booking together on the MariaDB target', function () {
    $targets = (new BackupTargetResolver())->targets();
    $mariadb = collect($targets)->first(fn ($t) => $t['driver'] === 'mariadb');

    expect($mariadb)->not->toBeNull()
        ->and($mariadb['connections'])->toEqualCanonicalizing(['reporting', 'booking']);
});

it('groups shelter and animals together on the MySQL target', function () {
    $targets = (new BackupTargetResolver())->targets();
    $mysql = collect($targets)->first(fn ($t) => $t['driver'] === 'mysql');

    expect($mysql)->not->toBeNull()
        ->and($mysql['connections'])->toEqualCanonicalizing(['shelter', 'animals']);
});

it('keeps users on its own PostgreSQL target', function () {
    $targets = (new BackupTargetResolver())->targets();
    $pgsql = collect($targets)->first(fn ($t) => $t['driver'] === 'pgsql');

    expect($pgsql)->not->toBeNull()
        ->and($pgsql['connections'])->toBe(['users']);
});
