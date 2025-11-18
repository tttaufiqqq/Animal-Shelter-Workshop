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
      $bookings = \App\Models\Booking::with('animals')
         ->where('userID', auth()->id())
         ->orderBy('appointment_date', 'desc')
         ->get();

      return view('booking-adoption.main', compact('bookings'));
   }
    public function storeBooking(Request $request)
   {

      $request->validate([
         'animalID' => 'required|exists:animal,id',
         'appointment_date' => 'required|date|after:now',
         'terms' => 'required|accepted',
      ]);

      // Split datetime-local into date + time
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
         'appointment_date' => $dateTime->toDateString(),  // YYYY-MM-DD
         'appointment_time' => $dateTime->toTimeString(),      // HH:MM:SS
         'status' => 'Pending',
         'notes' => $request->notes,
      ]);

      return redirect()->back()->with('success', 'Adoption appointment booked successfully! We will contact you to confirm the details.');
   }

    /**
     * Display a listing of the user's bookings.
     */
    public function index()
    {
        // Get all bookings for the logged-in user with related data
        $bookings = Booking::where('userID', Auth::id())
            ->with(['animal', 'adoption'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get();

        return view('booking-adoption.main', compact('bookings'));
    }

    public function indexAdmin()
    {
        // Get all bookings for the logged-in user with related data
        $bookings = Booking::with(['animal', 'adoption', 'user'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get();

        return view('booking-adoption.admin', compact('bookings'));
    }
   
    public function show(Booking $booking)
    {
        // Make sure the booking belongs to the logged-in user
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $booking->load(['animals', 'adoption']);
        
        return view('booking-adoption.show', compact('booking'));
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking)
    {
        // Make sure the booking belongs to the logged-in user
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow cancellation if status is pending or confirmed
        if (in_array($booking->status, ['Pending', 'Confirmed'])) {
            $booking->update(['status' => 'Cancelled']);
            return redirect()->route('booking:main')->with('success', 'Booking cancelled successfully!');
        }

        return redirect()->route('booking:main')->with('error', 'Cannot cancel this booking.');
    }

    public function confirm(Booking $booking, Request $request)
    {
        // Make sure the booking belongs to the logged-in user
        if ($booking->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow confirmation if status is pending
        if (!in_array($booking->status, ['Pending', 'pending', 'confirmed', 'Confirmed'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot confirm this booking.'
                ], 400);
            }
            return redirect()->route('booking:main')->with('error', 'Cannot confirm this booking.');
        }

        // Calculate adoption fee
        $booking->load('animal');
        $medicalRecords = Medical::where('animalID', $booking->animalID)->get();
        $vaccinationRecords = Vaccination::where('animalID', $booking->animalID)->get();
        $feeBreakdown = $this->calculateAdoptionFee($booking->animal, $medicalRecords, $vaccinationRecords);
        
        // Update booking status to Confirmed (not Completed yet)
        $booking->update(['status' => 'Confirmed']);
        
        // Store fee in session for payment
        session([
            'booking_id' => $booking->id,
            'adoption_fee' => $feeBreakdown['total_fee'],
            'animal_name' => $booking->animal->name,
        ]);
        
        // Redirect to create bill
        return $this->createBill($booking, $feeBreakdown['total_fee']);
    }
   //ToyyibPay
    public function createBill(Booking $booking, $adoptionFee)
    {
        $user = Auth::user();
        $animalName = $booking->animal->name;

        $option = [
            'userSecretKey' => config('toyyibpay.key'),
            'categoryCode' => config('toyyibpay.category'),
            'billName' => 'Adopt ' . substr($animalName, 0, 20),
            'billDescription' => 'Adoption fee for ' . $animalName . ' (Booking #' . $booking->id . ')',
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => ($adoptionFee) * 100, // Convert to cents
            'billReturnUrl' => route('toyyibpay-status'),
            'billCallbackUrl' => route('toyyibpay-callback'),
            'billExternalReferenceNo' => 'BOOKING-' . $booking->id . '-' . time(),
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

        // dd([
        //     'status'   => $response->status(),
        //     'body'     => $response->body(),
        //     'json'     => $response->json(),
        //     'payload'  => $option, // optional but VERY useful
        // ]);

        if (isset($data[0]['BillCode'])) {
            $billCode = $data[0]['BillCode'];
            
            // Store bill code in session for verification
            session(['bill_code' => $billCode]);
            
            return redirect('https://dev.toyyibpay.com/' . $billCode);
        } else {
            dd([
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            // Revert booking status back to Pending
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
        
        // Get payment status details
        $paymentStatus = $this->getBillTransactions($billCode);
        
        if ($statusId == 1) {
            // Payment successful
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                
                if ($booking) {
                    // Update booking status to Completed
                    $booking->update(['status' => 'Completed']);

                    // Mark the animal as Adopted
                    $booking->animal->update(['adoption_status' => 'Adopted']);
                    
                    // Create transaction
                    $transaction = Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Success',
                        'remarks' => 'Adoption payment for ' . $animalName . ' (Booking #' . $bookingId . ') - Bill Code: ' . $billCode,
                        'date' => now(),
                        'type' => 'Online Banking',
                        'userID' => Auth::id(),
                    ]);

                    // Create adoption record
                    Adoption::create([
                        'fee'=> $adoptionFee,
                        'remarks' =>  $animalName . ' Adopted',
                        'bookingID'=> $bookingId,
                        'transactionID' => $transaction->id,
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

                    // Clear session
                    session()->forget(['booking_id', 'adoption_fee', 'animal_name', 'bill_code']);
                    
                    Log::info('Payment Success', [
                        'booking_id' => $bookingId,
                        'amount' => $adoptionFee,
                        'bill_code' => $billCode
                    ]);
                }
            }
        }
 else {
            // Payment failed or pending
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                
                if ($booking && $booking->status == 'Confirmed') {
                    // Keep status as Confirmed (not completed)
                    Log::info('Payment Failed/Pending', [
                        'booking_id' => $bookingId,
                        'status_id' => $statusId,
                        'bill_code' => $billCode
                    ]);
                    
                    // Optionally create a failed transaction record
                    Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Failed',
                        'remarks' => 'Failed adoption payment for ' . $animalName . ' (Booking #' . $bookingId . ') - Bill Code: ' . $billCode,
                        'date' => now(),
                        'type' => 'Adoption Fee',
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
            'payment_details' => $paymentStatus,
        ]);
    }

    public function callback(Request $request)
    {
        Log::info('ToyyibPay Callback:', $request->all());
        
        $billCode = $request->input('billcode');
        $statusId = $request->input('status_id');
        
        // You can also update booking status here as a backup
        // Parse the external reference to get booking ID
        $refNo = $request->input('refno');
        if (strpos($refNo, 'BOOKING-') !== false) {
            $parts = explode('-', $refNo);
            if (isset($parts[1])) {
                $bookingId = $parts[1];
                $booking = Booking::find($bookingId);
                
                if ($booking && $statusId == 1) {
                    $booking->update(['status' => 'Completed']);
                    
                    // Create transaction if not already created
                    $existingTransaction = Transaction::where('remarks', 'like', '%' . $billCode . '%')->first();
                    if (!$existingTransaction) {
                        Transaction::create([
                            'amount' => $request->input('amount') / 100, // Convert from cents
                            'status' => 'Success',
                            'remarks' => 'Adoption payment (Callback) - Booking #' . $bookingId . ' - Bill Code: ' . $billCode,
                            'date' => now(),
                            'type' => 'Adoption Fee',
                            'userID' => $booking->userID,
                        ]);
                    }
                }
            }
        }
    }
    
    // Helper function to get bill transaction details
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
      // Make sure the booking belongs to the logged-in user
      if ($booking->userID !== Auth::id()) {
         abort(403, 'Unauthorized action.');
      }

      // Load animal with medical and vaccination records
      $booking->load('animal');
      
      // Get medical and vaccination records
      $medicalRecords = Medical::where('animalID', $booking->animalID)->get();
      $vaccinationRecords = Vaccination::where('animalID', $booking->animalID)->get();
      
      // Calculate fees
      $feeBreakdown = $this->calculateAdoptionFee($booking->animal, $medicalRecords, $vaccinationRecords);
      
      return view('booking-adoption.adoption', compact('booking', 'feeBreakdown', 'medicalRecords', 'vaccinationRecords'));
   }

   private function calculateAdoptionFee($animal, $medicalRecords, $vaccinationRecords)
   {
      // Base fee by species
      $baseFees = [
         'Dog' => 150.00,
         'Cat' => 100.00,
      ];
      
      $baseFee = $baseFees[$animal->species] ?? $baseFees['Other'];
      
      // Medical records fee (RM 20 per record)
      $medicalRate = 20.00;
      $medicalCount = $medicalRecords->count();
      $medicalFee = $medicalCount * $medicalRate;
      
      // Vaccination records fee (RM 30 per vaccination)
      $vaccinationRate = 30.00;
      $vaccinationCount = $vaccinationRecords->count();
      $vaccinationFee = $vaccinationCount * $vaccinationRate;
      
      // Total fee
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
            \Log::error('Booking modal error: ' . $e->getMessage());
            return response()->json([
                  'error' => 'Failed to load booking',
                  'message' => $e->getMessage()
            ], 500);
         }
      }
      public function showModalAdmin($id)
      {
         try {
            // REMOVED: ->where('userID', auth()->id())
            // An admin should be able to view any booking by its ID.
            $booking = Booking::with(['animal.images', 'user'])
                  ->where('id', $id)
                  ->firstOrFail(); // Throws ModelNotFoundException if booking with $id doesn't exist.
            
            return view('booking-adoption.show-admin', compact('booking'));
            
         } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Admin Booking modal error for ID ' . $id . ': ' . $e->getMessage());
            
            // Return a 404 (Not Found) if the ModelNotFoundException was caught, 
            // or a 500 for other errors.
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
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
