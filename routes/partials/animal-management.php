<?php

use App\Http\Controllers\AnimalManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/animal:main', [AnimalManagementController::class, 'index'])->name('animal:main');
    Route::get('/animal', [AnimalManagementController::class, 'index'])->name('animal-management.index');
    Route::get('/animal/create/rescue-{rescue_id?}', [AnimalManagementController::class, 'create'])->name('animal-management.create');
    Route::post('/animal/store', [AnimalManagementController::class, 'store'])->name('animal-management.store');
    Route::get('/animal/{animal}', [AnimalManagementController::class, 'show'])->name('animal-management.show');
    Route::put('/animal/{animal}', [AnimalManagementController::class, 'update'])->name('animal-management.update');
    Route::delete('/animal/{animal}', [AnimalManagementController::class, 'destroy'])->name('animal-management.destroy');
    Route::post('/animals/{animal}/assign-slot', [AnimalManagementController::class, 'assignSlot'])->name('animals.assignSlot');

    Route::get('/clinic-vet', [AnimalManagementController::class, 'indexClinic'])->name('animal-management.clinic-index');
    Route::post('/store-clinics', [AnimalManagementController::class, 'storeClinic'])->name('animal-management.store-clinics');
    Route::post('/store-vets', [AnimalManagementController::class, 'storeVet'])->name('animal-management.store-vets');
    Route::get('/clinics/{id}/edit', [AnimalManagementController::class, 'editClinic'])->name('clinics.edit');
    Route::put('/clinics/{id}', [AnimalManagementController::class, 'updateClinic'])->name('clinics.update');
    Route::delete('/clinics/{id}', [AnimalManagementController::class, 'destroyClinic'])->name('clinics.destroy');
    Route::get('/vets/{id}/edit', [AnimalManagementController::class, 'editVet'])->name('vets.edit');
    Route::put('/vets/{id}', [AnimalManagementController::class, 'updateVet'])->name('vets.update');
    Route::delete('/vets/{id}', [AnimalManagementController::class, 'destroyVet'])->name('vets.destroy');

    Route::get('/medical-create', [AnimalManagementController::class, 'indexClinic'])->name('medical-records.create');
    Route::get('/vaccination-create', [AnimalManagementController::class, 'indexClinic'])->name('vaccination-records.create');
    Route::post('/medical-records/store', [AnimalManagementController::class, 'storeMedical'])->name('medical-records.store');
    Route::post('/vaccination-records/store', [AnimalManagementController::class, 'storeVaccination'])->name('vaccination-records.store');
});
