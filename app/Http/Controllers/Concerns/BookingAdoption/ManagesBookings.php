<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesBookings
{
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

        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
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
}
