<?php

use Illuminate\Support\Facades\Route;

it('denies a non-admin user access to every admin/* route', function () {
    $user = $this->makePublicUser();
    $failures = [];

    foreach (Route::getRoutes() as $route) {
        if (! str_starts_with($route->uri(), 'admin/')) {
            continue;
        }

        $method = collect($route->methods())->first(fn ($m) => $m !== 'HEAD');
        $uri = '/' . preg_replace('/\{[^}]+\}/', '1', $route->uri());

        $response = $this->actingAs($user)->call($method, $uri);
        $status = $response->baseResponse->getStatusCode();

        if ($status !== 403) {
            $failures[] = "{$method} {$uri} -> {$status} (expected 403)";
        }
    }

    expect($failures)->toBe([]);
});

it('allows an admin to reach admin routes', function () {
    $admin = $this->makeAdmin();

    $this->actingAs($admin)->get('/admin/caretaker')->assertOk();
    $this->actingAs($admin)->get('/admin/shelter-management')->assertOk();
});

it('prevents a non-admin from suspending another user', function () {
    $attacker = $this->makePublicUser();
    $target = $this->makeAdopter();

    $response = $this->actingAs($attacker)->postJson("/admin/users/{$target->id}/suspend", [
        'reason' => 'malicious suspension attempt',
    ]);

    $response->assertForbidden();
    expect($target->fresh()->account_status)->toBe('active');
});

it('prevents privilege escalation via force-password-reset', function () {
    $attacker = $this->makePublicUser();
    $target = $this->makeAdopter();
    $originalPasswordHash = $target->password;

    $response = $this->actingAs($attacker)->postJson("/admin/users/{$target->id}/force-password-reset", [
        'password' => 'new-attacker-password',
        'password_confirmation' => 'new-attacker-password',
    ]);

    $response->assertForbidden();

    $target->refresh();
    expect($target->password)->toBe($originalPasswordHash)
        ->and($target->require_password_reset)->toBeFalse();
});
