<?php

it('suspends a public user', function () {
    $admin = $this->makeAdmin();
    $target = $this->makePublicUser();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$target->id}/suspend", [
        'reason' => 'Repeated policy violations',
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    expect($target->fresh()->account_status)->toBe('suspended');
});

it('prevents an admin from suspending their own account', function () {
    $admin = $this->makeAdmin();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$admin->id}/suspend", [
        'reason' => 'test',
    ]);

    $response->assertStatus(403);
    expect($admin->fresh()->account_status)->toBe('active');
});

it('prevents suspending another admin account', function () {
    $admin = $this->makeAdmin();
    $otherAdmin = $this->makeAdmin();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$otherAdmin->id}/suspend", [
        'reason' => 'test',
    ]);

    $response->assertStatus(403);
    expect($otherAdmin->fresh()->account_status)->toBe('active');
});

it('locks a user for the requested duration', function () {
    // Doesn't compare locked_until against PHP's now() — see the documented
    // 'users' connection timezone skew (handoff.md): fn_user_lock computes
    // the deadline in Postgres (UTC), but PHP reads those digits back as
    // Asia/Kuala_Lumpur, so a PHP-side comparison is off by ~8h. A raw
    // Postgres-side comparison sidesteps the mismatch, same as
    // UserViewsTest/UserStatsViewsTest already do.
    $admin = $this->makeAdmin();
    $target = $this->makePublicUser();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$target->id}/lock", [
        'duration' => '24_hours',
        'reason' => 'Suspicious activity',
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    $fresh = $target->fresh();
    expect($fresh->account_status)->toBe('locked')
        ->and($fresh->locked_until)->not->toBeNull();

    $stillLocked = \Illuminate\Support\Facades\DB::connection('users')
        ->selectOne('SELECT NOW() < locked_until AS still_locked, EXTRACT(EPOCH FROM (locked_until - NOW())) / 3600 AS hours_remaining FROM users WHERE id = ?', [$target->id]);

    expect($stillLocked->still_locked)->toBeTrue();
    expect((float) $stillLocked->hours_remaining)->toBeGreaterThan(23)->toBeLessThan(24.1);
});

it('prevents a non-admin from being reachable as an admin action target while self-locking is blocked', function () {
    $admin = $this->makeAdmin();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$admin->id}/lock", [
        'duration' => '1_hour',
        'reason' => 'test',
    ]);

    $response->assertStatus(403);
});

it('unlocks a locked account', function () {
    $admin = $this->makeAdmin();
    $target = $this->makePublicUser(['account_status' => 'locked', 'locked_until' => now()->addDay()]);

    $response = $this->actingAs($admin)->postJson("/admin/users/{$target->id}/unlock");

    $response->assertOk()->assertJson(['success' => true]);
    expect($target->fresh()->account_status)->toBe('active');
});

it('refuses to unlock an account that is neither locked nor suspended', function () {
    $admin = $this->makeAdmin();
    $target = $this->makePublicUser();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$target->id}/unlock");

    $response->assertStatus(400);
});

it('force-resets a users password and flags them for a required change', function () {
    $admin = $this->makeAdmin();
    $target = $this->makePublicUser();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$target->id}/force-password-reset", [
        'password' => 'newSecurePassword123',
        'password_confirmation' => 'newSecurePassword123',
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    expect($target->fresh()->require_password_reset)->toBeTrue();
});

it('prevents resetting an admins password through this method', function () {
    $admin = $this->makeAdmin();
    $otherAdmin = $this->makeAdmin();

    $response = $this->actingAs($admin)->postJson("/admin/users/{$otherAdmin->id}/force-password-reset", [
        'password' => 'newSecurePassword123',
        'password_confirmation' => 'newSecurePassword123',
    ]);

    $response->assertStatus(403);
});
