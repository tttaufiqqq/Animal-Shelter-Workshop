<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\Booking;
use App\Models\AnimalBooking;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait ManagesBookings
{
    public function index(Request $request)
    {
        $result = $this->safeQuery(function() use ($request) {
            $taufiqOnline = $this->isDatabaseAvailable('users');
            $relationships = ['adoptions', 'animalBookings'];
            if ($taufiqOnline) $relationships[] = 'user';

            $query = Booking::where('userID', Auth::id())->with($relationships);

            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(fn($q) => $q->where('id', 'LIKE', "%{$search}%")->orWhere('appointment_date', 'LIKE', "%{$search}%")->orWhere('remarks', 'LIKE', "%{$search}%"));
            }
            if ($request->filled('date_from')) $query->where('appointment_date', '>=', $request->date_from);
            if ($request->filled('date_to')) $query->where('appointment_date', '<=', $request->date_to);

            $bookings = $query->orderBy('created_at', 'desc')->paginate(40)->appends($request->query());

            $this->loadAnimalsForBookings($bookings, true);
            $bookings->each(function($booking) use ($taufiqOnline) {
                if (!$taufiqOnline && !$booking->relationLoaded('user')) $booking->setRelation('user', null);
            });

            $statusCounts = Booking::where('userID', Auth::id())->select('status', DB::connection('booking')->raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts', 'totalBookings');
        }, ['bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40), 'statusCounts' => collect([]), 'totalBookings' => 0], 'booking');

        return view('booking-adoption.main', $result);
    }

    public function indexAdmin(Request $request)
    {
        $result = $this->safeQuery(function() use ($request) {
            $taufiqOnline = $this->isDatabaseAvailable('users');
            $relationships = ['adoptions', 'animalBookings'];
            if ($taufiqOnline) $relationships[] = 'user';

            $query = Booking::with($relationships);

            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('user_search') && $taufiqOnline) {
                $userSearch = $request->user_search;
                $userIds = DB::connection('users')->table('users')->where(fn($q) => $q->where('name', 'LIKE', "%{$userSearch}%")->orWhere('email', 'LIKE', "%{$userSearch}%"))->pluck('id')->toArray();
                $query->when(!empty($userIds), fn($q) => $q->whereIn('userID', $userIds), fn($q) => $q->whereRaw('1 = 0'));
            }
            if ($request->filled('booking_id')) $query->where('id', $request->booking_id);
            if ($request->filled('date_from')) $query->where('appointment_date', '>=', $request->date_from);
            if ($request->filled('date_to')) $query->where('appointment_date', '<=', $request->date_to);

            $bookings = $query->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc')->paginate(40)->appends($request->query());

            $this->loadAnimalsForBookings($bookings, true);
            $bookings->each(function($booking) use ($taufiqOnline) {
                if (!$taufiqOnline && !$booking->relationLoaded('user')) $booking->setRelation('user', null);
            });

            $statusCounts = Booking::select('status', DB::connection('booking')->raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts', 'totalBookings');
        }, ['bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40), 'statusCounts' => collect([]), 'totalBookings' => 0], 'booking');

        return view('booking-adoption.admin', $result);
    }

    public function cancel(Booking $booking)
    {
        try {
            if ($booking->userID != Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

            if (in_array($booking->status, ['Pending', 'Confirmed'])) {
                $booking->update(['status' => 'Cancelled']);
                return redirect()->route('booking:main')->with('success', 'Booking cancelled successfully!');
            }

            return redirect()->route('booking:main')->with('error', 'Cannot cancel this booking. Current status: ' . $booking->status);

        } catch (\Exception $e) {
            \Log::error('Error cancelling booking: ' . $e->getMessage(), ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('booking:main')->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    public function showAdoptionFee(Booking $booking, Request $request)
    {
        try {
            if ($booking->userID != Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

            $bookingAnimalIds = DB::connection('booking')->table('animal_booking')->where('bookingID', $booking->id)->pluck('animalID')->toArray();
            $animalIds = $request->query('animal_ids', $bookingAnimalIds);

            $animals = collect([]);
            if ($this->isDatabaseAvailable('animals')) {
                $animals = Animal::whereIn('id', $animalIds)->with(['medicals', 'vaccinations'])->get();
            }

            $totalFee = 0;
            $allFeeBreakdowns = [];

            foreach ($animals as $animal) {
                $feeBreakdown = $this->calculateAdoptionFee($animal, $animal->medicals ?? collect(), $animal->vaccinations ?? collect());
                $totalFee += $feeBreakdown['total_fee'];
                $allFeeBreakdowns[$animal->id] = $feeBreakdown;
            }

            return view('booking-adoption.main', compact('booking', 'animals', 'allFeeBreakdowns', 'totalFee'));

        } catch (\Throwable $e) {
            \Log::error('Show Adoption Fee Error: BookingID=' . $booking->id . ' | ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->ajax()) return response()->json(['error' => 'Failed to load adoption fee details.'], 500);
            abort(500, 'Failed to load adoption fee details.');
        }
    }

    public function confirm(Request $request, $bookingId)
    {
        Log::info('Confirm Booking - Raw Request Input', ['booking_id' => $bookingId, 'input' => $request->all(), 'user' => auth()->id()]);

        try {
            $validated = $request->validate([
                'animal_ids' => 'required|array|min:1',
                'animal_ids.*' => 'required|exists:animals.animal,id',
                'total_fee' => 'required|numeric|min:0',
                'agree_terms' => 'required|accepted',
            ], [
                'animal_ids.required' => 'Please select at least one animal.',
                'animal_ids.min' => 'Please select at least one animal.',
                'agree_terms.required' => 'You must agree to the terms.',
                'agree_terms.accepted' => 'You must agree to the terms.',
            ]);

            $booking = Booking::with('animalBookings')->findOrFail($bookingId);
            $this->loadAnimalsForBookings(collect([$booking]), false);

            $currentStatus = strtolower($booking->status);
            if ($currentStatus !== 'pending' && $currentStatus !== 'confirmed') {
                return back()->with('error', 'This booking cannot be confirmed. Current status: ' . $booking->status);
            }

            $bookingAnimalIDs = AnimalBooking::where('animal_booking.bookingID', $bookingId)->pluck('animal_booking.animalID')->toArray();

            foreach ($validated['animal_ids'] as $chosen) {
                if (!in_array($chosen, $bookingAnimalIDs)) {
                    return back()->with('error', 'Invalid animal selection for this booking.');
                }
            }

            $selectedAnimals = Animal::whereIn('id', $validated['animal_ids'])->get();
            $animalNames = $selectedAnimals->pluck('name')->toArray();

            $booking->update(['totalFee' => $validated['total_fee'], 'status' => 'Confirmed']);

            AuditService::logPayment('booking_confirmed', $bookingId, $validated['total_fee'], $validated['animal_ids'], null, 'success');

            session(['booking_id' => $booking->id, 'adoption_fee' => $validated['total_fee'], 'animal_ids' => $validated['animal_ids'], 'animal_names' => implode(', ', $animalNames)]);

            return $this->createBill($booking, $validated['total_fee'], $selectedAnimals);

        } catch (\Exception $e) {
            Log::error("Booking Confirmation Error: {$e->getMessage()}", ['booking_id' => $bookingId, 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to confirm booking: ' . $e->getMessage() . ' [' . get_class($e) . ']');
        }
    }

    public function createBill(Booking $booking, $adoptionFee, $selectedAnimals)
    {
        try {
            $user = Auth::user();
            $animalNames = $selectedAnimals->pluck('name')->toArray();
            $animalCount = count($animalNames);

            if ($animalCount === 1) {
                $billName = 'Adopt ' . substr($animalNames[0], 0, 20);
                $billDescription = 'Adoption fee for ' . $animalNames[0] . ' (Booking #' . $booking->id . ')';
            } else {
                $billName = 'Adopt ' . $animalCount . ' Animals';
                $billDescription = 'Adoption fee for ' . implode(', ', array_slice($animalNames, 0, 3)) . ($animalCount > 3 ? ' and ' . ($animalCount - 3) . ' more' : '') . ' (Booking #' . $booking->id . ')';
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
                session(['bill_code' => $billCode, 'reference_no' => $referenceNo]);
                return redirect('https://dev.toyyibpay.com/' . $billCode);
            }

            $booking->update(['status' => 'Confirmed']);
            return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment. Please try again.']);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Request Error creating bill: ' . $e->getMessage(), ['booking_id' => $booking->id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('booking:main')->withErrors(['error' => 'Payment gateway connection error. Please try again later.']);
        } catch (\Exception $e) {
            Log::error('Error creating bill: ' . $e->getMessage(), ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()]);
        }
    }
}
