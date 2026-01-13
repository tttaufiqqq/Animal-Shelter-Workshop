<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrayReportingManagementController;
use App\Http\Controllers\AnimalManagementController;
use App\Http\Controllers\ShelterManagementController;
use App\Http\Controllers\BookingAdoptionController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Http\Controllers\RescueMapController;
use App\Services\DatabaseConnectionChecker;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CaretakerController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ShelterManagementController as AdminShelterManagementController;

Route::get('/rescue-map', [RescueMapController::class, 'index'])->name('rescue.map');
Route::get('/api/rescue-clusters', [RescueMapController::class, 'getClusterData'])->name('rescue.clusters');

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


Route::post('/animal/profile/store/{animal}', [AnimalManagementController::class, 'storeOrUpdate'])->name('animal.profile.store');
Route::post('/adopter/profile/store', [ProfileController::class, 'storeOrUpdate'])->name('adopter.profile.store');

Route::middleware('auth')->group(function () {
    Route::get('/animal-matches', [AnimalManagementController::class, 'getMatches'])->name('animal.matches');
});

//Stray-Reporting
Route::middleware('auth')->group(function () {
    // Report Management Routes
    Route::prefix('reports')->group(function () {
        Route::get('/all', [StrayReportingManagementController::class, 'index'])->name('reports.index');
        Route::get('/create', [StrayReportingManagementController::class, 'create'])->name('reports.create');
        Route::post('/', [StrayReportingManagementController::class, 'store'])->name('reports.store');
        Route::get('/{id}', [StrayReportingManagementController::class, 'show'])->name('reports.show');
        Route::get('/{id}/edit', [StrayReportingManagementController::class, 'edit'])->name('reports.edit');
        Route::delete('/{id}', [StrayReportingManagementController::class, 'destroy'])->name('reports.destroy');
        Route::patch('/{id}/assign-caretaker', [StrayReportingManagementController::class, 'assignCaretaker'])->name('reports.assign-caretaker');
    });

    // Rescue Management Routes
    Route::prefix('rescues')->group(function () {
        Route::get('/', [StrayReportingManagementController::class, 'indexcaretaker'])->name('rescues.index');
        Route::get('/count/new', [StrayReportingManagementController::class, 'getNewRescueCount'])->name('rescues.count.new');
        Route::get('/{id}', [StrayReportingManagementController::class, 'showCaretaker'])->name('rescues.show');
        Route::patch('/{id}/update-status-with-animals', [StrayReportingManagementController::class, 'updateStatusWithAnimals'])->name('rescues.update-status-with-animals');
        Route::patch('/{id}/update-status', [StrayReportingManagementController::class, 'updateStatusCaretaker'])->name('rescues.update-status');
    });
});

//Animal-Management
Route::middleware('auth')->group(function () {
    // Animal CRUD routes
    Route::get('/animal:main', [AnimalManagementController::class, 'index'])->name('animal:main');
    Route::get('/animal', [AnimalManagementController::class, 'index'])->name('animal-management.index');
    Route::get('/animal/create/rescue-{rescue_id?}', [AnimalManagementController::class, 'create'])->name('animal-management.create');
    Route::post('/animal/store', [AnimalManagementController::class, 'store'])->name('animal-management.store');
    Route::get('/animal/{animal}', [AnimalManagementController::class, 'show'])->name('animal-management.show');
    Route::get('/animal/{animal}/edit', [AnimalManagementController::class, 'edit'])->name('animal-management.edit');
    Route::put('/animal/{animal}', [AnimalManagementController::class, 'update'])->name('animal-management.update');
    Route::delete('/animal/{animal}', [AnimalManagementController::class, 'destroy'])->name('animal-management.destroy');
    Route::post('/animals/{animal}/assign-slot', [AnimalManagementController::class, 'assignSlot'])->name('animals.assignSlot');

    // Clinic and Vet management routes
    Route::get('/clinic-vet', [AnimalManagementController::class, 'indexClinic'])->name('animal-management.clinic-index');
    Route::get('/create-vet', [AnimalManagementController::class, 'createVet'])->name('animal-management.create.vet');
    Route::post('/store-clinics', [AnimalManagementController::class, 'storeClinic'])->name('animal-management.store-clinics');
    Route::post('/store-vets', [AnimalManagementController::class, 'storeVet'])->name('animal-management.store-vets');
    Route::get('/clinics/{id}/edit', [AnimalManagementController::class, 'editClinic'])->name('clinics.edit');
    Route::put('/clinics/{id}', [AnimalManagementController::class, 'updateClinic'])->name('clinics.update');
    Route::delete('/clinics/{id}', [AnimalManagementController::class, 'destroyClinic'])->name('clinics.destroy');
    Route::get('/vets/{id}/edit', [AnimalManagementController::class, 'editVet'])->name('vets.edit');
    Route::put('/vets/{id}', [AnimalManagementController::class, 'updateVet'])->name('vets.update');
    Route::delete('/vets/{id}', [AnimalManagementController::class, 'destroyVet'])->name('vets.destroy');

    // Medical and Vaccination records routes
    Route::get('/medical-create', [AnimalManagementController::class, 'indexClinic'])->name('medical-records.create');
    Route::get('/vaccination-create', [AnimalManagementController::class, 'indexClinic'])->name('vaccination-records.create');
    Route::post('/medical-records/store', [AnimalManagementController::class, 'storeMedical'])->name('medical-records.store');
    Route::post('/vaccination-records/store', [AnimalManagementController::class, 'storeVaccination'])->name('vaccination-records.store');
});

