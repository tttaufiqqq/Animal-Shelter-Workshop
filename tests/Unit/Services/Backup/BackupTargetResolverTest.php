<?php

use App\Services\Backup\BackupTargetResolver;

/**
 * Each of the 5 Laravel connections now lives on its own physical machine —
 * shelter/animals and reporting/booking were split off their former shared
 * hosts on 2026-07-20 (see CLAUDE.md's Server Topology table). The resolver
 * groups by driver+host+port+database, so with 5 distinct hosts that now
 * naturally yields 5 targets, one per connection — these tests pin that so a
 * future re-merge of hosts doesn't silently start collapsing backups again
 * without anyone noticing.
 */
it('keeps all 5 Laravel connections as 5 distinct physical database targets', function () {
    $targets = (new BackupTargetResolver())->targets();

    expect($targets)->toHaveCount(5);
});

it('keeps reporting and booking on separate MariaDB targets', function () {
    $targets = (new BackupTargetResolver())->targets();
    $mariadbConnections = collect($targets)
        ->filter(fn ($t) => $t['driver'] === 'mariadb')
        ->pluck('connections')
        ->flatten()
        ->all();

    expect($mariadbConnections)->toEqualCanonicalizing(['reporting', 'booking']);
});

it('keeps shelter and animals on separate MySQL targets', function () {
    $targets = (new BackupTargetResolver())->targets();
    $mysqlConnections = collect($targets)
        ->filter(fn ($t) => $t['driver'] === 'mysql')
        ->pluck('connections')
        ->flatten()
        ->all();

    expect($mysqlConnections)->toEqualCanonicalizing(['shelter', 'animals']);
});

it('keeps users on its own PostgreSQL target', function () {
    $targets = (new BackupTargetResolver())->targets();
    $pgsql = collect($targets)->first(fn ($t) => $t['driver'] === 'pgsql');

    expect($pgsql)->not->toBeNull()
        ->and($pgsql['connections'])->toBe(['users']);
});
