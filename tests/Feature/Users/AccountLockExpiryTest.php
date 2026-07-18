<?php

// Pins User::isLocked() as a deliberate but surprising side effect: it's
// read-shaped (isLocked()) but writes to the database when the lock has
// expired, auto-clearing account_status/locked_until/lock_reason. Worth
// encoding explicitly (Rule 9) since a future refactor could easily "fix"
// this into a pure read and silently break the auto-unlock behavior every
// caller of isLocked()/canLogin() currently relies on.

it('auto-unlocks and persists the change when a lock has expired', function () {
    $user = $this->makeAdopter([
        'account_status' => 'locked',
        'locked_until' => now()->subMinute(),
        'lock_reason' => 'Too many failed attempts',
    ]);

    expect($user->isLocked())->toBeFalse();

    $fresh = $user->fresh();
    expect($fresh->account_status)->toBe('active')
        ->and($fresh->locked_until)->toBeNull()
        ->and($fresh->lock_reason)->toBeNull();
});

it('reports locked and makes no write while the lock is still active', function () {
    $user = $this->makeAdopter([
        'account_status' => 'locked',
        'locked_until' => now()->addHour(),
        'lock_reason' => 'Too many failed attempts',
    ]);

    expect($user->isLocked())->toBeTrue();
    expect($user->fresh()->account_status)->toBe('locked');
});
