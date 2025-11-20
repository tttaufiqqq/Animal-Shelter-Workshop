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

    public function storeBooking(Request $request)
    {
        $request->validate([
            'animalID' => 'required|exists:animal,id',
            'appointment_date' => 'required|date|after:now',
            'terms' => 'required|accepted',
        ]);

        $dateTime = Carbon::parse($request->appointment_date);

        // Check if user already has a pending booking
        $existingBooking = Booking::where('userID', auth()->id())
            ->where('animalID', $request->animalID)
            ->where('status', 'Pending')
            ->first();

        if ($existingBooking) {
            return redirect()->back()->with('error', 'You already have a pending booking for this animal.');
        }

        Booking::create([
            'userID' => auth()->id(),
            'animalID' => $request->animalID,
            'appointment_date' => $dateTime->toDateString(),
            'appointment_time' => $dateTime->toTimeString(),
            'status' => 'Pending',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Adoption appointment booked successfully! We will contact you to confirm the details.');
    }

    public function index()
    {
        $bookings = Booking::where('userID', Auth::id())
            ->with(['animal', 'adoption'])
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


    public function indexAdmin()
    {
        $bookings = Booking::with(['animal', 'adoption', 'user'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(6);
            
        $statusCounts = Booking::select('status', DB::raw('COUNT(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status'); 

        return view('booking-adoption.admin', compact('bookings', 'statusCounts'));
    }
   
    public function show(Booking $booking)
    {
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $booking->load(['animals', 'adoption']);
        
        return view('booking-adoption.show', compact('booking'));
    }

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

    public function confirm(Booking $booking, Request $request)
    {
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Case-insensitive status check
        $currentStatus = strtolower($booking->status);
        if (!in_array($currentStatus, ['pending', 'confirmed'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot confirm this booking.'
                ], 400);
            }
            return redirect()->route('booking:main')->with('error', 'Cannot confirm this booking.');
        }

        $booking->load('animal');
        $medicalRecords = Medical::where('animalID', $booking->animalID)->get();
        $vaccinationRecords = Vaccination::where('animalID', $booking->animalID)->get();
        $feeBreakdown = $this->calculateAdoptionFee($booking->animal, $medicalRecords, $vaccinationRecords);
        
        $booking->update(['status' => 'Confirmed']);
        
        session([
            'booking_id' => $booking->id,
            'adoption_fee' => $feeBreakdown['total_fee'],
            'animal_name' => $booking->animal->name,
        ]);
        
        return $this->createBill($booking, $feeBreakdown['total_fee']);
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

    public function showAdoptionFee(Booking $booking)
    {
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $booking->load('animal');
        
        $medicalRecords = Medical::where('animalID', $booking->animalID)->get();
        $vaccinationRecords = Vaccination::where('animalID', $booking->animalID)->get();
        
        $feeBreakdown = $this->calculateAdoptionFee($booking->animal, $medicalRecords, $vaccinationRecords);
        
        return view('booking-adoption.adoption', compact('booking', 'feeBreakdown', 'medicalRecords', 'vaccinationRecords'));
    }

    private function calculateAdoptionFee($animal, $medicalRecords, $vaccinationRecords)
    {
        $baseFees = [
            'Dog' => 150.00,
            'Cat' => 100.00,
        ];
        
        $baseFee = $baseFees[$animal->species] ?? ($baseFees['Other'] ?? 100.00);
        
        $medicalRate = 20.00;
        $medicalCount = $medicalRecords->count();
        $medicalFee = $medicalCount * $medicalRate;
        
        $vaccinationRate = 30.00;
        $vaccinationCount = $vaccinationRecords->count();
        $vaccinationFee = $vaccinationCount * $vaccinationRate;
        
        $totalFee = $baseFee + $medicalFee + $vaccinationFee;
        
        return [
            'base_fee' => $baseFee,
            'medical_count' => $medicalCount,
            'medical_rate' => $medicalRate,
            'medical_fee' => $medicalFee,
            'vaccination_count' => $vaccinationCount,
            'vaccination_rate' => $vaccinationRate,
            'vaccination_fee' => $vaccinationFee,
            'total_fee' => $totalFee,
        ];
    }

    public function showModal($id)
    {
        try {
            $booking = Booking::with(['animal.images', 'user'])
                ->where('id', $id)
                ->where('userID', auth()->id())
                ->firstOrFail();
            
            return view('booking-adoption.show-modal', compact('booking'));
        } catch (\Exception $e) {
            Log::error('Booking modal error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load booking',
                'message' => $e->getMessage()
            ], 500);
        }
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