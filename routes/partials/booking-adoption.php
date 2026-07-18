<?php

use App\Http\Controllers\BookingAdoptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/booking:main', [BookingAdoptionController::class, 'index'])->name('booking:main');
    Route::post('/adoption/book', [BookingAdoptionController::class, 'storeBooking'])->name('adoption.book');

    Route::prefix('visit-list')->group(function () {
        Route::get('/', [BookingAdoptionController::class, 'indexList'])->name('visit.list');
        Route::post('/add/{animal}', [BookingAdoptionController::class, 'addList'])->name('visit.list.add');
        Route::delete('/remove/{animalId}', [BookingAdoptionController::class, 'removeList'])->name('visit.list.remove');
        Route::post('/confirm', [BookingAdoptionController::class, 'confirmAppointment'])->name('visit.list.confirm');
    });

    Route::prefix('bookings')->group(function () {
        Route::get('/index', [BookingAdoptionController::class, 'index'])->name('bookings.index');
        Route::get('/all', [BookingAdoptionController::class, 'indexAdmin'])->name('bookings.index-admin');
        Route::patch('/{booking}/cancel', [BookingAdoptionController::class, 'cancel'])->name('bookings.cancel');
        Route::patch('/{booking}/confirm', [BookingAdoptionController::class, 'confirm'])->name('bookings.confirm');
    });

    Route::get('/payment/status', [BookingAdoptionController::class, 'paymentStatus'])->name('toyyibpay-status');
});

// ToyyibPay calls this server-to-server with no browser session — it cannot carry
// an auth cookie or a CSRF token, so it must sit outside both (see bootstrap/app.php
// for the matching CSRF exemption). Gateway authenticity is verified separately via
// isGatewayConfirmed() rather than relying on auth/CSRF.
Route::post('/payment/callback', [BookingAdoptionController::class, 'callback'])->name('toyyibpay-callback');
