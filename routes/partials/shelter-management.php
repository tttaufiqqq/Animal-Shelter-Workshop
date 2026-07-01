<?php

use App\Http\Controllers\ShelterManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('shelter-management')->middleware('auth')->group(function () {
    Route::get('/slots', [ShelterManagementController::class, 'indexSlot'])->name('shelter-management.index');
    Route::post('/slots', [ShelterManagementController::class, 'storeSlot'])->name('shelter-management.store-slot');
    Route::get('/slots/{id}/edit', [ShelterManagementController::class, 'editSlot'])->name('shelter-management.edit-slot');
    Route::put('/slots/{id}', [ShelterManagementController::class, 'updateSlot'])->name('shelter-management.update-slot');
    Route::delete('/slots/{id}', [ShelterManagementController::class, 'deleteSlot'])->name('shelter-management.delete-slot');
    Route::get('/slots/{id}/details', [ShelterManagementController::class, 'getSlotDetails'])->name('shelter-management.slot-details');

    Route::post('/inventory', [ShelterManagementController::class, 'storeInventory'])->name('shelter-management.store-inventory');
    Route::get('/inventory/{id}/details', [ShelterManagementController::class, 'getInventoryDetails'])->name('shelter-management.inventory-details');
    Route::put('/inventory/{id}', [ShelterManagementController::class, 'updateInventory'])->name('shelter-management.update-inventory');
    Route::delete('/inventory/{id}', [ShelterManagementController::class, 'deleteInventory'])->name('shelter-management.delete-inventory');

    Route::get('/animals/{id}/details', [ShelterManagementController::class, 'getAnimalDetails'])->name('shelter-management.animal-details');

    Route::post('/sections', [ShelterManagementController::class, 'storeSection'])->name('shelter-management.store-section');
    Route::get('/sections/{id}/edit', [ShelterManagementController::class, 'editSection'])->name('shelter-management.edit-section');
    Route::put('/sections/{id}', [ShelterManagementController::class, 'updateSection'])->name('shelter-management.update-section');
    Route::delete('/sections/{id}', [ShelterManagementController::class, 'deleteSection'])->name('shelter-management.delete-section');

    Route::post('/categories', [ShelterManagementController::class, 'storeCategory'])->name('shelter-management.store-category');
    Route::get('/categories/{id}/edit', [ShelterManagementController::class, 'editCategory'])->name('shelter-management.edit-category');
    Route::put('/categories/{id}', [ShelterManagementController::class, 'updateCategory'])->name('shelter-management.update-category');
    Route::delete('/categories/{id}', [ShelterManagementController::class, 'deleteCategory'])->name('shelter-management.delete-category');
});
