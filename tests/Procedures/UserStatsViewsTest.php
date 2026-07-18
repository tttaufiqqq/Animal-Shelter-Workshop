<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('users');
});

afterEach(function () {
    $this->truncate('users', ['audit_logs', 'users']);
});

function fetchAccountStats(): object
{
    return DB::connection('users')->selectOne('SELECT * FROM v_user_account_stats');
}

it('stays stale after a new user is created until refresh_all_taufiq_stats() is called', function () {
    // v_user_account_stats is a MATERIALIZED view — a snapshot, not a live
    // query. Staleness is deliberate (cheap dashboard reads), so this pins
    // the intended behaviour rather than treating it as a bug.
    // Refresh first to get a true baseline — other test files truncate
    // 'users' without refreshing, so the snapshot can otherwise be stale
    // from a previous file's data before this test even starts.
    DB::connection('users')->selectOne('SELECT refresh_all_taufiq_stats()');
    $before = fetchAccountStats();

    User::factory()->create();

    $stale = fetchAccountStats();
    expect((int) $stale->total_users)->toBe((int) $before->total_users);

    DB::connection('users')->selectOne('SELECT refresh_all_taufiq_stats()');

    $fresh = fetchAccountStats();
    expect((int) $fresh->total_users)->toBe((int) $before->total_users + 1);
});

it('counts suspended and active users separately in the refreshed snapshot', function () {
    User::factory()->create(['account_status' => 'active']);
    User::factory()->create(['account_status' => 'suspended']);

    DB::connection('users')->selectOne('SELECT refresh_all_taufiq_stats()');
    $stats = fetchAccountStats();

    expect((int) $stats->active_users)->toBeGreaterThanOrEqual(1);
    expect((int) $stats->suspended_users)->toBeGreaterThanOrEqual(1);
});

it('excludes an expired lock from the locked_users count in the refreshed snapshot', function () {
    $user = User::factory()->create(['account_status' => 'locked']);
    DB::connection('users')->statement(
        "UPDATE users SET locked_until = NOW() - INTERVAL '1 minute' WHERE id = ?",
        [$user->id]
    );

    DB::connection('users')->selectOne('SELECT refresh_all_taufiq_stats()');
    $stats = fetchAccountStats();

    $this->assertDatabaseHas('users', ['id' => $user->id, 'account_status' => 'locked'], 'users');
    expect((int) $stats->locked_users)->toBe(0);
});
