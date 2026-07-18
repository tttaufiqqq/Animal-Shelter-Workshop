<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\Booking;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait StoresBooking
{
    public function storeBooking(Request $request)
    {
        try {
            $validated = $request->validate([
                'animalID' => 'required|exists:animals.animal,id',
                'appointment_date' => 'required|date|after_or_equal:now',
                'terms' => 'required|accepted',
            ], [
                'animalID.required' => 'Please select an animal.',
                'appointment_date.required' => 'Please select an appointment date and time.',
                'appointment_date.after_or_equal' => 'Appointment must be in the future.',
                'terms.accepted' => 'You must accept the terms and conditions.',
            ]);

            $user = Auth::user();
            $appointmentDateTime = \Carbon\Carbon::parse($validated['appointment_date']);
            $appointmentDate = $appointmentDateTime->toDateString();
            $appointmentTime = $appointmentDateTime->toTimeString();

            $conflictingAnimals = $this->getAnimalsWithBookingConflicts([$validated['animalID']], $appointmentDate, $appointmentTime);

            if ($conflictingAnimals->isNotEmpty()) {
                $errorMessages = $this->getBookedAnimalsErrorMessage($conflictingAnimals, $user->id);
                return back()->with('error', 'This animal is not available at that time:<br><br>' . implode('<br>', $errorMessages))->withInput();
            }

            DB::connection('booking')->beginTransaction();

            try {
                $booking = Booking::create([
                    'userID' => $user->id,
                    'status' => 'Pending',
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                ]);

                $booking->animals()->attach([$validated['animalID'] => ['created_at' => now(), 'updated_at' => now()]]);

                $animalName = Animal::find($validated['animalID'])?->name;
                AuditService::log('payment', 'booking_created', [
                    'entity_type' => 'Booking',
                    'entity_id' => $booking->id,
                    'source_database' => 'booking',
                    'metadata' => [
                        'appointment_date' => $appointmentDate,
                        'appointment_time' => $appointmentTime,
                        'animal_ids' => [$validated['animalID']],
                        'animal_names' => [$animalName],
                        'animal_count' => 1,
                    ],
                ]);

                DB::connection('booking')->commit();

                return redirect()->route('animal-management.index')->with('success', 'Your appointment has been scheduled! View it in My Bookings.');

            } catch (\Illuminate\Database\QueryException $e) {
                DB::connection('booking')->rollBack();

                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'unique') !== false) {
                    return back()->with('error', 'This time slot has just been booked. Please select a different time.')->withInput();
                }

                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            if (DB::connection('booking')->transactionLevel() > 0) {
                DB::connection('booking')->rollBack();
            }

            Log::error('Store Booking Error: ' . $e->getMessage(), ['user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Failed to schedule appointment: ' . $e->getMessage());
        }
    }
}
