<?php

use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('users');
});

afterEach(function () {
    $this->truncate('users', ['audit_logs', 'users']);
});

// The app calls fn_user_* (procedure_wrappers), never the standalone sp_user_*
// functions in user_procedures/ directly — confirmed via grep across app/.
// fn_user_create wraps the TRUE procedure sp_user_create_proc via CALL; the
// plain sp_user_create function in user_procedures/ is an unused duplicate
// (Rule 7 — flagged, not removed this session).

function callUserCreate(array $overrides = []): object
{
    $p = array_merge([
        'name' => 'Jane Doe', 'email' => 'jane@example.test', 'password' => 'hashed',
        'phone_num' => null, 'address' => null, 'city' => null, 'state' => null,
        'audit_user_id' => null, 'audit_user_name' => null, 'audit_user_email' => null, 'audit_user_role' => null,
    ], $overrides);

    return DB::connection('users')->selectOne('SELECT * FROM fn_user_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_values($p));
}

it('creates a user on the happy path', function () {
    $result = callUserCreate();

    expect($result->o_status)->toBe('success');
    $this->assertDatabaseHas('users', ['id' => $result->o_user_id, 'email' => 'jane@example.test'], 'users');
});

it('rejects a duplicate email', function () {
    callUserCreate(['email' => 'dupe@example.test']);

    $result = callUserCreate(['email' => 'dupe@example.test']);

    expect($result->o_status)->toBe('error');
    expect($result->o_message)->toBe('Email already exists');
});

it('updates a user', function () {
    $created = callUserCreate();

    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$created->o_user_id, 'New Name', 'jane@example.test', null, null, null, null, null, null, null, null]
    );

    expect($result->o_status)->toBe('success');
    $this->assertDatabaseHas('users', ['id' => $created->o_user_id, 'name' => 'New Name'], 'users');
});

it('updates a password and clears the require_password_reset flag', function () {
    $created = callUserCreate();
    DB::connection('users')->table('users')->where('id', $created->o_user_id)->update(['require_password_reset' => true]);

    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_update_password(?, ?, ?, ?, ?, ?)',
        [$created->o_user_id, 'new-hashed-password', null, null, null, null]
    );

    expect($result->o_status)->toBe('success');
    $this->assertDatabaseHas('users', [
        'id' => $created->o_user_id, 'password' => 'new-hashed-password', 'require_password_reset' => false,
    ], 'users');
});

// --- fn_user_delete: the audit_logs FK-violation fix ---

it('deletes a user without violating the audit_logs FK constraint', function () {
    // Pins the fix: log_user_changes() used to insert audit_logs.user_id as
    // the just-deleted user's own id, which no longer exists post-DELETE and
    // violated audit_logs_user_id_foreign every time. Now logs NULL on DELETE.
    $created = callUserCreate();

    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_delete(?, ?, ?, ?, ?)',
        [$created->o_user_id, null, null, null, null]
    );

    expect($result->o_status)->toBe('success');
    $this->assertDatabaseMissing('users', ['id' => $created->o_user_id], 'users');
    $this->assertDatabaseHas('audit_logs', [
        'entity_id' => $created->o_user_id, 'action' => 'user_deleted', 'user_id' => null,
    ], 'users');
});

it('returns an error status when deleting a user that does not exist', function () {
    $result = DB::connection('users')->selectOne(
        'SELECT * FROM fn_user_delete(?, ?, ?, ?, ?)',
        [999999, null, null, null, null]
    );

    expect($result->o_status)->toBe('error');
    expect($result->o_message)->toBe('User not found');
});
