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
use App\Services\AuditService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\DatabaseErrorHandler;


class BookingAdoptionController extends Controller
{
    use DatabaseErrorHandler;

    /**
     * Helper method to manually load animals for bookings to avoid cross-database SQL joins
     * This loads animals through the pivot table, then attaches them to booking models
     */
    private function loadAnimalsForBookings($bookings, $loadImages = false)
    {
        if ($bookings->isEmpty()) {
            return;
        }

        // Check if shafiqah database is available
        if (!$this->isDatabaseAvailable('shafiqah')) {
            // Set empty animals collection for each booking
            $bookings->each(function($booking) {
                $booking->setRelation('animals', collect([]));
            });
            return;
        }

        // Get all unique animal IDs from all bookings' animalBookings
        $animalIds = $bookings->flatMap(function($booking) {
            return $booking->animalBookings->pluck('animalID');
        })->unique()->values()->toArray();

        if (empty($animalIds)) {
            $bookings->each(function($booking) {
                $booking->setRelation('animals', collect([]));
            });
            return;
        }

        // Load animals from shafiqah database
        $animalsQuery = Animal::whereIn('id', $animalIds)
            ->with(['medicals', 'vaccinations']);

        // Optionally load images if eilya is online
        if ($loadImages && $this->isDatabaseAvailable('eilya')) {
            $animalsQuery->with('images');
        }

        $animals = $animalsQuery->get()->keyBy('id');

        // Attach animals to each booking based on their animalBookings
        $bookings->each(function($booking) use ($animals) {
            $bookingAnimals = $booking->animalBookings->map(function($animalBooking) use ($animals) {
                $animal = $animals->get($animalBooking->animalID);
                if ($animal) {
                    // Attach pivot data to the animal
                    $animal->pivot = $animalBooking;
                }
                return $animal;
            })->filter(); // Remove nulls

            $booking->setRelation('animals', $bookingAnimals);
        });
    }

    /**
     * Helper method to manually load animals for a visit list to avoid cross-database SQL joins
     * This loads animal IDs from the pivot table, then fetches animals from shafiqah database
     */
    private function loadAnimalsForVisitList($visitList, $withRelations = [])
    {
        // Check if shafiqah database is available
        if (!$this->isDatabaseAvailable('shafiqah')) {
            return collect([]);
        }

        // Get animal IDs from the pivot table (on danish database)
        $animalIds = DB::connection('danish')
            ->table('visit_list_animal')
            ->where('listID', $visitList->id)
            ->pluck('animalID')
            ->toArray();

        if (empty($animalIds)) {
            return collect([]);
        }

        // Load animals from shafiqah database
        $query = Animal::whereIn('id', $animalIds);

        // Add any requested relationships
        if (!empty($withRelations)) {
            $query->with($withRelations);
        }

        return $query->get();
    }

