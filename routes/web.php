<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrayReportingManagementController;
use App\Http\Controllers\AnimalManagementController;
use App\Http\Controllers\RescueMapController;
use App\Services\DatabaseConnectionChecker;
use Illuminate\Support\Facades\Route;

Route::get('/rescue-map', [RescueMapController::class, 'index'])->name('rescue.map');

// CSRF Token Refresh Route
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');

// Real-time database status API endpoint
Route::get('/api/database-status', function (DatabaseConnectionChecker $checker) {
    $status = $checker->checkAll(true); // Use cache
    $connected = array_filter($status, fn($db) => $db['connected']);
    $disconnected = array_filter($status, fn($db) => !$db['connected']);

    return response()->json([
        'status' => $status,
        'connected' => array_values($connected),
        'disconnected' => array_values($disconnected),
        'allOnline' => count($disconnected) === 0,
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.database.status');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'role:admin'])->name('dashboard');

Route::get('/', [StrayReportingManagementController::class, 'indexUser'])->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updateProfilePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Forced password change routes (after admin reset)
    Route::get('/password/change', [ProfileController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [ProfileController::class, 'updatePassword'])->name('password.update');
});

Route::get('/about', function () {
    return view('contact');
})->name('contact');

Route::middleware('auth')->group(function () {
    Route::post('/animal/profile/store/{animal}', [AnimalManagementController::class, 'storeOrUpdate'])->name('animal.profile.store');
    Route::post('/adopter/profile/store', [ProfileController::class, 'storeOrUpdate'])->name('adopter.profile.store');
    Route::get('/animal-matches', [AnimalManagementController::class, 'getMatches'])->name('animal.matches');
});

require __DIR__.'/partials/stray-reporting.php';
require __DIR__.'/partials/animal-management.php';
require __DIR__.'/partials/shelter-management.php';
require __DIR__.'/partials/booking-adoption.php';
require __DIR__.'/partials/admin.php';
require __DIR__.'/auth.php';
