<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrayReportingManagementController;
use App\Http\Controllers\AnimalManagementController;
use App\Http\Controllers\ShelterManagementController;
use App\Http\Controllers\BookingAdoptionController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Http\Controllers\RescueMapController;

Route::get('/rescue-map', [RescueMapController::class, 'index'])->name('rescue.map');
Route::get('/api/rescue-clusters', [RescueMapController::class, 'getClusterData'])->name('rescue.clusters');
Route::get('/dashboard', Dashboard::class)->name('dashboard');

Route::get('/', [StrayReportingManagementController::class, 'indexUser'])->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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

});

//Shelter-Management
Route::middleware('auth')->group(function () {
    Route::get('/slots', [ShelterManagementController::class, 'indexSlot'])->name('shelter-management.index');
    Route::post('/slots-store', [ShelterManagementController::class, 'storeSlot'])->name('shelter-management.store-slot');
    Route::put('/shelter-management/slots/{id}', [ShelterManagementController::class, 'updateSlot'])->name('shelter-management.update-slot');
    Route::delete('/slots/{id}', [ShelterManagementController::class, 'deleteSlot'])->name('shelter-management.delete-slot');
    Route::get('/shelter-management/slots/{id}/edit', [ShelterManagementController::class, 'editSlot'])->name('shelter-management.edit-slot');
    Route::get('/shelter-management/slots/{id}/details', [ShelterManagementController::class, 'getSlotDetails'])->name('shelter-management.slot-details');
    Route::post('/shelter-management/inventory', [ShelterManagementController::class, 'storeInventory'])->name('shelter-management.store-inventory');

    Route::get('/shelter-management/inventory/{id}/details', [ShelterManagementController::class, 'getInventoryDetails'])->name('shelter-management.inventory-details');
    Route::put('/shelter-management/inventory/{id}', [ShelterManagementController::class, 'updateInventory'])->name('shelter-management.update-inventory');
    Route::delete('/shelter-management/inventory/{id}', [ShelterManagementController::class, 'deleteInventory'])->name('shelter-management.delete-inventory');
    Route::get('/shelter-management/animals/{id}/details', [ShelterManagementController::class, 'getAnimalDetails'])->name('shelter-management.animal-details');
});


//Booking-Adoption
Route::middleware('auth')->group(function () {
    Route::get('/booking:main', [BookingAdoptionController::class, 'index'])->name('booking:main');


    // Visit list actions
    Route::get('/visit-list', [BookingAdoptionController::class, 'indexList'])->name('visit.list');
    Route::post('/visit-list/add/{animal}', [BookingAdoptionController::class, 'addList'])->name('visit.list.add');
    Route::post('/visit-list/remove/{animal}', [BookingAdoptionController::class, 'removeList'])->name('visit.list.remove');

// Booking final submission
    Route::post('/adoption/book', [BookingAdoptionController::class, 'storeBooking'])->name('adoption.book')->middleware('auth');
    Route::get('/bookings/index', [BookingAdoptionController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/all', [BookingAdoptionController::class, 'indexAdmin'])->name('bookings.index-admin');
    Route::get('/bookings/create', [BookingAdoptionController::class, 'create'])->name('bookings.create');
    Route::post('/bookings/store', [BookingAdoptionController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingAdoptionController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [BookingAdoptionController::class, 'cancel'])->name('bookings.cancel');
   // Route to show the fee modal (GET request)
    Route::get('//bookings/{id}/adoption-fee', [BookingAdoptionController::class, 'showAdoptionFee'])->name('bookings.adoption-fee');
    // Route to confirm booking (PATCH request - only updates status)
    Route::patch('/bookings/{booking}/confirm', [BookingAdoptionController::class, 'confirm'])->name('bookings.confirm')->middleware('auth');

    Route::get('/payment/status', [BookingAdoptionController::class, 'paymentStatus'])->name('toyyibpay-status');
    Route::post('/payment/callback', [BookingAdoptionController::class, 'callback'])->name('toyyibpay-callback');


    Route::get('/bookings/{id}/modal', [BookingAdoptionController::class, 'showModal'])->name('bookings.show.modal')->middleware('auth');
    Route::get('/bookings/{id}/modal/admin', [BookingAdoptionController::class, 'showModalAdmin'])->name('bookings.show.modal-admin')->middleware('auth');

});

require __DIR__.'/auth.php';
