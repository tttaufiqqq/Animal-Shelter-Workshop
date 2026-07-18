<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CaretakerController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ShelterManagementController as AdminShelterManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin/audit')->name('admin.audit.')->group(function () {
    Route::get('/', [AuditController::class, 'index'])->name('index');
    Route::get('/all', [AuditController::class, 'all'])->name('all');
    Route::get('/authentication', [AuditController::class, 'authentication'])->name('authentication');
    Route::get('/payments', [AuditController::class, 'payments'])->name('payments');
    Route::get('/animals', [AuditController::class, 'animals'])->name('animals');
    Route::get('/rescues', [AuditController::class, 'rescues'])->name('rescues');
    Route::get('/timeline/{correlationId}', [AuditController::class, 'timeline'])->name('timeline');
    Route::get('/export/{category}', [AuditController::class, 'export'])->name('export');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/caretaker')->name('admin.caretaker.')->group(function () {
    Route::get('/', [CaretakerController::class, 'index'])->name('index');
    Route::post('/store', [CaretakerController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/users')->name('admin.users.')->group(function () {
    Route::get('/{userId}/activity', [UserManagementController::class, 'getUserActivity'])->name('activity');
    Route::post('/{userId}/suspend', [UserManagementController::class, 'suspendUser'])->name('suspend');
    Route::post('/{userId}/lock', [UserManagementController::class, 'lockUser'])->name('lock');
    Route::post('/{userId}/unlock', [UserManagementController::class, 'unlockUser'])->name('unlock');
    Route::post('/{userId}/force-password-reset', [UserManagementController::class, 'forcePasswordReset'])->name('force-password-reset');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/shelter-management')->name('admin.shelter-management.')->group(function () {
    Route::get('/', [AdminShelterManagementController::class, 'index'])->name('index');

    Route::post('/slots', [AdminShelterManagementController::class, 'storeSlot'])->name('store-slot');
    Route::get('/slots/{id}/edit', [AdminShelterManagementController::class, 'editSlot'])->name('edit-slot');
    Route::put('/slots/{id}', [AdminShelterManagementController::class, 'updateSlot'])->name('update-slot');
    Route::delete('/slots/{id}', [AdminShelterManagementController::class, 'deleteSlot'])->name('delete-slot');
    Route::get('/slots/{id}/details', [AdminShelterManagementController::class, 'getSlotDetails'])->name('slot-details');

    Route::post('/inventory', [AdminShelterManagementController::class, 'storeInventory'])->name('store-inventory');
    Route::get('/inventory/{id}/details', [AdminShelterManagementController::class, 'getInventoryDetails'])->name('inventory-details');
    Route::put('/inventory/{id}', [AdminShelterManagementController::class, 'updateInventory'])->name('update-inventory');
    Route::delete('/inventory/{id}', [AdminShelterManagementController::class, 'deleteInventory'])->name('delete-inventory');

    Route::get('/animals/{id}/details', [AdminShelterManagementController::class, 'getAnimalDetails'])->name('animal-details');

    Route::post('/sections', [AdminShelterManagementController::class, 'storeSection'])->name('store-section');
    Route::get('/sections/{id}/edit', [AdminShelterManagementController::class, 'editSection'])->name('edit-section');
    Route::put('/sections/{id}', [AdminShelterManagementController::class, 'updateSection'])->name('update-section');
    Route::delete('/sections/{id}', [AdminShelterManagementController::class, 'deleteSection'])->name('delete-section');

    Route::post('/categories', [AdminShelterManagementController::class, 'storeCategory'])->name('store-category');
    Route::get('/categories/{id}/edit', [AdminShelterManagementController::class, 'editCategory'])->name('edit-category');
    Route::put('/categories/{id}', [AdminShelterManagementController::class, 'updateCategory'])->name('update-category');
    Route::delete('/categories/{id}', [AdminShelterManagementController::class, 'deleteCategory'])->name('delete-category');
});
