<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Animal;
use App\Models\VisitList;
use App\Models\AnimalBooking;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\Adoption;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Services\ForeignKeyValidator;


class BookingAdoptionController extends Controller
{
    /**
     * Show user's bookings
     * Bookings from Danish's database, Animals from Shafiqah's database
     */
    public function userBookings()
    {
        // Get bookings from Danish's database with cross-database relationship to animals
        $bookings = Booking::with('animals')
            ->where('userID', auth()->id())
            ->orderBy('appointment_date', 'desc')
            ->get();

        return view('booking-adoption.main', compact('bookings'));
    }

    /**
     * Show user's visit list.
     * Visit List from Danish's database, Animals from Shafiqah's database
     */
    public function indexList()
    {
        $user = Auth::user();

        // Find or create the user's visit list in Danish's database
        $visitList = VisitList::firstOrCreate([
            'userID' => $user->id,
        ]);

        // Load animals from Shafiqah's database with cross-database relationships
        $animals = $visitList->animals()
            ->with([
                'images', // Cross-database to Eilya
                'bookings' => function($query) use ($user) {
                    $query->where('userID', $user->id)
                        ->where('status', 'Pending')
                        ->latest();
                }
            ])
            ->get();

        return view('booking-adoption.visit-list', compact('animals'));
    }

