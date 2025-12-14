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
use App\DatabaseErrorHandler;


class BookingAdoptionController extends Controller
{
    use DatabaseErrorHandler;
    /**
     * Helper method to check if animals have active bookings at a specific date/time
     * Active bookings = Pending or Confirmed status
     */
    private function getAnimalsWithBookingConflicts(array $animalIds, $appointmentDate, $appointmentTime)
    {
        return Animal::whereIn('id', $animalIds)
            ->whereHas('bookings', function ($query) use ($appointmentDate, $appointmentTime) {
                $query->where('appointment_date', $appointmentDate)
                      ->where('appointment_time', $appointmentTime)
                      ->whereIn('status', ['Pending', 'Confirmed']);
            })
            ->with(['bookings' => function($query) use ($appointmentDate, $appointmentTime) {
                $query->where('appointment_date', $appointmentDate)
                      ->where('appointment_time', $appointmentTime)
                      ->whereIn('status', ['Pending', 'Confirmed'])
                      ->with('user');
            }])
            ->get();
    }

    /**
     * Helper method to get detailed error message for booked animals
     */
    private function getBookedAnimalsErrorMessage($bookedAnimals, $currentUserId = null)
    {
        $messages = [];

        foreach ($bookedAnimals as $animal) {
            $booking = $animal->bookings->first();
            $isOwnBooking = $currentUserId && $booking->userID == $currentUserId;

            $userInfo = $isOwnBooking
                ? 'by you'
                : 'by ' . ($booking->user->name ?? 'another user');

            $messages[] = sprintf(
                '<strong>%s</strong>: Already booked %s on <strong>%s at %s</strong> (Booking #%d - %s)',
                $animal->name,
                $userInfo,
                \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y'),
                \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A'),
                $booking->id,
                $booking->status
            );
        }

        return $messages;
    }

    public function userBookings()
    {
        $bookings = $this->safeQuery(
            fn() => Booking::with('animals')
                ->where('userID', auth()->id())
                ->orderBy('appointment_date', 'desc')->get(),
            collect([])
        );

        return view('booking-adoption.main', compact('bookings'));
    }

    // Show visit list
    /**
     * Show user's visit list.
     */
    //BookingAdoptionController.php
    public function indexList()
    {
        $user = Auth::user();

        // Find or create the user's visit list with error handling
        $animals = $this->safeQuery(function() use ($user) {
            $visitList = VisitList::firstOrCreate([
                'userID' => $user->id,
            ]);

            // Load animals with their images and pending bookings
            return $visitList->animals()
                ->with([
                    'images', // Add this to eager load images
                    'bookings' => function($query) use ($user) {
                        $query->where('userID', $user->id)
                            ->where('status', 'Pending')
                            ->latest();
                    }
                ])
                ->get();
        }, collect([]));

        return view('booking-adoption.visit-list', compact('animals'));
    }

