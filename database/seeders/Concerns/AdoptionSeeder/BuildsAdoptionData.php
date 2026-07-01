<?php

namespace Database\Seeders\Concerns\AdoptionSeeder;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait BuildsAdoptionData
{
    private function buildAdoptionData(
        $completedBookings,
        array $animalsByBooking,
        $animalsData,
        array $medicalCounts,
        array $vaccinationCounts
    ): array {
        $baseFees       = ['dog' => 20, 'cat' => 10];
        $medicalRate    = 10;
        $vaccinationRate = 20;

        $totalAnimals    = $animalsData->count();
        $targetAdoptions = (int) ceil($totalAnimals * 0.5);
        $this->command->info("Target: ~{$targetAdoptions} adoptions (50% of {$totalAnimals} animals)");

        $allTransactions  = [];
        $transactionMeta  = [];
        $adoptedAnimalIds = [];
        $affectedSlotIds  = [];

        foreach ($completedBookings as $booking) {
            if (count($adoptedAnimalIds) >= $targetAdoptions) {
                break;
            }

            $bookingAnimalIds = $animalsByBooking[$booking->id] ?? [];
            if (empty($bookingAnimalIds)) {
                continue;
            }

            $available      = array_diff($bookingAnimalIds, $adoptedAnimalIds);
            if (empty($available)) {
                continue;
            }

            $remainingQuota = $targetAdoptions - count($adoptedAnimalIds);
            if ($remainingQuota <= 0) {
                break;
            }

            $roll        = rand(1, 100);
            $adoptCount  = $roll <= 60 ? 1 : ($roll <= 90 ? min(2, count($available)) : count($available));
            $adoptCount  = min($adoptCount, $remainingQuota);

            shuffle($available);
            $animalsToAdopt = array_slice($available, 0, $adoptCount);
            $adoptionDate   = Carbon::parse($booking->appointment_date);
            $totalFee       = 0;
            $names          = [];

            foreach ($animalsToAdopt as $animalId) {
                $animal = $animalsData->get($animalId);
                if (!$animal) {
                    continue;
                }

                $species     = strtolower($animal->species);
                $base        = $baseFees[$species] ?? 15;
                $medical     = ($medicalCounts[$animalId] ?? 0) * $medicalRate;
                $vaccination = ($vaccinationCounts[$animalId] ?? 0) * $vaccinationRate;

                $totalFee += $base + $medical + $vaccination;
                $names[]   = $animal->name;
                $adoptedAnimalIds[] = $animalId;

                if ($animal->slotID) {
                    $affectedSlotIds[] = $animal->slotID;
                }
            }

            $billCode = 'BILL-' . strtoupper(Str::random(8));

            $allTransactions[] = [
                'amount'       => $totalFee,
                'status'       => 'Success',
                'remarks'      => 'Adoption fee for ' . implode(', ', $names),
                'type'         => 'FPX Online Banking',
                'bill_code'    => $billCode,
                'reference_no' => 'REF-' . $adoptionDate->format('Ymd') . '-' . rand(1000, 9999),
                'userID'       => $booking->userID,
                'created_at'   => $adoptionDate->format('Y-m-d H:i:s'),
                'updated_at'   => $adoptionDate->format('Y-m-d H:i:s'),
            ];

            $transactionMeta[] = [
                'billCode'  => $billCode,
                'bookingID' => $booking->id,
                'animalIds' => $animalsToAdopt,
                'date'      => $adoptionDate->format('Y-m-d H:i:s'),
            ];
        }

        return [$allTransactions, $transactionMeta, $adoptedAnimalIds, $affectedSlotIds];
    }
}