    /**
     * Add an animal to the user's visit list.
     * Visit List in Danish's database, Animal in Shafiqah's database
     */
    public function addList($animalId)
    {
        $user = Auth::user();

        // Validate animal exists in Shafiqah's database
        if (!ForeignKeyValidator::validateAnimal($animalId)) {
            return back()->with('error', 'Animal does not exist.');
        }

        // Get animal details from Shafiqah's database
        $animal = Animal::find($animalId);
        if (!$animal) {
            return back()->with('error', 'Animal does not exist.');
        }

        // CHECK 1: Prevent adding if user has active booking for this animal
        // Query Danish's database for bookings
        $hasActiveBooking = Booking::where('userID', $user->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->whereHas('animals', function ($query) use ($animalId) {
                $query->where('animal.id', $animalId);
            })
            ->exists();

        if ($hasActiveBooking) {
            return back()->with('error', "You already have an active booking for {$animal->name}. You cannot add this animal to your visit list.");
        }

        // Ensure visit list exists in Danish's database
        $visitList = VisitList::firstOrCreate([
            'userID' => $user->id
        ]);

        // CHECK 2: Prevent duplicate in visit list
        if ($visitList->animals()->where('animalID', $animalId)->exists()) {
            return back()->with('error', 'This animal is already in your visit list.');
        }

        // Attach to pivot table in Danish's database (cross-database reference to Shafiqah)
        $visitList->animals()->attach($animalId, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "{$animal->name} has been added to your visit list. You can view the list at the top right");
    }

    /**
     * Remove an animal from the visit list.
     * Visit List in Danish's database
     */
    public function removeList($animalId)
    {
        $user = Auth::user();
        $visitList = $user->visitList;

        if (!$visitList) {
            return back()->with('error', 'Visit list not found.');
        }

        // Remove from pivot table in Danish's database
        $visitList->animals()->detach($animalId);

        return back()->with('success', 'Animal removed from your visit list.');
    }

    /**
     * Confirm appointment (convert visit list -> booking + booking_animal)
     * Multi-database transaction: Danish (bookings, visit lists) and Shafiqah (animals)
     */
    public function confirmAppointment(Request $request)
    {
        try {
            Log::info('=== CONFIRM APPOINTMENT STARTED ===');
            Log::info('Request data:', $request->all());

            // Validation
            $validated = $request->validate([
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required',
                'animal_ids' => [
                    'required',
                    'array',
                    'min:1',
                    function ($attribute, $value, $fail) {
                        // Validate all animals exist in Shafiqah's database
                        $result = ForeignKeyValidator::validateAnimals($value);
                        if (!empty($result['invalid'])) {
                            $fail('Some selected animals do not exist.');
                        }
                    },
                ],
                'animal_ids.*' => 'required',
                'remarks' => 'nullable|array',
                'remarks.*' => 'nullable|string|max:500',
                'terms' => 'required|accepted',
            ], [
                'appointment_date.required' => 'Please select an appointment date.',
                'appointment_time.required' => 'Please select an appointment time.',
                'appointment_date.after_or_equal' => 'Appointment must be in the future.',
                'animal_ids.required' => 'Please select at least one animal.',
                'terms.accepted' => 'You must accept the terms and conditions.',
            ]);

            Log::info('Validation passed');

            $user = Auth::user();

            // Get the user's VISIT LIST from Danish's database
            $visitList = VisitList::where('userID', $user->id)->first();

            if (!$visitList || $visitList->animals->isEmpty()) {
                Log::warning('No visit list found for user', ['user_id' => $user->id]);
                return back()
                    ->with('error', 'Your visit list is empty.')
                    ->with('open_visit_modal', true);
            }

            Log::info('Found visit list', ['visit_list_id' => $visitList->id]);

            // Verify all selected animals are in the visit list
            $visitListAnimalIds = $visitList->animals->pluck('id')->toArray();
            $requestedAnimalIds = $validated['animal_ids'];

            Log::info('Animal verification', [
                'visit_list_animals' => $visitListAnimalIds,
                'requested_animals' => $requestedAnimalIds
            ]);

            // Check if all requested animals are in the visit list
            $invalidAnimals = array_diff($requestedAnimalIds, $visitListAnimalIds);
            if (!empty($invalidAnimals)) {
                Log::warning('Invalid animals requested', ['invalid_ids' => $invalidAnimals]);
                return back()
                    ->with('error', 'Some selected animals are not in your visit list.')
                    ->with('open_visit_modal', true);
            }

            $appointmentDate = $validated['appointment_date'];
            $appointmentTime = $validated['appointment_time'];

            // CHECK 1: ANIMAL AVAILABILITY (Pending/Confirmed bookings)
            // Cross-database query: Animals from Shafiqah, Bookings from Danish
            $conflictingAnimals = Animal::whereIn('id', $requestedAnimalIds)
                ->whereHas('bookings', function ($query) use ($appointmentDate, $appointmentTime) {
                    $query->where('appointment_date', $appointmentDate)
                        ->where('appointment_time', $appointmentTime)
                        ->whereIn('status', ['Pending', 'Confirmed']);
                })
                ->get(['id', 'name']);

            if ($conflictingAnimals->isNotEmpty()) {
                $animalNames = $conflictingAnimals->pluck('name')->join(', ');
                Log::warning('Animals not available - already booked', [
                    'conflicting_animals' => $conflictingAnimals->pluck('name')->toArray(),
                    'date' => $appointmentDate,
                    'time' => $appointmentTime
                ]);

                return back()
                    ->with('error', "The following animals are not available at {$appointmentTime} on {$appointmentDate}: {$animalNames}. Please choose a different time.")
                    ->with('open_visit_modal', true)
                    ->withInput();
            }

            // CHECK 2: PREVENT DUPLICATE BOOKINGS (including cancelled ones)
            // Query Danish's database for existing bookings
            $existingUserBookings = Booking::where('userID', $user->id)
                ->where('appointment_date', $appointmentDate)
                ->where('appointment_time', $appointmentTime)
                ->whereHas('animals', function ($query) use ($requestedAnimalIds) {
                    $query->whereIn('animal.id', $requestedAnimalIds);
                })
                ->with('animals')
                ->get();

            if ($existingUserBookings->isNotEmpty()) {
                // Get all animals that user has already booked at this time (any status)
                $alreadyBookedAnimalIds = $existingUserBookings->flatMap(function ($booking) {
                    return $booking->animals->pluck('id');
                })->unique()->toArray();

                $duplicateAnimalIds = array_intersect($requestedAnimalIds, $alreadyBookedAnimalIds);

                if (!empty($duplicateAnimalIds)) {
                    $duplicateAnimals = Animal::whereIn('id', $duplicateAnimalIds)->pluck('name')->toArray();
                    $duplicateNames = implode(', ', $duplicateAnimals);

                    $bookingStatuses = $existingUserBookings->pluck('status')->unique()->toArray();
                    $statusText = implode(', ', $bookingStatuses);

                    Log::warning('Duplicate booking attempt detected', [
                        'user_id' => $user->id,
                        'duplicate_animals' => $duplicateAnimals,
                        'existing_statuses' => $bookingStatuses,
                        'date' => $appointmentDate,
                        'time' => $appointmentTime
                    ]);

                    return back()
                        ->with('error', "You already have a booking ({$statusText}) for {$duplicateNames} at {$appointmentTime} on {$appointmentDate}. Please choose different animals or a different time slot.")
                        ->with('open_visit_modal', true)
                        ->withInput();
                }
            }

            // Use database transaction for Danish's database only
            // (Animals in Shafiqah are only read, not modified)
            DB::connection('danish')->beginTransaction();

            try {
                // Create new booking in Danish's database
                $booking = Booking::create([
                    'userID' => $user->id,
                    'status' => 'Pending',
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                ]);

                Log::info('Created booking', ['booking_id' => $booking->id]);

                // Attach animals with remarks to the booking (animal_booking pivot table in Danish)
                // This creates cross-database references to Shafiqah's animals
                $animalData = [];
                foreach ($validated['animal_ids'] as $animalId) {
                    $animalData[$animalId] = [
                        'remarks' => $validated['remarks'][$animalId] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $booking->animals()->attach($animalData);
                Log::info('Attached animals to booking', ['count' => count($animalData)]);

                // Remove the confirmed animals from the visit list (Danish's database)
                $visitList->animals()->detach($validated['animal_ids']);
                Log::info('Removed animals from visit list');

                // If visit list has no more animals, delete it
                if ($visitList->animals()->count() === 0) {
                    $visitList->delete();
                    Log::info('Deleted empty visit list');
                }

                DB::connection('danish')->commit();
                Log::info('=== CONFIRM APPOINTMENT COMPLETED SUCCESSFULLY ===');

                return redirect()->route('animal-management.index')
                    ->with('success', 'Your visit appointment has been scheduled! View them at My Booking tab');

            } catch (\Illuminate\Database\QueryException $e) {
                DB::connection('danish')->rollBack();

                // Check if it's a unique constraint violation
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'unique') !== false) {
                    Log::error('Duplicate booking detected', [
                        'user_id' => $user->id,
                        'date' => $appointmentDate,
                        'time' => $appointmentTime,
                        'error' => $e->getMessage()
                    ]);

                    return back()
                        ->with('error', 'This time slot has just been booked by another user. Please select a different time.')
                        ->with('open_visit_modal', true)
                        ->withInput();
                }

                throw $e; // Re-throw if it's a different database error
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);

            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('open_visit_modal', true);

        } catch (\Exception $e) {
            if (DB::connection('danish')->transactionLevel() > 0) {
                DB::connection('danish')->rollBack();
            }

            Log::error('Booking Confirmation Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Failed to schedule appointment. Please try again.')
                ->with('open_visit_modal', true);
        }
    }

    /**
     * Display user's bookings
     * Bookings from Danish's database
     */
    public function index(Request $request)
    {
        // Query Danish's database with cross-database relationships
        $query = Booking::where('userID', Auth::id())
            ->with([
                'animals.images',       // Danish -> Shafiqah -> Eilya
                'animals.medicals',     // Danish -> Shafiqah -> Shafiqah
                'animals.vaccinations', // Danish -> Shafiqah -> Shafiqah
                'user',                 // Danish -> Taufiq (cross-database)
                'adoptions'             // Danish -> Danish
            ]);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(40)
            ->appends($request->query());

        // Count statuses for this user only from Danish's database
        $statusCounts = Booking::where('userID', Auth::id())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Calculate total of all bookings (unfiltered)
        $totalBookings = $statusCounts->sum();

        return view('booking-adoption.main', compact('bookings', 'statusCounts','totalBookings'));
    }

    /**
     * Display a listing of all bookings for admin.
     * Bookings from Danish's database
     */
    public function indexAdmin(Request $request)
    {
        // Query Danish's database with cross-database relationships
        $query = Booking::with([
            'animals.images',       // Danish -> Shafiqah -> Eilya
            'animals.medicals',     // Danish -> Shafiqah -> Shafiqah
            'animals.vaccinations', // Danish -> Shafiqah -> Shafiqah
            'user',                 // Danish -> Taufiq (cross-database)
            'adoptions'             // Danish -> Danish
        ]);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(40)
            ->appends($request->query());

        // Count statuses from Danish's database
        $statusCounts = Booking::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Calculate total of all bookings (unfiltered)
        $totalBookings = $statusCounts->sum();

        return view('booking-adoption.admin', compact('bookings', 'statusCounts', 'totalBookings'));
    }

    /**
     * Cancel a booking
     * Update booking in Danish's database
     */
    public function cancel(Booking $booking)
    {
        // Verify ownership
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow cancellation of Pending or Confirmed bookings
        if (in_array($booking->status, ['Pending', 'Confirmed'])) {
            // Update booking status in Danish's database
            $booking->update(['status' => 'Cancelled']);

            return redirect()->route('booking:main')
                ->with('success', 'Booking cancelled successfully!');
        }

        return redirect()->route('booking:main')
            ->with('error', 'Cannot cancel this booking.');
    }

    /**
     * Show adoption fee calculation
     * Bookings from Danish's database, Animals from Shafiqah's database
     */
    public function showAdoptionFee(Booking $booking, Request $request)
    {
        try {
            // Authorization - check user owns this booking
            if ($booking->userID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

            // Get selected animals from request (support multiple IDs)
            $animalIds = $request->query('animal_ids', $booking->animals->pluck('id')->toArray());

            // Validate all animal IDs exist in Shafiqah's database
            $validationResult = ForeignKeyValidator::validateAnimals($animalIds);
            if (!empty($validationResult['invalid'])) {
                Log::warning('Invalid animal IDs in fee calculation', [
                    'invalid_ids' => $validationResult['invalid']
                ]);
                return back()->with('error', 'Some selected animals do not exist.');
            }

            // Get animals from Shafiqah's database with cross-database query
            // Avoid ambiguous 'id' by prefixing table name
            $animals = $booking->animals()
                ->whereIn('animal.id', $animalIds)
                ->with(['medicals', 'vaccinations']) // eager load from Shafiqah's database
                ->get();

            $totalFee = 0;
            $allFeeBreakdowns = [];

            foreach ($animals as $animal) {
                $medicalRecords = $animal->medicals ?? collect();
                $vaccinationRecords = $animal->vaccinations ?? collect();

                // Calculate fee per animal
                $feeBreakdown = $this->calculateAdoptionFee($animal, $medicalRecords, $vaccinationRecords);

                $totalFee += $feeBreakdown['total_fee'];
                $allFeeBreakdowns[$animal->id] = $feeBreakdown;
            }

            // Return view with all animals and fees
            return view('booking-adoption.main', compact('booking', 'animals', 'allFeeBreakdowns', 'totalFee'));

        } catch (\Throwable $e) {
            Log::error('Show Adoption Fee Error: BookingID=' . $booking->id . ' | ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return a JSON error for AJAX requests
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to load adoption fee details.'], 500);
            }

            // Or fallback for normal requests
            abort(500, 'Failed to load adoption fee details.');
        }
    }

    /**
     * Confirm booking and proceed to payment
     * Updates Danish's database, validates animals in Shafiqah's database
     */
    public function confirm(Request $request, $bookingId)
    {
        // Log raw received input
        Log::info('Confirm Booking - Raw Request Input', [
            'booking_id' => $bookingId,
            'input' => $request->all(),
            'user' => auth()->id()
        ]);

        // Start transaction for Danish's database
        DB::connection('danish')->beginTransaction();

        try {
            Log::info("Running validation for booking confirmation...", ['booking_id' => $bookingId]);

            // Validate
            $validated = $request->validate([
                'animal_ids' => [
                    'required',
                    'array',
                    'min:1',
                    function ($attribute, $value, $fail) {
                        // Validate all animals exist in Shafiqah's database
                        $result = ForeignKeyValidator::validateAnimals($value);
                        if (!empty($result['invalid'])) {
                            $fail('Some selected animals do not exist.');
                        }
                    },
                ],
                'animal_ids.*' => 'required',
                'total_fee'   => 'required|numeric|min:0',
                'agree_terms' => 'required|accepted',
            ], [
                'animal_ids.required' => 'Please select at least one animal.',
                'animal_ids.min'      => 'Please select at least one animal.',
                'agree_terms.required' => 'You must agree to the terms.',
                'agree_terms.accepted' => 'You must agree to the terms.',
            ]);

            Log::info("Validation passed for booking confirmation", [
                'booking_id' => $bookingId,
                'validated'  => $validated
            ]);

            // Retrieve booking from Danish's database
            $booking = Booking::with('animals')->findOrFail($bookingId);

            // Check booking status (case-insensitive)
            $currentStatus = strtolower($booking->status);
            if ($currentStatus !== 'pending' && $currentStatus !== 'confirmed') {
                Log::error("Booking status invalid for confirmation", [
                    'booking_id' => $bookingId,
                    'status' => $booking->status,
                    'normalized_status' => $currentStatus
                ]);

                DB::connection('danish')->rollBack();
                return back()->with('error', 'This booking cannot be confirmed. Current status: ' . $booking->status);
            }

            Log::info("Booking status valid for confirmation", [
                'booking_id' => $bookingId,
                'status' => $booking->status
            ]);

            // Get animal IDs from animal_booking pivot table in Danish's database
            // Avoid ambiguous "id" column by explicitly referencing table
            $bookingAnimalIDs = AnimalBooking::where('animal_booking.bookingID', $bookingId)
                ->pluck('animal_booking.animalID')
                ->toArray();

            Log::info("Comparing selected animal IDs with booking animals", [
                'booking_id' => $bookingId,
                'selected' => $validated['animal_ids'],
                'booking_animals' => $bookingAnimalIDs
            ]);

            // Ensure ALL selected animals belong to this booking
            foreach ($validated['animal_ids'] as $chosen) {
                if (!in_array($chosen, $bookingAnimalIDs)) {
                    Log::error("Animal ID does not belong to this booking", [
                        'booking_id' => $bookingId,
                        'invalid_animal' => $chosen
                    ]);

                    DB::connection('danish')->rollBack();
                    return back()->with('error', 'Invalid animal selection for this booking.');
                }
            }

            // Get selected animals for payment from Shafiqah's database
            $selectedAnimals = Animal::whereIn('id', $validated['animal_ids'])->get();
            $animalNames = $selectedAnimals->pluck('name')->toArray();

            // Update booking to Confirmed status in Danish's database
            $booking->update([
                'totalFee' => $validated['total_fee'],
                'status' => 'Confirmed',
            ]);

            DB::connection('danish')->commit();

            Log::info("Booking confirmed, preparing for payment", [
                'booking_id' => $bookingId,
                'total_fee' => $validated['total_fee'],
                'animal_count' => count($validated['animal_ids'])
            ]);

            // Store session info for payment
            session([
                'booking_id' => $booking->id,
                'adoption_fee' => $validated['total_fee'],
                'animal_ids' => $validated['animal_ids'],
                'animal_names' => implode(', ', $animalNames),
            ]);

            // Redirect to payment
            return $this->createBill($booking, $validated['total_fee'], $selectedAnimals);

        } catch (\Exception $e) {
            DB::connection('danish')->rollBack();

            Log::error("Booking Confirmation Error: {$e->getMessage()}", [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An unexpected error occurred while confirming the booking.');
        }
    }

    /**
     * Create ToyyibPay bill for payment
     */
    public function createBill(Booking $booking, $adoptionFee, $selectedAnimals)
    {
        $user = Auth::user();

        // Get animal names from Shafiqah's database
        $animalNames = $selectedAnimals->pluck('name')->toArray();
        $animalCount = count($animalNames);

        // Create bill name and description
        if ($animalCount === 1) {
            $billName = 'Adopt ' . substr($animalNames[0], 0, 20);
            $billDescription = 'Adoption fee for ' . $animalNames[0] . ' (Booking #' . $booking->id . ')';
        } else {
            $billName = 'Adopt ' . $animalCount . ' Animals';
            $billDescription = 'Adoption fee for ' . implode(', ', array_slice($animalNames, 0, 3)) .
                ($animalCount > 3 ? ' and ' . ($animalCount - 3) . ' more' : '') .
                ' (Booking #' . $booking->id . ')';
        }

        $referenceNo = 'BOOKING-' . $booking->id . '-' . time();

        $option = [
            'userSecretKey' => config('toyyibpay.key'),
            'categoryCode' => config('toyyibpay.category'),
            'billName' => $billName,
            'billDescription' => $billDescription,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => ($adoptionFee) * 100,
            'billReturnUrl' => route('toyyibpay-status'),
            'billCallbackUrl' => route('toyyibpay-callback'),
            'billExternalReferenceNo' => $referenceNo,
            'billTo' => $user->name,
            'billEmail' => $user->email,
            'billPhone' => $user->phoneNum ?? '0000000000',
            'billSplitPayment' => 0,
            'billPaymentChannel' => 0,
            'billChargeToCustomer' => 1,
            'billContentEmail' => 'Thank you for adopting from our shelter!',
        ];

        $url = 'https://dev.toyyibpay.com/index.php/api/createBill';
        $response = Http::withoutVerifying()->asForm()->post($url, $option);
        $data = $response->json();

        if (isset($data[0]['BillCode'])) {
            $billCode = $data[0]['BillCode'];

            // Store bill info in session
            session([
                'bill_code' => $billCode,
                'reference_no' => $referenceNo
            ]);

            Log::info('Bill Created', [
                'booking_id' => $booking->id,
                'bill_code' => $billCode,
                'reference_no' => $referenceNo,
                'amount' => $adoptionFee,
            ]);

            return redirect('https://dev.toyyibpay.com/' . $billCode);
        } else {
            // If bill creation fails, revert booking status back to Pending in Danish's database
            $booking->update(['status' => 'Pending']);

            Log::error('Bill Creation Failed', [
                'booking_id' => $booking->id,
                'response' => $data,
            ]);

            return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment. Please try again.']);
        }
    }

    /**
     * Handle payment status after ToyyibPay redirect
     * Multi-database transaction: Danish (bookings, transactions, adoptions) and Shafiqah (animals)
     */
    public function paymentStatus(Request $request)
    {
        $statusId = $request->input('status_id');
        $billCode = $request->input('billcode');
        $orderId = $request->input('order_id');

        // Get session data
        $bookingId = session('booking_id');
        $adoptionFee = session('adoption_fee');
        $animalIds = session('animal_ids', []);
        $animalNames = session('animal_names');
        $referenceNo = session('reference_no');
        $feeBreakdowns = session('fee_breakdowns', []); // Individual fees per animal

        $paymentStatus = $this->getBillTransactions($billCode);

        if ($statusId == 1) {
            // Payment Success - Use multi-database transaction
            DB::connection('danish')->beginTransaction();
            DB::connection('shafiqah')->beginTransaction();

            try {
                if ($bookingId) {
                    // Get booking from Danish's database
                    $booking = Booking::find($bookingId);

                    if ($booking) {
                        // Update booking status to Completed in Danish's database
                        $booking->update(['status' => 'Completed']);

                        // Update all selected animals to Adopted in Shafiqah's database
                        foreach ($animalIds as $animalId) {
                            Animal::where('id', $animalId)->update(['adoption_status' => 'Adopted']);
                        }

                        // Create transaction record in Danish's database
                        $transaction = Transaction::create([
                            'amount' => $adoptionFee,
                            'status' => 'Success',
                            'remarks' => 'Adoption payment for ' . $animalNames . ' (Booking #' . $bookingId . ')',
                            'type' => 'FPX Online Banking',
                            'bill_code' => $billCode,
                            'reference_no' => $referenceNo,
                            'userID' => Auth::id(), // Cross-database reference to Taufiq
                        ]);

                        // Create individual adoption record for EACH animal in Danish's database
                        foreach ($animalIds as $index => $animalId) {
                            // Get animal name from Shafiqah's database
                            $animal = Animal::find($animalId);
                            $animalName = $animal ? $animal->name : 'Unknown';

                            // Get individual fee if available, otherwise divide total equally
                            $individualFee = isset($feeBreakdowns[$animalId])
                                ? $feeBreakdowns[$animalId]
                                : ($adoptionFee / count($animalIds));

                            // Create adoption record in Danish's database
                            Adoption::create([
                                'fee' => $individualFee,
                                'remarks' => 'Adopted: ' . $animalName,
                                'bookingID' => $bookingId,
                                'transactionID' => $transaction->id,
                            ]);

                            Log::info('Adoption record created for animal', [
                                'booking_id' => $bookingId,
                                'animal_id' => $animalId,
                                'animal_name' => $animalName,
                                'fee' => $individualFee,
                                'transaction_id' => $transaction->id
                            ]);
                        }

                        Log::info('All adoption records created', [
                            'booking_id' => $bookingId,
                            'animal_count' => count($animalIds),
                            'total_fee' => $adoptionFee,
                            'animals' => $animalNames,
                            'transaction_id' => $transaction->id
                        ]);

                        // Update user role in Taufiq's database
                        $user = Auth::user();
                        if ($user->hasRole('public user')) {
                            $user->removeRole('public user');
                            $user->assignRole('adopter');
                        } elseif ($user->hasRole('caretaker')) {
                            if (!$user->hasRole('adopter')) {
                                $user->assignRole('adopter');
                            }
                        }

                        DB::connection('danish')->commit();
                        DB::connection('shafiqah')->commit();

                        // Clean up session
                        session()->forget(['booking_id', 'adoption_fee', 'animal_ids', 'animal_names', 'bill_code', 'reference_no', 'fee_breakdowns']);

                        Log::info('Payment Success', [
                            'booking_id' => $bookingId,
                            'amount' => $adoptionFee,
                            'animal_ids' => $animalIds,
                            'animal_count' => count($animalIds),
                            'bill_code' => $billCode,
                            'reference_no' => $referenceNo
                        ]);
                    }
                }
            } catch (\Exception $e) {
                DB::connection('danish')->rollBack();
                DB::connection('shafiqah')->rollBack();

                Log::error('Payment processing error: ' . $e->getMessage(), [
                    'booking_id' => $bookingId,
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e;
            }
        } else {
            // Payment Failed/Pending - Booking remains in 'Confirmed' status
            if ($bookingId) {
                // Get booking from Danish's database
                $booking = Booking::find($bookingId);

                if ($booking && strtolower($booking->status) == 'confirmed') {
                    Log::info('Payment Failed/Pending', [
                        'booking_id' => $bookingId,
                        'status_id' => $statusId,
                        'animal_ids' => $animalIds,
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo
                    ]);

                    // Create failed transaction record in Danish's database
                    Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Failed',
                        'remarks' => 'Failed adoption payment for ' . $animalNames . ' (Booking #' . $bookingId . ')',
                        'type' => 'Adoption Fee',
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo,
                        'userID' => Auth::id(), // Cross-database reference to Taufiq
                    ]);
                }
            }
        }

        return view('booking-adoption.payment-status', [
            'status_id' => $statusId,
            'billcode' => $billCode,
            'order_id' => $orderId,
            'booking_id' => $bookingId,
            'amount' => $adoptionFee,
            'animal_names' => $animalNames,
            'animal_count' => count($animalIds),
            'reference_no' => $referenceNo,
            'payment_details' => $paymentStatus,
        ]);
    }

    /**
     * Handle ToyyibPay callback
     * Multi-database update: Danish (bookings, transactions) and Shafiqah (animals)
     */
    public function callback(Request $request)
    {
        Log::info('ToyyibPay Callback:', $request->all());

        $billCode = $request->input('billcode');
        $statusId = $request->input('status_id');
        $referenceNo = $request->input('refno');

        if (strpos($referenceNo, 'BOOKING-') !== false) {
            $parts = explode('-', $referenceNo);
            if (isset($parts[1])) {
                $bookingId = $parts[1];

                // Use multi-database transaction
                DB::connection('danish')->beginTransaction();
                DB::connection('shafiqah')->beginTransaction();

                try {
                    // Get booking from Danish's database
                    $booking = Booking::find($bookingId);

                    if ($booking && $statusId == 1) {
                        // Payment successful - update booking to Completed in Danish's database
                        $booking->update(['status' => 'Completed']);

                        // Update all animals in this booking to Adopted in Shafiqah's database
                        $animalIds = $booking->animals->pluck('id')->toArray();
                        foreach ($animalIds as $animalId) {
                            Animal::where('id', $animalId)->update(['adoption_status' => 'Adopted']);
                        }

                        // Check if transaction already exists in Danish's database
                        $existingTransaction = Transaction::where('bill_code', $billCode)->first();
                        if (!$existingTransaction) {
                            Transaction::create([
                                'amount' => $request->input('amount') / 100,
                                'status' => 'Success',
                                'remarks' => 'Adoption payment (Callback) - Booking #' . $bookingId . ' (' . count($animalIds) . ' animals)',
                                'type' => 'Adoption Fee',
                                'bill_code' => $billCode,
                                'reference_no' => $referenceNo,
                                'userID' => $booking->userID, // Cross-database reference to Taufiq
                            ]);
                        }

                        DB::connection('danish')->commit();
                        DB::connection('shafiqah')->commit();

                        Log::info('Callback: Booking Completed', [
                            'booking_id' => $bookingId,
                            'animal_count' => count($animalIds),
                        ]);
                    }
                } catch (\Exception $e) {
                    DB::connection('danish')->rollBack();
                    DB::connection('shafiqah')->rollBack();

                    Log::error('Callback processing error: ' . $e->getMessage(), [
                        'booking_id' => $bookingId,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Get bill transactions from ToyyibPay
     */
    private function getBillTransactions($billCode)
    {
        $url = 'https://dev.toyyibpay.com/index.php/api/getBillTransactions';

        $response = Http::withoutVerifying()->asForm()->post($url, [
            'billCode' => $billCode,
            'userSecretKey' => config('toyyibpay.key'),
        ]);

        return $response->json();
    }

    /**
     * Calculate adoption fee based on medical and vaccination records
     * (Add this method if it doesn't exist in your original controller)
     */
    private function calculateAdoptionFee($animal, $medicalRecords, $vaccinationRecords)
    {
        $baseFee = 100; // Base adoption fee
        $medicalCosts = $medicalRecords->sum('costs');
        $vaccinationCosts = $vaccinationRecords->sum('costs');

        $totalFee = $baseFee + $medicalCosts + $vaccinationCosts;

        return [
            'base_fee' => $baseFee,
            'medical_costs' => $medicalCosts,
            'vaccination_costs' => $vaccinationCosts,
            'total_fee' => $totalFee,
        ];
    }
}
