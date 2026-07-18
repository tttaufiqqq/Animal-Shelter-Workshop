<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    // This app's registration form requires more than Breeze's stock
    // name/email/password - city/state/address/phoneNum are all required
    // (see RegisteredUserController::store()'s validation rules).
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'city' => 'Kuala Lumpur',
        'state' => 'Selangor',
        'address' => '123 Jalan Test',
        'phoneNum' => '0123456789',
    ]);

    $this->assertAuthenticated();
    // Non-admin users land on welcome, never /dashboard (role:admin-only) -
    // see AuthenticatedSessionController::store()'s role-based redirect.
    $response->assertRedirect(route('welcome', absolute: false));
});