//Shelter-Management
Route::prefix('shelter-management')->middleware('auth')->group(function () {
    // Slot Routes
    Route::get('/slots', [ShelterManagementController::class, 'indexSlot'])->name('shelter-management.index');
    Route::post('/slots', [ShelterManagementController::class, 'storeSlot'])->name('shelter-management.store-slot');
    Route::get('/slots/{id}/edit', [ShelterManagementController::class, 'editSlot'])->name('shelter-management.edit-slot');
    Route::put('/slots/{id}', [ShelterManagementController::class, 'updateSlot'])->name('shelter-management.update-slot');
    Route::delete('/slots/{id}', [ShelterManagementController::class, 'deleteSlot'])->name('shelter-management.delete-slot');
    Route::get('/slots/{id}/details', [ShelterManagementController::class, 'getSlotDetails'])->name('shelter-management.slot-details');

    // Inventory Routes
    Route::post('/inventory', [ShelterManagementController::class, 'storeInventory'])->name('shelter-management.store-inventory');
    Route::get('/inventory/{id}/details', [ShelterManagementController::class, 'getInventoryDetails'])->name('shelter-management.inventory-details');
    Route::put('/inventory/{id}', [ShelterManagementController::class, 'updateInventory'])->name('shelter-management.update-inventory');
    Route::delete('/inventory/{id}', [ShelterManagementController::class, 'deleteInventory'])->name('shelter-management.delete-inventory');

    // Animal Details Route
    Route::get('/animals/{id}/details', [ShelterManagementController::class, 'getAnimalDetails'])->name('shelter-management.animal-details');

    // Section Routes
    Route::post('/sections', [ShelterManagementController::class, 'storeSection'])->name('shelter-management.store-section');
    Route::get('/sections/{id}/edit', [ShelterManagementController::class, 'editSection'])->name('shelter-management.edit-section');
    Route::put('/sections/{id}', [ShelterManagementController::class, 'updateSection'])->name('shelter-management.update-section');
    Route::delete('/sections/{id}', [ShelterManagementController::class, 'deleteSection'])->name('shelter-management.delete-section');

    // Category Routes
    Route::post('/categories', [ShelterManagementController::class, 'storeCategory'])->name('shelter-management.store-category');
    Route::get('/categories/{id}/edit', [ShelterManagementController::class, 'editCategory'])->name('shelter-management.edit-category');
    Route::put('/categories/{id}', [ShelterManagementController::class, 'updateCategory'])->name('shelter-management.update-category');
    Route::delete('/categories/{id}', [ShelterManagementController::class, 'deleteCategory'])->name('shelter-management.delete-category');
});


//Booking-Adoption
Route::middleware('auth')->group(function () {
    // Main Booking Routes
    Route::get('/booking:main', [BookingAdoptionController::class, 'index'])->name('booking:main');
    Route::post('/adoption/book', [BookingAdoptionController::class, 'storeBooking'])->name('adoption.book');

    // Visit List Routes
    Route::prefix('visit-list')->group(function () {
        Route::get('/', [BookingAdoptionController::class, 'indexList'])->name('visit.list');
        Route::post('/add/{animal}', [BookingAdoptionController::class, 'addList'])->name('visit.list.add');
        Route::delete('/remove/{animalId}', [BookingAdoptionController::class, 'removeList'])->name('visit.list.remove');
        Route::post('/confirm', [BookingAdoptionController::class, 'confirmAppointment'])->name('visit.list.confirm');
    });

    // Booking Management Routes
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

    // Payment Routes
    Route::prefix('payment')->group(function () {
        Route::get('/status', [BookingAdoptionController::class, 'paymentStatus'])->name('toyyibpay-status');
        Route::post('/callback', [BookingAdoptionController::class, 'callback'])->name('toyyibpay-callback');
    });
});

