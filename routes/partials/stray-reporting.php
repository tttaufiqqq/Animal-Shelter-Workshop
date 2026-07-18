<?php

use App\Http\Controllers\StrayReportingManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('reports')->group(function () {
        Route::get('/all', [StrayReportingManagementController::class, 'index'])->name('reports.index');
        Route::post('/', [StrayReportingManagementController::class, 'store'])->name('reports.store');
        Route::get('/{id}', [StrayReportingManagementController::class, 'show'])->name('reports.show');
        Route::delete('/{id}', [StrayReportingManagementController::class, 'destroy'])->name('reports.destroy');
        Route::patch('/{id}/assign-caretaker', [StrayReportingManagementController::class, 'assignCaretaker'])->name('reports.assign-caretaker');
    });

    Route::prefix('rescues')->group(function () {
        Route::get('/', [StrayReportingManagementController::class, 'indexcaretaker'])->name('rescues.index');
        Route::get('/count/new', [StrayReportingManagementController::class, 'getNewRescueCount'])->name('rescues.count.new');
        Route::get('/{id}', [StrayReportingManagementController::class, 'showCaretaker'])->name('rescues.show');
        Route::patch('/{id}/update-status-with-animals', [StrayReportingManagementController::class, 'updateStatusWithAnimals'])->name('rescues.update-status-with-animals');
        Route::patch('/{id}/update-status', [StrayReportingManagementController::class, 'updateStatusCaretaker'])->name('rescues.update-status');
    });
});
