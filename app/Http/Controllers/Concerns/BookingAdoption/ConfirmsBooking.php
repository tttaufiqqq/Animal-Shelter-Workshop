<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\AnimalBooking;
use App\Models\Booking;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ConfirmsBooking
{
    public function confirm(Request $request, $bookingId)
    {
        Log::info('Confirm Booking - Raw Request Input', ['booking_id' => $bookingId, 'input' => $request->all(), 'user' => auth()->id()]);

        try {
            $validated = $request->validate([
                'animal_ids' => 'required|array|min:1',
                'animal_ids.*' => 'required|exists:animals.animal,id',
                'agree_terms' => 'required|accepted',
            ], [
                'animal_ids.required' => 'Please select at least one animal.',
                'animal_ids.min' => 'Please select at least one animal.',
                'agree_terms.required' => 'You must agree to the terms.',
                'agree_terms.accepted' => 'You must agree to the terms.',
            ]);

            $booking = Booking::with('animalBookings')->findOrFail($bookingId);

            if ($booking->userID != Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

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

            $selectedAnimals = Animal::whereIn('id', $validated['animal_ids'])->with(['medicals', 'vaccinations'])->get();
            $animalNames = $selectedAnimals->pluck('name')->toArray();

            // The fee is always computed server-side from the selected animals — never
            // trust a client-supplied amount, or a tampered request can adopt for RM0.
            $feeBreakdowns = [];
            $totalFee = 0;
            foreach ($selectedAnimals as $animal) {
                $breakdown = $this->calculateAdoptionFee($animal, $animal->medicals ?? collect(), $animal->vaccinations ?? collect());
                $feeBreakdowns[$animal->id] = $breakdown['total_fee'];
                $totalFee += $breakdown['total_fee'];
            }

            $booking->update(['status' => 'Confirmed']);

            AuditService::logPayment('booking_confirmed', $bookingId, $totalFee, $validated['animal_ids'], null, 'success');

            session(['booking_id' => $booking->id, 'adoption_fee' => $totalFee, 'animal_ids' => $validated['animal_ids'], 'animal_names' => implode(', ', $animalNames), 'fee_breakdowns' => $feeBreakdowns]);

            return $this->createBill($booking, $totalFee, $selectedAnimals);

        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Booking Confirmation Error: {$e->getMessage()}", ['booking_id' => $bookingId, 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to confirm booking: ' . $e->getMessage() . ' [' . get_class($e) . ']');
        }
    }
}
