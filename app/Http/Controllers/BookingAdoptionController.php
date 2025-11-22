<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Slot;
use App\Models\Inventory;
use App\Models\Animal;
use App\Models\Image;
use App\Models\Category;
use App\Models\Rescue;
use App\Models\Clinic;
use App\Models\Vet;
use App\Models\Medical;
use App\Models\Vaccination;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\Adoption;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class BookingAdoptionController extends Controller
{
    public function userBookings()
    {
        $bookings = Booking::with('animals')
            ->where('userID', auth()->id())
            ->orderBy('appointment_date', 'desc')->get();
        return view('booking-adoption.main', compact('bookings'));
    }

    public function addToVisitList($animalId) //book multiple animals to adopt
    {
        $list = session()->get('visit_list', []);

        if (!in_array($animalId, $list)) {
            $list[] = $animalId;
        }

        session()->put('visit_list', $list);

        return back()->with('success', 'Animal added to your visit list.');
    }

    public function indexList()
    {
        $list = session()->get('visit_list', []);
        $animals = Animal::whereIn('id', $list)->get();

        return view('booking-adoption.visit-list', compact('animals'));
    }

    // Add animal to visit list
    public function addList($animalId)
    {
        $list = session()->get('visit_list', []);

        if (!in_array($animalId, $list)) {
            $list[] = $animalId;
        }

        session()->put('visit_list', $list);

        return back()->with('success', 'Animal added to your visit list. View the list in the main animal page.');
    }

    // Remove animal from visit list
    public function removeList($animalId)
    {
        $list = session()->get('visit_list', []);

        $list = array_filter($list, function ($id) use ($animalId) {
            return $id != $animalId;
        });

        session()->put('visit_list', $list);

        return back()->with('success', 'Animal removed from visit list.');
    }


    public function storeBooking(Request $request) //store booking wiith multiple animals
    {
        $request->validate([
            'animal_ids' => 'required|array|min:1',
            'animal_ids.*' => 'exists:animal,id',
            'appointment_date' => 'required|date|after:now',
            'terms' => 'required|accepted',
        ]);

        $dateTime = Carbon::parse($request->appointment_date);

        $booking = Booking::create([
            'userID' => auth()->id(),
            'appointment_date' => $dateTime->toDateString(),
            'appointment_time' => $dateTime->toTimeString(),
            'status' => 'Pending',
            'notes' => $request->notes,
        ]);

        $pivotData = [];

        foreach ($request->animal_ids as $animalId) {
            $pivotData[$animalId] = [
                'remarks' => $request->remarks[$animalId] ?? null
            ];
        }

        $booking->animals()->attach($pivotData);

        session()->forget('visit_list');

        return redirect()->route('visit.list')->with('success', 'Appointment booked successfully!');
    }


    public function index()
    {
        $bookings = Booking::where('userID', Auth::id())
            ->with([
                'animals.images',       // For displaying animal photos
                'animals.medicals',     // For calculating medical fees
                'animals.vaccinations', // For calculating vaccination fees
                'user',                 // For booker information
                'adoptions'             // For showing adoption status
            ])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(6);

        // Count statuses for this user only
        $statusCounts = Booking::where('userID', Auth::id())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('booking-adoption.main', compact('bookings', 'statusCounts'));
    }

    /**
     * Display a listing of all bookings for admin.
     */
    public function indexAdmin()
    {
        $bookings = Booking::with([
            'animals.images',
            'animals.medicals',
            'animals.vaccinations',
            'user',
            'adoptions'
        ])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(6);

        $statusCounts = Booking::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('booking-adoption.admin', compact('bookings', 'statusCounts'));
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

    // Confirm adoption of a specific animal in a booking
    public function confirm(Request $request, Booking $booking)
    {
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'animal_id' => 'required|exists:animal,id',
        ]);

        $animalId = $request->animal_id;

        // Make sure the animal belongs to this booking
        if (!$booking->animals->contains('id', $animalId)) {
            return redirect()->route('booking:main')->with('error', 'Selected animal does not belong to this booking.');
        }

        // Case-insensitive status check
        $currentStatus = strtolower($booking->status);
        if (!in_array($currentStatus, ['pending', 'confirmed'])) {
            return redirect()->route('booking:main')->with('error', 'Cannot confirm this booking.');
        }

        // Load the selected animal
        $animal = $booking->animals()->where('id', $animalId)->first();
        if (!$animal) {
            return redirect()->route('booking:main')->with('error', 'Animal not found.');
        }

        // Fetch medical and vaccination records
        $medicalRecords = Medical::where('animalID', $animal->id)->get();
        $vaccinationRecords = Vaccination::where('animalID', $animal->id)->get();

        // Calculate adoption fee
        $feeBreakdown = $this->calculateAdoptionFee($animal, $medicalRecords, $vaccinationRecords);

        // Optionally, mark this animal as adopted (if you have a pivot column like `status` in booking_animal table)
        $booking->animals()->updateExistingPivot($animal->id, ['status' => 'Confirmed']);

        // Update booking status to confirmed if not already
        if ($currentStatus === 'pending') {
            $booking->update(['status' => 'Confirmed']);
        }

        // Store session info for checkout/payment
        session([
            'booking_id' => $booking->id,
            'adoption_fee' => $feeBreakdown['total_fee'],
            'animal_name' => $animal->name,
        ]);

        // Create bill / redirect to payment page
        return $this->createBill($booking, $feeBreakdown['total_fee']);
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
                $medicalRecords = $animal->medicalRecords ?? collect();
                $vaccinationRecords = $animal->vaccinations ?? collect();

                // Calculate fee per animal
                $feeBreakdown = $this->calculateAdoptionFee($animal, $medicalRecords, $vaccinationRecords);

                $totalFee += $feeBreakdown['total_fee'];
                $allFeeBreakdowns[$animal->id] = $feeBreakdown;
            }

            // Return view with all animals and fees
            return view('booking-adoption.adoption', compact('booking', 'animals', 'allFeeBreakdowns', 'totalFee'));

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

    protected function calculateAdoptionFee($animal, $medicalRecords, $vaccinationRecords): array
    {
        // Base adoption fee (can be customized per species)
        $speciesBaseFees = [
            'dog' => 150,
            'cat' => 120,
            // Add other species if needed
        ];

        $species = strtolower($animal->species);
        $baseFee = $speciesBaseFees[$species] ?? 100; // default RM 100 if species not listed

        // Medical fee
        $medicalRate = 20; // RM 20 per medical record
        $medicalCount = $medicalRecords->count();
        $medicalFee = $medicalCount * $medicalRate;

        // Vaccination fee
        $vaccinationRate = 15; // RM 15 per vaccination
        $vaccinationCount = $vaccinationRecords->count();
        $vaccinationFee = $vaccinationCount * $vaccinationRate;

        // Total fee
        $totalFee = $baseFee + $medicalFee + $vaccinationFee;

        // Return detailed breakdown
        return [
            'animal_id' => $animal->id,
            'animal_name' => $animal->name,
            'animal_species' => $animal->species,
            'base_fee' => $baseFee,
            'medical_rate' => $medicalRate,
            'medical_count' => $medicalCount,
            'medical_fee' => $medicalFee,
            'vaccination_rate' => $vaccinationRate,
            'vaccination_count' => $vaccinationCount,
            'vaccination_fee' => $vaccinationFee,
            'total_fee' => $totalFee,
        ];
    }

    public function createBill(Booking $booking, $adoptionFee)
    {
        $user = Auth::user();
        $animalName = $booking->animal->name;
        $referenceNo = 'BOOKING-' . $booking->id . '-' . time();

        $option = [
            'userSecretKey' => config('toyyibpay.key'),
            'categoryCode' => config('toyyibpay.category'),
            'billName' => 'Adopt ' . substr($animalName, 0, 20),
            'billDescription' => 'Adoption fee for ' . $animalName . ' (Booking #' . $booking->id . ')',
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => ($adoptionFee) * 100,
            'billReturnUrl' => route('toyyibpay-status'),
            'billCallbackUrl' => route('toyyibpay-callback'),
            'billExternalReferenceNo' => $referenceNo, // Store this
            'billTo' => $user->name,
            'billEmail' => $user->email,
            'billPhone' => $user->phone ?? '0000000000',
            'billSplitPayment' => 0,
            'billPaymentChannel' => 0,
            'billChargeToCustomer' => 1,
            'billContentEmail' => 'Thank you for adopting ' . $animalName . '!',
        ];

        $url = 'https://dev.toyyibpay.com/index.php/api/createBill';
        $response = Http::withoutVerifying()->asForm()->post($url, $option);
        $data = $response->json();

        if (isset($data[0]['BillCode'])) {
            $billCode = $data[0]['BillCode'];

            // Store both bill code and reference number in session
            session([
                'bill_code' => $billCode,
                'reference_no' => $referenceNo
            ]);

            return redirect('https://dev.toyyibpay.com/' . $billCode);
        } else {
            $booking->update(['status' => 'Pending']);
            return redirect()->route('booking.main')->withErrors(['error' => 'Failed to create payment. Please try again.']);
        }
    }

    public function paymentStatus(Request $request)
    {
        $statusId = $request->input('status_id');
        $billCode = $request->input('billcode');
        $orderId = $request->input('order_id');

        $bookingId = session('booking_id');
        $adoptionFee = session('adoption_fee');
        $animalName = session('animal_name');
        $referenceNo = session('reference_no'); // Get reference number from session

        $paymentStatus = $this->getBillTransactions($billCode);

        if ($statusId == 1) {
            if ($bookingId) {
                $booking = Booking::find($bookingId);

                if ($booking) {
                    $booking->update(['status' => 'Completed']);
                    $booking->animal->update(['adoption_status' => 'Adopted']);

                    $transaction = Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Success',
                        'remarks' => 'Adoption payment for ' . $animalName . ' (Booking #' . $bookingId . ')',
                        'type' => 'FPX Online Banking',
                        'bill_code' => $billCode, // Store bill code
                        'reference_no' => $referenceNo, // Store reference number
                        'userID' => Auth::id(),
                    ]);

                    Adoption::create([
                        'fee' => $adoptionFee,
                        'remarks' => $animalName . ' Adopted',
                        'bookingID' => $bookingId,
                        'transactionID' => $transaction->id,
                    ]);

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
                    session()->forget(['booking_id', 'adoption_fee', 'animal_name', 'bill_code', 'reference_no']);

                    Log::info('Payment Success', [
                        'booking_id' => $bookingId,
                        'amount' => $adoptionFee,
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo
                    ]);
                }
            }
        } else {
            if ($bookingId) {
                $booking = Booking::find($bookingId);

                if ($booking && strtolower($booking->status) == 'confirmed') {
                    Log::info('Payment Failed/Pending', [
                        'booking_id' => $bookingId,
                        'status_id' => $statusId,
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo
                    ]);

                    // Store bill code and reference for failed transactions too
                    Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Failed',
                        'remarks' => 'Failed adoption payment for ' . $animalName . ' (Booking #' . $bookingId . ')',
                        'type' => 'Adoption Fee',
                        'bill_code' => $billCode, // Store bill code
                        'reference_no' => $referenceNo, // Store reference number
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
            'animal_name' => $animalName,
            'reference_no' => $referenceNo,
            'payment_details' => $paymentStatus,
        ]);
    }

    public function callback(Request $request)
    {
        Log::info('ToyyibPay Callback:', $request->all());

        $billCode = $request->input('billcode');
        $statusId = $request->input('status_id');
        $referenceNo = $request->input('refno'); // This is your reference number

        if (strpos($referenceNo, 'BOOKING-') !== false) {
            $parts = explode('-', $referenceNo);
            if (isset($parts[1])) {
                $bookingId = $parts[1];
                $booking = Booking::find($bookingId);

                if ($booking && $statusId == 1) {
                    $booking->update(['status' => 'Completed']);

                    // Check if transaction already exists using bill_code
                    $existingTransaction = Transaction::where('bill_code', $billCode)->first();
                    if (!$existingTransaction) {
                        Transaction::create([
                            'amount' => $request->input('amount') / 100,
                            'status' => 'Success',
                            'remarks' => 'Adoption payment (Callback) - Booking #' . $bookingId,
                            'date' => now(),
                            'type' => 'Adoption Fee',
                            'bill_code' => $billCode, // Store bill code
                            'reference_no' => $referenceNo, // Store reference number
                            'userID' => $booking->userID,
                        ]);
                    }
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





    public function showModalAdmin($id)
    {
        try {
            $booking = Booking::with(['animal.images', 'user'])
                ->where('id', $id)
                ->firstOrFail();

            return view('booking-adoption.show-admin', compact('booking'));

        } catch (\Exception $e) {
            Log::error('Admin Booking modal error for ID ' . $id . ': ' . $e->getMessage());

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => 'Booking Not Found',
                    'message' => 'The requested booking ID (' . $id . ') does not exist.'
                ], 404);
            }

            return response()->json([
                'error' => 'Failed to load booking details',
                'message' => 'An internal server error occurred.'
            ], 500);
        }
    }
}
