<?php

it('redirects a user flagged for a required password change to the change-password page', function () {
    $user = $this->makeAdopter(['require_password_reset' => true]);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertRedirect(route('password.change'));
});

it('lets a flagged user reach the password change page itself', function () {
    $user = $this->makeAdopter(['require_password_reset' => true]);

    $response = $this->actingAs($user)->get('/password/change');

    $response->assertOk();
});

it('does not redirect a user with no pending password change', function () {
    $user = $this->makeAdopter(['require_password_reset' => false]);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();
});

it('clears the flag once the password is actually changed', function () {
    $user = $this->makeAdopter(['require_password_reset' => true]);

    $response = $this->actingAs($user)->post('/password/change', [
        'current_password' => 'password',
        'password' => 'aBrandNewPassword123',
        'password_confirmation' => 'aBrandNewPassword123',
    ]);

    $response->assertRedirect();
    expect($user->fresh()->require_password_reset)->toBeFalse();
});
