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

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/all', [StrayReportingManagementController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [StrayReportingManagementController::class, 'create'])->name('reports.create');
    Route::post('/reports', [StrayReportingManagementController::class, 'store'])->name('reports.store');
    Route::get('/reports/{id}', [StrayReportingManagementController::class, 'show'])->name('reports.show');
    Route::get('/reports/{id}/edit', [StrayReportingManagementController::class, 'edit'])->name('reports.edit');
    Route::delete('/reports/{id}', [StrayReportingManagementController::class, 'destroy'])->name('reports.destroy');
    Route::patch('/reports/{id}/assign-caretaker', [StrayReportingManagementController::class, 'assignCaretaker'])->name('reports.assign-caretaker');

    Route::get('/rescues', [StrayReportingManagementController::class, 'indexcaretaker'])->name('rescues.index');
    Route::patch('/rescues/{id}/update-status', [StrayReportingManagementController::class, 'updateStatusCaretaker'])->name('rescues.update-status');
    Route::get('/rescues/{id}', [StrayReportingManagementController::class, 'showCaretaker'])->name('rescues.show');
});


//Animal-Management
Route::middleware('auth')->group(function () {
    Route::get('/animal:main', [AnimalManagementController::class, 'index'])->name('animal:main');
});

Route::get('/animal', [AnimalManagementController::class, 'index'])->name('animal-management.index');
Route::get('/animal/create/rescue-{rescue_id?}', [AnimalManagementController::class, 'create'])->name('animal-management.create');
Route::post('/animal/store', [AnimalManagementController::class, 'store'])->name('animal-management.store');
Route::get('/animal/{animal}', [AnimalManagementController::class, 'show'])->name('animal-management.show');
Route::get('/animal/{animal}/edit', [AnimalManagementController::class, 'edit'])->name('animal-management.edit');
Route::put('/animal/{animal}', [AnimalManagementController::class, 'update'])->name('animal-management.update');
Route::delete('/animal/{animal}', [AnimalManagementController::class, 'destroy'])->name('animal-management.destroy');

Route::get('/clinic-vet', [AnimalManagementController::class, 'indexClinic'])->name('animal-management.clinic-index');
Route::get('/medical-create', [AnimalManagementController::class, 'indexClinic'])->name('medical-records.create');
Route::get('/vaccination-create', [AnimalManagementController::class, 'indexClinic'])->name('vaccination-records.create');


Route::post('/medical-records/store', [AnimalManagementController::class, 'storeMedical'])->name('medical-records.store');
Route::post('/vaccination-records/store', [AnimalManagementController::class, 'storeVaccination'])->name('vaccination-records.store');
Route::post('/animals/{animal}/assign-slot', [AnimalManagementController::class, 'assignSlot'])->name('animals.assignSlot');


Route::get('/create-vet', [AnimalManagementController::class, 'createVet'])->name('animal-management.create.vet');
Route::post('/store-clinics', [AnimalManagementController::class, 'storeClinic'])->name('animal-management.store-clinics');
Route::post('/store-vets', [AnimalManagementController::class, 'storeVet'])->name('animal-management.store-vets');

Route::get('/animal', [AnimalManagementController::class, 'index'])->name('animal-management.index');

Route::get('/clinics/{id}/edit', [AnimalManagementController::class, 'editClinic'])->name('clinics.edit');
Route::put('/clinics/{id}', [AnimalManagementController::class, 'updateClinic'])->name('clinics.update');
Route::delete('/clinics/{id}', [AnimalManagementController::class, 'destroyClinic'])->name('clinics.destroy');

Route::get('/vets/{id}/edit', [AnimalManagementController::class, 'editVet'])->name('vets.edit');
Route::put('/vets/{id}', [AnimalManagementController::class, 'updateVet'])->name('vets.update');
Route::delete('/vets/{id}', [AnimalManagementController::class, 'destroyVet'])->name('vets.destroy');



//Shelter-Management
  Route::get('/slots', [ShelterManagementController::class, 'indexSlot'])->name('shelter-management.index');
    Route::post('/slots-store', [ShelterManagementController::class, 'storeSlot'])->name('shelter-management.store-slot');
    Route::put('/shelter-management/slots/{id}', [ShelterManagementController::class, 'updateSlot'])->name('shelter-management.update-slot');
    Route::delete('/slots/{id}', [ShelterManagementController::class, 'deleteSlot'])->name('shelter-management.delete-slot');
    Route::get('/shelter-management/slots/{id}/edit', [ShelterManagementController::class, 'editSlot'])->name('shelter-management.edit-slot');

//Booking-Adoption
Route::middleware('auth')->group(function () {
    Route::get('/booking:main', [BookingAdoptionController::class, 'home'])->name('booking:main');
});

require __DIR__.'/auth.php';
