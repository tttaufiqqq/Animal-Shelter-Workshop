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

it('suspends a user', function () {
    $user = User::factory()->create();

    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_suspend(?, ?, ?, ?, ?, ?)',
        [$user->id, 'Policy violation', null, null, null, null]
    );

    expect($result->o_status)->toBe('success');
    $this->assertDatabaseHas('users', ['id' => $user->id, 'account_status' => 'suspended'], 'users');
});

it('locks a user until a computed expiry time', function () {
    $user = User::factory()->create();

    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_lock(?, ?, ?, ?, ?, ?, ?)',
        [$user->id, 30, 'Too many failed logins', null, null, null, null]
    );

    expect($result->o_status)->toBe('success');
    expect($result->o_locked_until)->not->toBeNull();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'account_status' => 'locked'], 'users');
});

it('unlocks a user and resets failed login tracking', function () {
    $user = User::factory()->create([
        'account_status' => 'locked', 'locked_until' => now()->addHour(),
        'failed_login_attempts' => 5,
    ]);

    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_unlock(?, ?, ?, ?, ?)',
        [$user->id, null, null, null, null]
    );

    expect($result->o_status)->toBe('success');
    $this->assertDatabaseHas('users', [
        'id' => $user->id, 'account_status' => 'active', 'locked_until' => null, 'failed_login_attempts' => 0,
    ], 'users');
});

it('forces a password reset', function () {
    $user = User::factory()->create(['require_password_reset' => false]);

    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_force_password_reset(?, ?, ?, ?, ?)',
        [$user->id, null, null, null, null]
    );

    expect($result->o_status)->toBe('success');
    $this->assertDatabaseHas('users', ['id' => $user->id, 'require_password_reset' => true], 'users');
});

it('returns an error status for a security action on a user that does not exist', function () {
    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_suspend(?, ?, ?, ?, ?, ?)',
        [999999, 'reason', null, null, null, null]
    );

    expect($result->o_status)->toBe('error');
    expect($result->o_message)->toBe('User not found');
});