    /**
     * Add an animal to the user's visit list.
     */
    public function addList($animalId)
    {
        $user = Auth::user();

        // Validate animal exists with error handling
        $animal = $this->safeQuery(
            fn() => Animal::find($animalId),
            null
        );

        if (!$animal) {
            return back()->with('error', 'Animal does not exist or database connection unavailable.');
        }

        // Note: We allow adding animals to visit list even if they have bookings
        // Users can select different time slots when creating their booking
        // Conflict checking happens during booking confirmation

        // Ensure visit list exists with error handling
        $result = $this->safeQuery(function() use ($user, $animalId, $animal) {
            $visitList = VisitList::firstOrCreate([
                'userID' => $user->id
            ]);

            // ===== CHECK: Prevent duplicate in visit list =====
            if ($visitList->animals()->where('animalID', $animalId)->exists()) {
                return ['error' => 'This animal is already in your visit list.'];
            }
            // ===== END CHECK =====

            // Attach to pivot table
            $visitList->animals()->attach($animalId, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['success' => "{$animal->name} has been added to your visit list. You can view the list at the top right"];
        }, ['error' => 'Database connection unavailable. Please try again later.']);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', $result['success']);
    }

    /**
     * Remove an animal from the visit list.
     */
    public function removeList($animalId)
    {
        $user = Auth::user();
        $visitList = $user->visitList;

        if (!$visitList) {
            return back()->with('error', 'Visit list not found.');
        }

        $visitList->animals()->detach($animalId);

        return back()->with('success', 'Animal removed from your visit list.');
    }

     /* Confirm appointment (convert visit list -> booking + booking_animal)
     */
    public function confirmAppointment(Request $request)
    {
        try {
            \Log::info('=== CONFIRM APPOINTMENT STARTED ===');
            \Log::info('Request data:', $request->all());

            // Validation
            $validated = $request->validate([
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required',
                'animal_ids' => 'required|array|min:1',
                'animal_ids.*' => 'required|exists:shafiqah.animal,id',  // Cross-database: Animal on shafiqah
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

            \Log::info('Validation passed');

            $user = Auth::user();

            // Get the user's VISIT LIST
            $visitList = VisitList::where('userID', $user->id)->first();

            if (!$visitList || $visitList->animals->isEmpty()) {
                \Log::warning('No visit list found for user', ['user_id' => $user->id]);
                return back()
                    ->with('error', 'Your visit list is empty.')
                    ->with('open_visit_modal', true);
            }

            \Log::info('Found visit list', ['visit_list_id' => $visitList->id]);

            // Verify all selected animals are in the visit list
            $visitListAnimalIds = $visitList->animals->pluck('id')->toArray();
            $requestedAnimalIds = $validated['animal_ids'];

            \Log::info('Animal verification', [
                'visit_list_animals' => $visitListAnimalIds,
                'requested_animals' => $requestedAnimalIds
            ]);

            // Check if all requested animals are in the visit list
            $invalidAnimals = array_diff($requestedAnimalIds, $visitListAnimalIds);
            if (!empty($invalidAnimals)) {
                \Log::warning('Invalid animals requested', ['invalid_ids' => $invalidAnimals]);
                return back()
                    ->with('error', 'Some selected animals are not in your visit list.')
                    ->with('open_visit_modal', true);
            }

            $appointmentDate = $validated['appointment_date'];
            $appointmentTime = $validated['appointment_time'];

            // ===== CHECK: PREVENT TIME SLOT CONFLICTS =====
            // Animals can have multiple bookings, but not at the same date/time
            $conflictingAnimals = $this->getAnimalsWithBookingConflicts(
                $requestedAnimalIds,
                $appointmentDate,
                $appointmentTime
            );

            if ($conflictingAnimals->isNotEmpty()) {
                $errorMessages = $this->getBookedAnimalsErrorMessage($conflictingAnimals, $user->id);
                $errorHtml = 'The following animals are not available at <strong>'
                    . \Carbon\Carbon::parse($appointmentDate)->format('M d, Y')
                    . ' at ' . \Carbon\Carbon::parse($appointmentTime)->format('g:i A')
                    . '</strong>:<br><br>'
                    . implode('<br>', $errorMessages)
                    . '<br><br>Please choose a different time slot or remove these animals from your selection.';

                \Log::warning('Time slot conflict detected', [
                    'animals' => $conflictingAnimals->pluck('name')->toArray(),
                    'date' => $appointmentDate,
                    'time' => $appointmentTime
                ]);

                return back()
                    ->with('error', $errorHtml)
                    ->with('open_visit_modal', true)
                    ->withInput();
            }

            // ===== CHECK 2: PREVENT DUPLICATE BOOKINGS (including cancelled ones) =====
            // Check if user is trying to rebook the same animals at the same time slot
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

                    \Log::warning('Duplicate booking attempt detected', [
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
            // ===== END AVAILABILITY CHECKS =====

            // Use database transaction to ensure data integrity
            // All operations are on danish database (Booking, VisitList, pivot tables)
            DB::connection('danish')->beginTransaction();

            try {
                // Create new booking
                $booking = Booking::create([
                    'userID' => $user->id,
                    'status' => 'Pending',
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                ]);

                \Log::info('Created booking', ['booking_id' => $booking->id]);

                // Attach animals with remarks to the booking
                $animalData = [];
                foreach ($validated['animal_ids'] as $animalId) {
                    $animalData[$animalId] = [
                        'remarks' => $validated['remarks'][$animalId] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $booking->animals()->attach($animalData);
                \Log::info('Attached animals to booking', ['count' => count($animalData)]);

                // Remove the confirmed animals from the visit list
                $visitList->animals()->detach($validated['animal_ids']);
                \Log::info('Removed animals from visit list');

                // If visit list has no more animals, delete it
                if ($visitList->animals()->count() === 0) {
                    $visitList->delete();
                    \Log::info('Deleted empty visit list');
                }

                DB::connection('danish')->commit();
                \Log::info('=== CONFIRM APPOINTMENT COMPLETED SUCCESSFULLY ===');

                return redirect()->route('animal-management.index')
                    ->with('success', 'Your visit appointment has been scheduled! View them at My Booking tab');

            } catch (\Illuminate\Database\QueryException $e) {
                DB::connection('danish')->rollBack();

                // Check if it's a unique constraint violation
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'unique') !== false) {
                    \Log::error('Duplicate booking detected', [
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
            \Log::error('Validation failed', ['errors' => $e->errors()]);

            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('open_visit_modal', true);

        } catch (\Exception $e) {
            if (DB::connection('danish')->transactionLevel() > 0) {
                DB::connection('danish')->rollBack();
            }

            \Log::error('Booking Confirmation Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Failed to schedule appointment. Please try again.')
                ->with('open_visit_modal', true);
        }
    }

    public function index(Request $request)
    {
        $result = $this->safeQuery(function() use ($request) {
            $query = Booking::where('userID', Auth::id())
                ->with([
                    'animals.images',       // For displaying animal photos
                    'animals.medicals',     // For calculating medical fees
                    'animals.vaccinations', // For calculating vaccination fees
                    'user',                 // For booker information
                    'adoptions'             // For showing adoption status
                ]);

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $bookings = $query->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->paginate(40)
                ->appends($request->query());

            // Count statuses for this user only
            $statusCounts = Booking::where('userID', Auth::id())
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            // Calculate total of all bookings (unfiltered)
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts','totalBookings');
        }, [
            'bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40),
            'statusCounts' => collect([]),
            'totalBookings' => 0
        ]);

        return view('booking-adoption.main', $result);
    }

    /**
     * Display a listing of all bookings for admin.
     */
    public function indexAdmin(Request $request)
    {
        $result = $this->safeQuery(function() use ($request) {
            $query = Booking::with([
                'animals.images',
                'animals.medicals',
                'animals.vaccinations',
                'user',
                'adoptions'
            ]);

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $bookings = $query->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->paginate(40)
                ->appends($request->query());

            $statusCounts = Booking::select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            // Calculate total of all bookings (unfiltered)
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts', 'totalBookings');
        }, [
            'bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40),
            'statusCounts' => collect([]),
            'totalBookings' => 0
        ]);

        return view('booking-adoption.admin', $result);
    }

    // Cancel a booking
    public function cancel(Booking $booking)
    {
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($booking->status, ['Pending', 'Confirmed'])) {
            $booking->update(['status' => 'Cancelled']);
            return redirect()->route('booking:main')->with('success', 'Booking cancelled successfully!');
        }

        return redirect()->route('booking:main')->with('error', 'Cannot cancel this booking.');
    }

    public function showAdoptionFee(Booking $booking, Request $request)
    {
        try {
            // Authorization
            if ($booking->userID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

            // Get selected animals from request (support multiple IDs)
            $animalIds = $request->query('animal_ids', $booking->animals->pluck('id')->toArray());

            // Avoid ambiguous 'id' by prefixing table name
            $animals = $booking->animals()
                ->whereIn('animal.id', $animalIds)
                ->with(['medicals', 'vaccinations']) // eager load
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
            \Log::error('Show Adoption Fee Error: BookingID=' . $booking->id . ' | ' . $e->getMessage(), [
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

    public function confirm(Request $request, $bookingId)
    {
        // Log raw received input
        Log::info('Confirm Booking - Raw Request Input', [
            'booking_id' => $bookingId,
            'input' => $request->all(),
            'user' => auth()->id()
        ]);

        try {

            Log::info("Running validation for booking confirmation...", ['booking_id' => $bookingId]);

            // Validate
            $validated = $request->validate([
                'animal_ids' => 'required|array|min:1',
                'animal_ids.*' => 'required|exists:shafiqah.animal,id',  // Cross-database: Animal on shafiqah
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

            // Retrieve booking
            $booking = Booking::with('animals')->findOrFail($bookingId);

            // Check booking status (case-insensitive)
            $currentStatus = strtolower($booking->status);
            if ($currentStatus !== 'pending' && $currentStatus !== 'confirmed') {
                Log::error("Booking status invalid for confirmation", [
                    'booking_id' => $bookingId,
                    'status' => $booking->status,
                    'normalized_status' => $currentStatus
                ]);

                return back()->with('error', 'This booking cannot be confirmed. Current status: ' . $booking->status);
            }

            Log::info("Booking status valid for confirmation", [
                'booking_id' => $bookingId,
                'status' => $booking->status
            ]);

            /*
            |--------------------------------------------------------------------------
            | FIX: Avoid ambiguous "id" column
            | We reference tables explicitly to avoid SQL error 42702.
            |--------------------------------------------------------------------------
            */
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

                    return back()->with('error', 'Invalid animal selection for this booking.');
                }
            }

            // Get selected animals for payment
            $selectedAnimals = Animal::whereIn('id', $validated['animal_ids'])->get();
            $animalNames = $selectedAnimals->pluck('name')->toArray();

            // Update booking to Confirmed status
            $booking->update([
                'totalFee' => $validated['total_fee'],
                'status' => 'Confirmed', // Use proper case
            ]);

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

            Log::error("Booking Confirmation Error: {$e->getMessage()}", [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An unexpected error occurred while confirming the booking.');
        }
    }



    public function createBill(Booking $booking, $adoptionFee, $selectedAnimals)
    {
        $user = Auth::user();

        // Get animal names
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
            'billPhone' => $user->phone ?? '0000000000',
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
            // If bill creation fails, revert booking status back to Pending
            $booking->update(['status' => 'Confirmed']);

            Log::error('Bill Creation Failed', [
                'booking_id' => $booking->id,
                'response' => $data,
            ]);

            return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment. Please try again.']);
        }
    }

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
            // Payment Success
            if ($bookingId) {
                $booking = Booking::find($bookingId);

                if ($booking) {
                    // Update booking status to Completed
                    $booking->update(['status' => 'Completed']);

                    // Update all selected animals to Adopted
                    foreach ($animalIds as $animalId) {
                        Animal::where('id', $animalId)->update(['adoption_status' => 'Adopted']);
                    }

                    // Create transaction record
                    $transaction = Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Success',
                        'remarks' => 'Adoption payment for ' . $animalNames . ' (Booking #' . $bookingId . ')',
                        'type' => 'FPX Online Banking',
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo,
                        'userID' => Auth::id(),
                    ]);

                    // Create individual adoption record for EACH animal
                    foreach ($animalIds as $index => $animalId) {
                        $animal = Animal::find($animalId);
                        $animalName = $animal ? $animal->name : 'Unknown';

                        // Get individual fee if available, otherwise divide total equally
                        $individualFee = isset($feeBreakdowns[$animalId])
                            ? $feeBreakdowns[$animalId]
                            : ($adoptionFee / count($animalIds));

                        Adoption::create([
                            'fee' => $individualFee,
                            'remarks' => 'Adopted: ' . $animalName,
                            'bookingID' => $bookingId,
                            'transactionID' => $transaction->id,
                            'animalID' => $animalId, // Link to specific animal
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

                    // Update user role
                    $user = Auth::user();
                    if ($user->hasRole('public user')) {
                        $user->removeRole('public user');
                        $user->assignRole('adopter');
                    } elseif ($user->hasRole('caretaker')) {
                        if (!$user->hasRole('adopter')) {
                            $user->assignRole('adopter');
                        }
                    }

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
        } else {
            // Payment Failed/Pending - Booking remains in 'Confirmed' status
            if ($bookingId) {
                $booking = Booking::find($bookingId);

                if ($booking && strtolower($booking->status) == 'confirmed') {
                    Log::info('Payment Failed/Pending', [
                        'booking_id' => $bookingId,
                        'status_id' => $statusId,
                        'animal_ids' => $animalIds,
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo
                    ]);

                    // Create failed transaction record
                    Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Failed',
                        'remarks' => 'Failed adoption payment for ' . $animalNames . ' (Booking #' . $bookingId . ')',
                        'type' => 'Adoption Fee',
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo,
                        'userID' => Auth::id(),
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
                $booking = Booking::find($bookingId);

                if ($booking && $statusId == 1) {
                    // Payment successful - update booking to Completed
                    $booking->update(['status' => 'Completed']);

                    // Update all animals in this booking to Adopted
                    $animalIds = $booking->animals->pluck('id')->toArray();
                    foreach ($animalIds as $animalId) {
                        Animal::where('id', $animalId)->update(['adoption_status' => 'Adopted']);
                    }

                    // Check if transaction already exists
                    $existingTransaction = Transaction::where('bill_code', $billCode)->first();
                    if (!$existingTransaction) {
                        Transaction::create([
                            'amount' => $request->input('amount') / 100,
                            'status' => 'Success',
                            'remarks' => 'Adoption payment (Callback) - Booking #' . $bookingId . ' (' . count($animalIds) . ' animals)',
                            'date' => now(),
                            'type' => 'Adoption Fee',
                            'bill_code' => $billCode,
                            'reference_no' => $referenceNo,
                            'userID' => $booking->userID,
                        ]);
                    }

                    Log::info('Callback: Booking Completed', [
                        'booking_id' => $bookingId,
                        'animal_count' => count($animalIds),
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function getBillTransactions($billCode)
    {
        $url = 'https://dev.toyyibpay.com/index.php/api/getBillTransactions';

        $response = Http::withoutVerifying()->asForm()->post($url, [
            'billCode' => $billCode,
            'userSecretKey' => config('toyyibpay.key'),
        ]);

        return $response->json();
    }
}