    /**
     * Helper method to check if animals have active bookings at a specific date/time
     * Active bookings = Pending or Confirmed status
     *
     * CROSS-DATABASE SAFE: Manually queries danish database for bookings, then shafiqah for animals
     */
    private function getAnimalsWithBookingConflicts(array $animalIds, $appointmentDate, $appointmentTime)
    {
        // Step 1: Find all bookings at this date/time with Pending/Confirmed status (danish database)
        $conflictingBookings = Booking::where('appointment_date', $appointmentDate)
            ->where('appointment_time', $appointmentTime)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->with('user') // Load user for error messages
            ->get();

        if ($conflictingBookings->isEmpty()) {
            return collect([]);
        }

        // Step 2: Get animal IDs from pivot table for these bookings (danish database)
        $bookingIds = $conflictingBookings->pluck('id')->toArray();
        $conflictingAnimalIds = DB::connection('danish')
            ->table('animal_booking')
            ->whereIn('bookingID', $bookingIds)
            ->whereIn('animalID', $animalIds) // Only check animals in our input list
            ->pluck('animalID')
            ->unique()
            ->toArray();

        if (empty($conflictingAnimalIds)) {
            return collect([]);
        }

        // Step 3: Load animals from shafiqah database
        if (!$this->isDatabaseAvailable('shafiqah')) {
            return collect([]);
        }

        $animals = Animal::whereIn('id', $conflictingAnimalIds)->get();

        // Step 4: Attach booking information to each animal
        $animals->each(function($animal) use ($conflictingBookings, $bookingIds) {
            // Find which bookings this animal is in
            $animalBookingIds = DB::connection('danish')
                ->table('animal_booking')
                ->where('animalID', $animal->id)
                ->whereIn('bookingID', $bookingIds)
                ->pluck('bookingID')
                ->toArray();

            $animalBookings = $conflictingBookings->whereIn('id', $animalBookingIds);
            $animal->setRelation('bookings', $animalBookings);
        });

        return $animals;
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
            collect([]),
            'danish'
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

            // Manually load animals to avoid cross-database JOIN
            $withRelations = [];

            // Add images if eilya is online
            if ($this->isDatabaseAvailable('eilya')) {
                $withRelations[] = 'images';
            }

            // Add bookings if danish is online
            if ($this->isDatabaseAvailable('danish')) {
                $withRelations['bookings'] = function($query) use ($user) {
                    $query->where('userID', $user->id)
                        ->where('status', 'Pending')
                        ->latest();
                };
            }

            return $this->loadAnimalsForVisitList($visitList, $withRelations);
        }, collect([]), 'danish');

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
            null,
            'shafiqah'
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
            // Query pivot table directly to avoid cross-database JOIN
            $exists = DB::connection('danish')
                ->table('visit_list_animal')
                ->where('listID', $visitList->id)
                ->where('animalID', $animalId)
                ->exists();

            if ($exists) {
                return ['error' => 'This animal is already in your visit list.'];
            }
            // ===== END CHECK =====

            // Attach to pivot table directly (no JOIN needed for attach)
            DB::connection('danish')
                ->table('visit_list_animal')
                ->insert([
                    'listID' => $visitList->id,
                    'animalID' => $animalId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            return ['success' => "{$animal->name} has been added to your visit list. You can view the list at the top right"];
        }, ['error' => 'Database connection unavailable. Please try again later.'], 'danish');

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
        try {
            $user = Auth::user();

            $result = $this->safeQuery(function() use ($user, $animalId) {
                $visitList = $user->visitList;

                if (!$visitList) {
                    return ['error' => 'Visit list not found.'];
                }

                $visitList->animals()->detach($animalId);

                return ['success' => 'Animal removed from your visit list.'];
            }, ['error' => 'Database connection unavailable. Please try again later.'], 'danish');

            if (isset($result['error'])) {
                return back()->with('error', $result['error']);
            }

            return back()->with('success', $result['success']);

        } catch (\Exception $e) {
            \Log::error('Error removing animal from visit list: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'animal_id' => $animalId,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to remove animal from visit list: ' . $e->getMessage());
        }
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

            if (!$visitList) {
                \Log::warning('No visit list found for user', ['user_id' => $user->id]);
                return back()
                    ->with('error', 'Your visit list is empty.')
                    ->with('open_visit_modal', true);
            }

            // Get animal IDs from pivot table directly (avoid cross-database JOIN)
            $visitListAnimalIds = DB::connection('danish')
                ->table('visit_list_animal')
                ->where('listID', $visitList->id)
                ->pluck('animalID')
                ->toArray();

            if (empty($visitListAnimalIds)) {
                \Log::warning('Visit list is empty for user', ['user_id' => $user->id]);
                return back()
                    ->with('error', 'Your visit list is empty.')
                    ->with('open_visit_modal', true);
            }

            \Log::info('Found visit list', ['visit_list_id' => $visitList->id]);

            // Verify all selected animals are in the visit list
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
            // Step 1: Get all user's bookings at this date/time (danish database)
            $existingUserBookings = Booking::where('userID', $user->id)
                ->where('appointment_date', $appointmentDate)
                ->where('appointment_time', $appointmentTime)
                ->with('animalBookings') // Load pivot records
                ->get();

            if ($existingUserBookings->isNotEmpty()) {
                // Step 2: Get all animal IDs from these bookings' pivot tables
                $alreadyBookedAnimalIds = $existingUserBookings->flatMap(function ($booking) {
                    return $booking->animalBookings->pluck('animalID');
                })->unique()->toArray();

                // Step 3: Filter to only keep bookings that have animals in the requested list
                $existingUserBookings = $existingUserBookings->filter(function($booking) use ($requestedAnimalIds) {
                    $bookingAnimalIds = $booking->animalBookings->pluck('animalID')->toArray();
                    return !empty(array_intersect($bookingAnimalIds, $requestedAnimalIds));
                });

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

                // AUDIT: Booking created
                $animalNames = Animal::whereIn('id', $validated['animal_ids'])->pluck('name')->toArray();
                AuditService::log('payment', 'booking_created', [
                    'entity_type' => 'Booking',
                    'entity_id' => $booking->id,
                    'source_database' => 'danish',
                    'metadata' => [
                        'appointment_date' => $appointmentDate,
                        'appointment_time' => $appointmentTime,
                        'animal_ids' => $validated['animal_ids'],
                        'animal_names' => $animalNames,
                        'animal_count' => count($validated['animal_ids']),
                    ],
                ]);

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
                DB::connection('danish')
                    ->table('visit_list_animal')
                    ->where('listID', $visitList->id)
                    ->whereIn('animalID', $validated['animal_ids'])
                    ->delete();
                \Log::info('Removed animals from visit list');

                // If visit list has no more animals, delete it
                $remainingCount = DB::connection('danish')
                    ->table('visit_list_animal')
                    ->where('listID', $visitList->id)
                    ->count();

                if ($remainingCount === 0) {
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
            // Check which databases are available for conditional loading
            $shafiqahOnline = $this->isDatabaseAvailable('shafiqah');
            $eilyaOnline = $this->isDatabaseAvailable('eilya');
            $taufiqOnline = $this->isDatabaseAvailable('taufiq');

            // Build relationships array based on database availability
            $relationships = [];

            // Always try to load adoptions (same database)
            $relationships[] = 'adoptions';

            // Load user if taufiq database is online
            if ($taufiqOnline) {
                $relationships[] = 'user';
            }

            // Load animalBookings pivot data (same database - danish)
            // We load animals separately after to avoid cross-database SQL joins
            $relationships[] = 'animalBookings';

            $query = Booking::where('userID', Auth::id())
                ->with($relationships);

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('appointment_date', 'LIKE', "%{$search}%")
                      ->orWhere('remarks', 'LIKE', "%{$search}%");
                });
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->where('appointment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('appointment_date', '<=', $request->date_to);
            }

            $bookings = $query->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->paginate(40)
                ->appends($request->query());

            // Manually load animals for bookings to avoid cross-database SQL joins
            $this->loadAnimalsForBookings($bookings, $eilyaOnline);

            // Prevent lazy-loading by setting empty collections for unloaded relationships
            $bookings->each(function($booking) use ($taufiqOnline) {
                if (!$taufiqOnline && !$booking->relationLoaded('user')) {
                    $booking->setRelation('user', null);
                }
            });

            // Count statuses for this user only
            $statusCounts = Booking::where('userID', Auth::id())
                ->select('status', DB::connection('danish')->raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            // Calculate total of all bookings (unfiltered)
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts','totalBookings');
        }, [
            'bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40),
            'statusCounts' => collect([]),
            'totalBookings' => 0
        ], 'danish'); // Pre-check danish database

        return view('booking-adoption.main', $result);
    }

    /**
     * Display a listing of all bookings for admin.
     */
    public function indexAdmin(Request $request)
    {
        $result = $this->safeQuery(function() use ($request) {
            // Check which databases are available for conditional loading
            $shafiqahOnline = $this->isDatabaseAvailable('shafiqah');
            $eilyaOnline = $this->isDatabaseAvailable('eilya');
            $taufiqOnline = $this->isDatabaseAvailable('taufiq');

            // Build relationships array based on database availability
            $relationships = [];

            // Always try to load adoptions (same database)
            $relationships[] = 'adoptions';

            // Load user if taufiq database is online
            if ($taufiqOnline) {
                $relationships[] = 'user';
            }

            // Load animalBookings pivot data (same database - danish)
            // We load animals separately after to avoid cross-database SQL joins
            $relationships[] = 'animalBookings';

            $query = Booking::with($relationships);

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by user name or email (cross-database search)
            if ($request->filled('user_search') && $taufiqOnline) {
                $userSearch = $request->user_search;

                // Get user IDs from taufiq database that match search
                $userIds = DB::connection('taufiq')
                    ->table('users')
                    ->where(function($q) use ($userSearch) {
                        $q->where('name', 'LIKE', "%{$userSearch}%")
                          ->orWhere('email', 'LIKE', "%{$userSearch}%");
                    })
                    ->pluck('id')
                    ->toArray();

                if (!empty($userIds)) {
                    $query->whereIn('userID', $userIds);
                } else {
                    // No users found, return empty result
                    $query->whereRaw('1 = 0');
                }
            }

            // Search by booking ID
            if ($request->filled('booking_id')) {
                $query->where('id', $request->booking_id);
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->where('appointment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('appointment_date', '<=', $request->date_to);
            }

            $bookings = $query->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->paginate(40)
                ->appends($request->query());

            // Manually load animals for bookings to avoid cross-database SQL joins
            $this->loadAnimalsForBookings($bookings, $eilyaOnline);

            // Prevent lazy-loading by setting empty collections for unloaded relationships
            $bookings->each(function($booking) use ($taufiqOnline) {
                if (!$taufiqOnline && !$booking->relationLoaded('user')) {
                    $booking->setRelation('user', null);
                }
            });

            $statusCounts = Booking::select('status', DB::connection('danish')->raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            // Calculate total of all bookings (unfiltered)
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts', 'totalBookings');
        }, [
            'bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40),
            'statusCounts' => collect([]),
            'totalBookings' => 0
        ], 'danish'); // Pre-check danish database

        return view('booking-adoption.admin', $result);
    }

    // Cancel a booking
    public function cancel(Booking $booking)
    {
        try {
            if ($booking->userID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

            if (in_array($booking->status, ['Pending', 'Confirmed'])) {
                $booking->update(['status' => 'Cancelled']);

                \Log::info('Booking cancelled', [
                    'booking_id' => $booking->id,
                    'user_id' => Auth::id(),
                    'previous_status' => $booking->getOriginal('status')
                ]);

                return redirect()->route('booking:main')->with('success', 'Booking cancelled successfully!');
            }

            return redirect()->route('booking:main')->with('error', 'Cannot cancel this booking. Current status: ' . $booking->status);

        } catch (\Exception $e) {
            \Log::error('Error cancelling booking: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('booking:main')->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    public function showAdoptionFee(Booking $booking, Request $request)
    {
        try {
            // Authorization
            if ($booking->userID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

            // Get animal IDs from pivot table directly (avoid cross-database JOIN)
            $bookingAnimalIds = DB::connection('danish')
                ->table('animal_booking')
                ->where('bookingID', $booking->id)
                ->pluck('animalID')
                ->toArray();

            // Get selected animals from request (default to all booking animals)
            $animalIds = $request->query('animal_ids', $bookingAnimalIds);

            // Load animals from shafiqah database
            $animals = collect([]);
            if ($this->isDatabaseAvailable('shafiqah')) {
                $animals = Animal::whereIn('id', $animalIds)
                    ->with(['medicals', 'vaccinations']) // eager load
                    ->get();
            }

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

            // Retrieve booking (don't use 'with' to avoid cross-database JOIN)
            $booking = Booking::with('animalBookings')->findOrFail($bookingId);

            // Manually load animals to avoid cross-database JOIN
            $this->loadAnimalsForBookings(collect([$booking]), false);

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

            // AUDIT: Booking confirmed
            AuditService::logPayment(
                'booking_confirmed',
                $bookingId,
                $validated['total_fee'],
                $validated['animal_ids'],
                null, // Bill code not yet generated
                'success'
            );

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
        try {
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
                // If bill creation fails, revert booking status back to Confirmed
                $booking->update(['status' => 'Confirmed']);

                Log::error('Bill Creation Failed', [
                    'booking_id' => $booking->id,
                    'response' => $data,
                ]);

                return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment. Please try again.']);
            }

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Request Error creating bill: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('booking:main')->withErrors(['error' => 'Payment gateway connection error. Please try again later.']);
        } catch (\Exception $e) {
            Log::error('Error creating bill: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()]);
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
                    // Start transactions for cross-database updates
                    DB::connection('danish')->beginTransaction();
                    DB::connection('shafiqah')->beginTransaction();

                    // Check if atiqah (slots) database is available
                    $atiqahOnline = $this->isDatabaseAvailable('atiqah');
                    if ($atiqahOnline) {
                        DB::connection('atiqah')->beginTransaction();
                    }

                    try {
                        // Update booking status to Completed
                        $booking->update(['status' => 'Completed']);

                        // AUDIT: Payment completed
                        AuditService::logPayment(
                            'payment_completed',
                            $bookingId,
                            $adoptionFee,
                            $animalIds,
                            $billCode,
                            'success'
                        );

                        // Track affected slots for status update
                        $affectedSlotIds = [];

                        // Update all selected animals to Adopted and clear their slots
                        foreach ($animalIds as $animalId) {
                            $animal = Animal::find($animalId);
                            if ($animal) {
                                $oldStatus = $animal->adoption_status;
                                $oldSlotId = $animal->slotID;

                                // Update adoption status and clear slot
                                Animal::where('id', $animalId)->update([
                                    'adoption_status' => 'Adopted',
                                    'slotID' => null // Animal leaves the shelter
                                ]);

                                // Track slot for status recalculation
                                if ($oldSlotId) {
                                    $affectedSlotIds[] = $oldSlotId;
                                }

                                // AUDIT: Animal adoption status changed
                                AuditService::logAnimal(
                                    'adoption_status_changed',
                                    $animalId,
                                    $animal->name,
                                    ['adoption_status' => $oldStatus, 'slotID' => $oldSlotId],
                                    ['adoption_status' => 'Adopted', 'slotID' => null],
                                    [
                                        'booking_id' => $bookingId,
                                        'adopter_id' => Auth::id(),
                                        'adopter_name' => Auth::user()->name,
                                        'adoption_fee' => $adoptionFee / count($animalIds),
                                    ]
                                );
                            }
                        }

                        // Update slot statuses for affected slots (if atiqah is online)
                        if ($atiqahOnline && !empty($affectedSlotIds)) {
                            $uniqueSlotIds = array_unique($affectedSlotIds);
                            foreach ($uniqueSlotIds as $slotId) {
                                $slot = Slot::find($slotId);
                                if ($slot) {
                                    // Count remaining animals (after adoption)
                                    $remainingAnimals = Animal::where('slotID', $slotId)->count();

                                    // Recalculate status
                                    if ($remainingAnimals >= $slot->capacity) {
                                        $slot->status = 'occupied';
                                    } else {
                                        $slot->status = 'available';
                                    }
                                    $slot->save();

                                    \Log::info('Slot status updated after adoption', [
                                        'slot_id' => $slotId,
                                        'remaining_animals' => $remainingAnimals,
                                        'capacity' => $slot->capacity,
                                        'new_status' => $slot->status,
                                    ]);
                                }
                            }
                        }

                        // Commit all transactions
                        DB::connection('danish')->commit();
                        DB::connection('shafiqah')->commit();
                        if ($atiqahOnline) {
                            DB::connection('atiqah')->commit();
                        }

                    } catch (\Exception $e) {
                        // Rollback all transactions
                        DB::connection('danish')->rollBack();
                        DB::connection('shafiqah')->rollBack();
                        if ($atiqahOnline) {
                            DB::connection('atiqah')->rollBack();
                        }
                        throw $e;
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

                        $adoption = Adoption::create([
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

                        // AUDIT: Adoption finalized
                        AuditService::log('payment', 'adoption_completed', [
                            'entity_type' => 'Adoption',
                            'entity_id' => $adoption->id,
                            'source_database' => 'danish',
                            'metadata' => [
                                'animal_id' => $animalId,
                                'animal_name' => $animalName,
                                'adoption_fee' => $individualFee,
                                'booking_id' => $bookingId,
                                'transaction_id' => $transaction->id,
                                'adopter_id' => Auth::id(),
                                'adopter_name' => Auth::user()->name,
                            ],
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

                    // AUDIT: Payment failed
                    AuditService::logPayment(
                        'payment_failed',
                        $bookingId,
                        $adoptionFee,
                        $animalIds,
                        $billCode,
                        'failure'
                    );
                }
            }
        }

        // Store payment status data in session to show in modal
        session()->flash('payment_status', [
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

        // Redirect to booking main page with modal flag
        return redirect()->route('booking:main')->with('show_payment_modal', true);
    }

    public function callback(Request $request)
    {
        try {
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
                        // Start transaction for cross-database updates
                        DB::connection('danish')->beginTransaction();
                        DB::connection('shafiqah')->beginTransaction();

                        // Check if atiqah (slots) database is available
                        $atiqahOnline = $this->isDatabaseAvailable('atiqah');
                        if ($atiqahOnline) {
                            DB::connection('atiqah')->beginTransaction();
                        }

                        try {
                            // Payment successful - update booking to Completed
                            $booking->update(['status' => 'Completed']);

                            // Track affected slots for status update
                            $affectedSlotIds = [];

                            // Update all animals in this booking to Adopted and clear their slots
                            $animalIds = $booking->animals->pluck('id')->toArray();
                            foreach ($animalIds as $animalId) {
                                $animal = Animal::find($animalId);
                                if ($animal) {
                                    $oldSlotId = $animal->slotID;

                                    // Update adoption status and clear slot
                                    Animal::where('id', $animalId)->update([
                                        'adoption_status' => 'Adopted',
                                        'slotID' => null // Animal leaves the shelter
                                    ]);

                                    // Track slot for status recalculation
                                    if ($oldSlotId) {
                                        $affectedSlotIds[] = $oldSlotId;
                                    }
                                }
                            }

                            // Update slot statuses for affected slots (if atiqah is online)
                            if ($atiqahOnline && !empty($affectedSlotIds)) {
                                $uniqueSlotIds = array_unique($affectedSlotIds);
                                foreach ($uniqueSlotIds as $slotId) {
                                    $slot = Slot::find($slotId);
                                    if ($slot) {
                                        // Count remaining animals (after adoption)
                                        $remainingAnimals = Animal::where('slotID', $slotId)->count();

                                        // Recalculate status
                                        if ($remainingAnimals >= $slot->capacity) {
                                            $slot->status = 'occupied';
                                        } else {
                                            $slot->status = 'available';
                                        }
                                        $slot->save();

                                        Log::info('Callback: Slot status updated after adoption', [
                                            'slot_id' => $slotId,
                                            'remaining_animals' => $remainingAnimals,
                                            'capacity' => $slot->capacity,
                                            'new_status' => $slot->status,
                                        ]);
                                    }
                                }
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

                            DB::connection('danish')->commit();
                            DB::connection('shafiqah')->commit();
                            if ($atiqahOnline) {
                                DB::connection('atiqah')->commit();
                            }

                            Log::info('Callback: Booking Completed', [
                                'booking_id' => $bookingId,
                                'animal_count' => count($animalIds),
                                'affected_slots' => count($affectedSlotIds),
                            ]);

                        } catch (\Exception $e) {
                            DB::connection('danish')->rollBack();
                            DB::connection('shafiqah')->rollBack();
                            if ($atiqahOnline) {
                                DB::connection('atiqah')->rollBack();
                            }
                            throw $e;
                        }
                    }
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('ToyyibPay Callback Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Callback processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getBillTransactions($billCode)
    {
        try {
            $url = 'https://dev.toyyibpay.com/index.php/api/getBillTransactions';

            $response = Http::withoutVerifying()->asForm()->post($url, [
                'billCode' => $billCode,
                'userSecretKey' => config('toyyibpay.key'),
            ]);

            if ($response->failed()) {
                Log::error('Failed to fetch bill transactions', [
                    'bill_code' => $billCode,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }

            return $response->json();

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Request Error fetching bill transactions: ' . $e->getMessage(), [
                'bill_code' => $billCode,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching bill transactions: ' . $e->getMessage(), [
                'bill_code' => $billCode,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}