// Admin Audit Trail Routes
Route::middleware(['auth'])->prefix('admin/audit')->name('admin.audit.')->group(function () {
    Route::get('/', [AuditController::class, 'index'])->name('index');
    Route::get('/all', [AuditController::class, 'all'])->name('all');
    Route::get('/authentication', [AuditController::class, 'authentication'])->name('authentication');
    Route::get('/payments', [AuditController::class, 'payments'])->name('payments');
    Route::get('/animals', [AuditController::class, 'animals'])->name('animals');
    Route::get('/rescues', [AuditController::class, 'rescues'])->name('rescues');
    Route::get('/timeline/{correlationId}', [AuditController::class, 'timeline'])->name('timeline');
    Route::get('/export/{category}', [AuditController::class, 'export'])->name('export');
});

// Admin Caretaker Management Routes
Route::middleware(['auth'])->prefix('admin/caretaker')->name('admin.caretaker.')->group(function () {
    Route::get('/', [CaretakerController::class, 'index'])->name('index');
    Route::post('/store', [CaretakerController::class, 'store'])->name('store');
});

// Admin User Management Routes
Route::middleware(['auth'])->prefix('admin/users')->name('admin.users.')->group(function () {
    Route::get('/{userId}/activity', [UserManagementController::class, 'getUserActivity'])->name('activity');
    Route::post('/{userId}/suspend', [UserManagementController::class, 'suspendUser'])->name('suspend');
    Route::post('/{userId}/lock', [UserManagementController::class, 'lockUser'])->name('lock');
    Route::post('/{userId}/unlock', [UserManagementController::class, 'unlockUser'])->name('unlock');
    Route::post('/{userId}/force-password-reset', [UserManagementController::class, 'forcePasswordReset'])->name('force-password-reset');
});

// Admin Shelter Management Routes
Route::middleware(['auth'])->prefix('admin/shelter-management')->name('admin.shelter-management.')->group(function () {
    // Main index
    Route::get('/', [AdminShelterManagementController::class, 'index'])->name('index');

    // Slot routes
    Route::post('/slots', [AdminShelterManagementController::class, 'storeSlot'])->name('store-slot');
    Route::get('/slots/{id}/edit', [AdminShelterManagementController::class, 'editSlot'])->name('edit-slot');
    Route::put('/slots/{id}', [AdminShelterManagementController::class, 'updateSlot'])->name('update-slot');
    Route::delete('/slots/{id}', [AdminShelterManagementController::class, 'deleteSlot'])->name('delete-slot');
    Route::get('/slots/{id}/details', [AdminShelterManagementController::class, 'getSlotDetails'])->name('slot-details');

    // Inventory routes
    Route::post('/inventory', [AdminShelterManagementController::class, 'storeInventory'])->name('store-inventory');
    Route::get('/inventory/{id}/details', [AdminShelterManagementController::class, 'getInventoryDetails'])->name('inventory-details');
    Route::put('/inventory/{id}', [AdminShelterManagementController::class, 'updateInventory'])->name('update-inventory');
    Route::delete('/inventory/{id}', [AdminShelterManagementController::class, 'deleteInventory'])->name('delete-inventory');

    // Animal details (cross-database query)
    Route::get('/animals/{id}/details', [AdminShelterManagementController::class, 'getAnimalDetails'])->name('animal-details');

    // Section routes
    Route::post('/sections', [AdminShelterManagementController::class, 'storeSection'])->name('store-section');
    Route::get('/sections/{id}/edit', [AdminShelterManagementController::class, 'editSection'])->name('edit-section');
    Route::put('/sections/{id}', [AdminShelterManagementController::class, 'updateSection'])->name('update-section');
    Route::delete('/sections/{id}', [AdminShelterManagementController::class, 'deleteSection'])->name('delete-section');

    // Category routes
    Route::post('/categories', [AdminShelterManagementController::class, 'storeCategory'])->name('store-category');
    Route::get('/categories/{id}/edit', [AdminShelterManagementController::class, 'editCategory'])->name('edit-category');
    Route::put('/categories/{id}', [AdminShelterManagementController::class, 'updateCategory'])->name('update-category');
    Route::delete('/categories/{id}', [AdminShelterManagementController::class, 'deleteCategory'])->name('delete-category');
});

require __DIR__.'/auth.php';
