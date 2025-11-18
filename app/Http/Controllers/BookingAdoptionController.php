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
use App\Models\Booking;  
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        if (in_array($booking->status, ['pending', 'confirmed'])) {
            $booking->update(['status' => 'cancelled']);
            return redirect()->route('booking-adoption.main')->with('success', 'Booking cancelled successfully!');
        }

        return redirect()->route('booking-adoption.main')->with('error', 'Cannot cancel this booking.');
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

}
