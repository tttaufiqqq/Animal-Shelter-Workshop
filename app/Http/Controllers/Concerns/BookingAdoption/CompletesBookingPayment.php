<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Adoption;
use App\Models\Animal;
use App\Models\Booking;
use App\Models\Slot;
use App\Models\Transaction;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

trait CompletesBookingPayment
{
    /**
     * Marks a booking Completed, adopts its animals, frees/occupies their slot, and
     * records the Transaction + Adoption rows — all atomically on the 'booking' and
     * 'animals' connections. Idempotent on bill_code: a repeat call for a bill that
     * already has a Transaction record is a no-op that returns the existing one.
     */
    private function completeBookingPayment(
        Booking $booking,
        array $animalIds,
        ?string $billCode,
        ?string $referenceNo,
        float $adoptionFee,
        array $feeBreakdowns = [],
        ?string $animalNames = null,
        ?int $userId = null
    ): ?Transaction {
        if (empty($animalIds)) {
            return null;
        }

        if ($billCode) {
            $existing = Transaction::where('bill_code', $billCode)->first();
            if ($existing) {
                return $existing;
            }
        }

        DB::connection('booking')->beginTransaction();
        DB::connection('animals')->beginTransaction();

        $atiqahOnline = $this->isDatabaseAvailable('shelter');
        if ($atiqahOnline) {
            DB::connection('shelter')->beginTransaction();
        }

        try {
            $booking->update(['status' => 'Completed']);
            AuditService::logPayment('payment_completed', $booking->id, $adoptionFee, $animalIds, $billCode, 'success');

            $affectedSlotIds = [];
            $animalNamesById = [];

            foreach ($animalIds as $animalId) {
                $animal = Animal::find($animalId);
                if (! $animal) {
                    continue;
                }

                $oldStatus = $animal->adoption_status;
                $oldSlotId = $animal->slotID;
                $animalNamesById[$animalId] = $animal->name;

                Animal::where('id', $animalId)->update(['adoption_status' => 'Adopted', 'slotID' => null]);

                if ($oldSlotId) {
                    $affectedSlotIds[] = $oldSlotId;
                }

                AuditService::logAnimal(
                    'adoption_status_changed',
                    $animalId,
                    $animal->name,
                    ['adoption_status' => $oldStatus, 'slotID' => $oldSlotId],
                    ['adoption_status' => 'Adopted', 'slotID' => null],
                    ['booking_id' => $booking->id, 'adopter_id' => $userId, 'adoption_fee' => $adoptionFee / count($animalIds)]
                );
            }

            if ($atiqahOnline && ! empty($affectedSlotIds)) {
                foreach (array_unique($affectedSlotIds) as $slotId) {
                    $slot = Slot::find($slotId);
                    if ($slot) {
                        $remainingAnimals = Animal::where('slotID', $slotId)->count();
                        // A slot with nothing left in it is never "occupied", even at zero capacity.
                        $slot->status = ($remainingAnimals > 0 && $remainingAnimals >= $slot->capacity) ? 'occupied' : 'available';
                        $slot->save();
                    }
                }
            }

            $transaction = Transaction::create([
                'amount' => $adoptionFee,
                'status' => 'Success',
                'remarks' => 'Adoption payment for ' . ($animalNames ?? '') . ' (Booking #' . $booking->id . ')',
                'type' => 'FPX Online Banking',
                'bill_code' => $billCode,
                'reference_no' => $referenceNo,
                'userID' => $userId ?? $booking->userID,
            ]);

            foreach ($animalIds as $animalId) {
                $animalName = $animalNamesById[$animalId] ?? 'Unknown';
                $individualFee = $feeBreakdowns[$animalId] ?? ($adoptionFee / count($animalIds));

                $adoption = Adoption::create([
                    'fee' => $individualFee,
                    'remarks' => 'Adopted: ' . $animalName,
                    'bookingID' => $booking->id,
                    'transactionID' => $transaction->id,
                    'animalID' => $animalId,
                ]);

                AuditService::log('payment', 'adoption_completed', [
                    'entity_type' => 'Adoption',
                    'entity_id' => $adoption->id,
                    'source_database' => 'booking',
                    'metadata' => [
                        'animal_id' => $animalId,
                        'animal_name' => $animalName,
                        'adoption_fee' => $individualFee,
                        'booking_id' => $booking->id,
                        'transaction_id' => $transaction->id,
                        'adopter_id' => $userId,
                    ],
                ]);
            }

            DB::connection('booking')->commit();
            DB::connection('animals')->commit();
            if ($atiqahOnline) {
                DB::connection('shelter')->commit();
            }

            return $transaction;

        } catch (\Exception $e) {
            DB::connection('booking')->rollBack();
            DB::connection('animals')->rollBack();
            if ($atiqahOnline) {
                DB::connection('shelter')->rollBack();
            }
            throw $e;
        }
    }
}
