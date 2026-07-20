<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Registration Routes
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Login Routes
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Password Confirmation Routes
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Logout Route
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Stock Breeze scaffolding's PUT /password -> Auth\PasswordController->update(),
// name 'password.update', was dropped from here entirely — it collided with
// web.php's actively-used 'password.update' (the forced-password-change flow,
// wired to resources/views/auth/change-password.blade.php and covered by
// ForcedPasswordChangeTest), and grep confirmed Auth\PasswordController had no
// other reference anywhere in the app: dead scaffolding, never wired to any
// view or test, matching the precedent of removing the unused
// email-verification scaffolding (docs/09-production-hardening.md). Found via
// a real route:cache run, which fails outright on duplicate route names —
// this had never been caught before because route:cache had never run for
// real against this app until now.
