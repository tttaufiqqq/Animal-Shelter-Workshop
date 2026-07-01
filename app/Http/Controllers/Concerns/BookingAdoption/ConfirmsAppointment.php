<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\Booking;
use App\Models\VisitList;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ConfirmsAppointment
{
    public function confirmAppointment(Request $request)
    {
        try {
            \Log::info('=== CONFIRM APPOINTMENT STARTED ===');
            \Log::info('Request data:', $request->all());

            $validated = $request->validate([
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required',
                'animal_ids' => 'required|array|min:1',
                'animal_ids.*' => 'required|exists:animals.animal,id',
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

            $user = Auth::user();
            $visitList = VisitList::where('userID', $user->id)->first();

            if (!$visitList) {
                return back()->with('error', 'Your visit list is empty.')->with('open_visit_modal', true);
            }

            $visitListAnimalIds = DB::connection('booking')->table('visit_list_animal')->where('listID', $visitList->id)->pluck('animalID')->toArray();

            if (empty($visitListAnimalIds)) {
                return back()->with('error', 'Your visit list is empty.')->with('open_visit_modal', true);
            }

            $requestedAnimalIds = $validated['animal_ids'];
            $invalidAnimals = array_diff($requestedAnimalIds, $visitListAnimalIds);
            if (!empty($invalidAnimals)) {
                return back()->with('error', 'Some selected animals are not in your visit list.')->with('open_visit_modal', true);
            }

            $appointmentDate = $validated['appointment_date'];
            $appointmentTime = $validated['appointment_time'];

            $conflictingAnimals = $this->getAnimalsWithBookingConflicts($requestedAnimalIds, $appointmentDate, $appointmentTime);

            if ($conflictingAnimals->isNotEmpty()) {
                $errorMessages = $this->getBookedAnimalsErrorMessage($conflictingAnimals, $user->id);
                $errorHtml = 'The following animals are not available at <strong>'
                    . \Carbon\Carbon::parse($appointmentDate)->format('M d, Y')
                    . ' at ' . \Carbon\Carbon::parse($appointmentTime)->format('g:i A')
                    . '</strong>:<br><br>'
                    . implode('<br>', $errorMessages)
                    . '<br><br>Please choose a different time slot or remove these animals from your selection.';

                return back()->with('error', $errorHtml)->with('open_visit_modal', true)->withInput();
            }

            $existingUserBookings = Booking::where('userID', $user->id)->where('appointment_date', $appointmentDate)->where('appointment_time', $appointmentTime)->with('animalBookings')->get();

            if ($existingUserBookings->isNotEmpty()) {
                $alreadyBookedAnimalIds = $existingUserBookings->flatMap(fn($booking) => $booking->animalBookings->pluck('animalID'))->unique()->toArray();
                $existingUserBookings = $existingUserBookings->filter(fn($booking) => !empty(array_intersect($booking->animalBookings->pluck('animalID')->toArray(), $requestedAnimalIds)));
                $duplicateAnimalIds = array_intersect($requestedAnimalIds, $alreadyBookedAnimalIds);

                if (!empty($duplicateAnimalIds)) {
                    $duplicateAnimals = Animal::whereIn('id', $duplicateAnimalIds)->pluck('name')->toArray();
                    $duplicateNames = implode(', ', $duplicateAnimals);
                    $bookingStatuses = $existingUserBookings->pluck('status')->unique()->toArray();
                    $statusText = implode(', ', $bookingStatuses);

                    return back()
                        ->with('error', "You already have a booking ({$statusText}) for {$duplicateNames} at {$appointmentTime} on {$appointmentDate}. Please choose different animals or a different time slot.")
                        ->with('open_visit_modal', true)
                        ->withInput();
                }
            }

            DB::connection('booking')->beginTransaction();

            try {
                $booking = Booking::create([
                    'userID' => $user->id,
                    'status' => 'Pending',
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                ]);

                $animalNames = Animal::whereIn('id', $validated['animal_ids'])->pluck('name')->toArray();
                AuditService::log('payment', 'booking_created', [
                    'entity_type' => 'Booking',
                    'entity_id' => $booking->id,
                    'source_database' => 'booking',
                    'metadata' => ['appointment_date' => $appointmentDate, 'appointment_time' => $appointmentTime, 'animal_ids' => $validated['animal_ids'], 'animal_names' => $animalNames, 'animal_count' => count($validated['animal_ids'])],
                ]);

                $animalData = [];
                foreach ($validated['animal_ids'] as $animalId) {
                    $animalData[$animalId] = ['remarks' => $validated['remarks'][$animalId] ?? null, 'created_at' => now(), 'updated_at' => now()];
                }
                $booking->animals()->attach($animalData);

                DB::connection('booking')->table('visit_list_animal')->where('listID', $visitList->id)->whereIn('animalID', $validated['animal_ids'])->delete();

                $remainingCount = DB::connection('booking')->table('visit_list_animal')->where('listID', $visitList->id)->count();
                if ($remainingCount === 0) {
                    $visitList->delete();
                }

                DB::connection('booking')->commit();

                return redirect()->route('animal-management.index')->with('success', 'Your visit appointment has been scheduled! View them at My Booking tab');

            } catch (\Illuminate\Database\QueryException $e) {
                DB::connection('booking')->rollBack();

                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'unique') !== false) {
                    return back()->with('error', 'This time slot has just been booked by another user. Please select a different time.')->with('open_visit_modal', true)->withInput();
                }

                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput()->with('open_visit_modal', true);

        } catch (\Exception $e) {
            if (DB::connection('booking')->transactionLevel() > 0) {
                DB::connection('booking')->rollBack();
            }

            \Log::error('Booking Confirmation Error: ' . $e->getMessage(), ['user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Failed to schedule appointment: ' . $e->getMessage() . ' [' . get_class($e) . ']')->with('open_visit_modal', true);
        }
    }
}
