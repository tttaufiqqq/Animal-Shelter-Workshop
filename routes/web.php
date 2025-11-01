<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrayReportingManagementController;
use App\Http\Controllers\AnimalManagementController;
use App\Http\Controllers\ShelterManagementController;
use App\Http\Controllers\BookingAdoptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/about', function () {
    return view('contact');
})->name('contact');


//Stray-Reporting
Route::middleware('auth')->group(function () {
    Route::get('/report:main', [StrayReportingManagementController::class, 'home'])->name('report:main');
});

//Animal-Management
Route::middleware('auth')->group(function () {
    Route::get('/animal:main', [AnimalManagementController::class, 'home'])->name('animal:main');
});

//Shelter-Management
Route::middleware('auth')->group(function () {
    Route::get('/slot:main', [ShelterManagementController::class, 'home'])->name('slot:main');
});

//Booking-Adoption
Route::middleware('auth')->group(function () {
    Route::get('/booking:main', [BookingAdoptionController::class, 'home'])->name('booking:main');
});

require __DIR__.'/auth.php';
