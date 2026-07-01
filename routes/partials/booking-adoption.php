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
        Route::get('/create', [BookingAdoptionController::class, 'create'])->name('bookings.create');
        Route::post('/store', [BookingAdoptionController::class, 'store'])->name('bookings.store');
        Route::get('/{booking}', [BookingAdoptionController::class, 'show'])->name('bookings.show');
        Route::patch('/{booking}/cancel', [BookingAdoptionController::class, 'cancel'])->name('bookings.cancel');
        Route::patch('/{booking}/confirm', [BookingAdoptionController::class, 'confirm'])->name('bookings.confirm');
        Route::get('/{id}/modal', [BookingAdoptionController::class, 'showModal'])->name('bookings.show.modal');
        Route::get('/{id}/modal/admin', [BookingAdoptionController::class, 'showModalAdmin'])->name('bookings.show.modal-admin');
    });

    Route::prefix('payment')->group(function () {
        Route::get('/status', [BookingAdoptionController::class, 'paymentStatus'])->name('toyyibpay-status');
        Route::post('/callback', [BookingAdoptionController::class, 'callback'])->name('toyyibpay-callback');
    });
});
