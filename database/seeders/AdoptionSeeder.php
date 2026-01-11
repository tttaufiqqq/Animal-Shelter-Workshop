<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdoptionSeeder extends Seeder
{
    /**
     * OPTIMIZED FOR SQL SERVER - No long transactions, small batches
     *
     * Business Flow:
     * - Confirmed bookings = User decided to adopt, awaiting payment (NO adoptions)
     * - Completed bookings = Payment successful (CREATE adoptions)
     */
    public function run()
    {
        $this->command->info('Starting Adoption Seeder (SQL Server Optimized)...');

        // Check Atiqah availability
        $atiqahAvailable = false;
        try {
            DB::connection('atiqah')->getPdo();
            $atiqahAvailable = true;
            $this->command->info('Atiqah: ONLINE');
        } catch (\Exception $e) {
            $this->command->warn('Atiqah: OFFLINE (slot updates skipped)');
        }

        // Get completed bookings
        $completedBookings = DB::connection('danish')->table('booking')
            ->where('status', 'Completed')
            ->orderBy('id', 'asc')
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->command->warn('No completed bookings found.');
            return;
        }
        $this->command->info("Found " . $completedBookings->count() . " completed bookings");

        // Get animal-booking relationships
        $bookingIds = $completedBookings->pluck('id')->toArray();
        $animalBookings = DB::connection('danish')->table('animal_booking')
            ->whereIn('bookingID', $bookingIds)
            ->get();

        // Group animals by booking
        $animalsByBooking = [];
        foreach ($animalBookings as $ab) {
            $animalsByBooking[$ab->bookingID][] = $ab->animalID;
        }

        // Get all animal data
        $allAnimalIds = $animalBookings->pluck('animalID')->unique()->toArray();
        $animalsData = DB::connection('shafiqah')->table('animal')
            ->whereIn('id', $allAnimalIds)
            ->get()->keyBy('id');

        // Get medical/vaccination counts
        $medicalCounts = DB::connection('shafiqah')->table('medical')
            ->whereIn('animalID', $allAnimalIds)
            ->selectRaw('animalID, COUNT(*) as cnt')
            ->groupBy('animalID')
            ->pluck('cnt', 'animalID')->toArray();

        $vaccinationCounts = DB::connection('shafiqah')->table('vaccination')
            ->whereIn('animalID', $allAnimalIds)
            ->selectRaw('animalID, COUNT(*) as cnt')
            ->groupBy('animalID')
            ->pluck('cnt', 'animalID')->toArray();

        $this->command->info('Generating adoption data...');

        // Fee structure
        $baseFees = ['dog' => 20, 'cat' => 10];
        $medicalRate = 10;
        $vaccinationRate = 20;

        // Target: adopt ~50% of total animals
        $totalAnimals = count($allAnimalIds);
        $targetAdoptions = (int) ceil($totalAnimals * 0.5); // 50% of animals
        $this->command->info("Target: ~{$targetAdoptions} adoptions (50% of {$totalAnimals} animals)");

        // Pre-generate all data
        $allTransactions = [];
        $transactionMeta = []; // index => [bookingID, animalIds, date]
        $adoptedAnimalIds = [];
        $affectedSlotIds = [];

        foreach ($completedBookings as $booking) {
            // Stop if we've reached target adoptions
            if (count($adoptedAnimalIds) >= $targetAdoptions) {
                break;
            }

            $bookingAnimalIds = $animalsByBooking[$booking->id] ?? [];
            if (empty($bookingAnimalIds)) continue;

            // Filter already adopted
            $available = array_diff($bookingAnimalIds, $adoptedAnimalIds);
            if (empty($available)) continue;

            // Check how many more we can adopt
            $remainingQuota = $targetAdoptions - count($adoptedAnimalIds);
            if ($remainingQuota <= 0) break;

            // Adoption count: 60% one, 30% two, 10% all (but limited by quota)
            $roll = rand(1, 100);
            $adoptCount = $roll <= 60 ? 1 : ($roll <= 90 ? min(2, count($available)) : count($available));
            $adoptCount = min($adoptCount, $remainingQuota); // Don't exceed quota

            shuffle($available);
            $animalsToAdopt = array_slice($available, 0, $adoptCount);

            $adoptionDate = Carbon::parse($booking->appointment_date);
            $totalFee = 0;
            $names = [];

            foreach ($animalsToAdopt as $animalId) {
                $animal = $animalsData->get($animalId);
                if (!$animal) continue;

                $species = strtolower($animal->species);
                $base = $baseFees[$species] ?? 15;
                $medical = ($medicalCounts[$animalId] ?? 0) * $medicalRate;
                $vaccination = ($vaccinationCounts[$animalId] ?? 0) * $vaccinationRate;

                $totalFee += $base + $medical + $vaccination;
                $names[] = $animal->name;
                $adoptedAnimalIds[] = $animalId;

                if ($animal->slotID) {
                    $affectedSlotIds[] = $animal->slotID;
                }
            }

            $billCode = 'BILL-' . strtoupper(Str::random(8));

            $allTransactions[] = [
                'amount' => $totalFee,
                'status' => 'Success',
                'remarks' => 'Adoption fee for ' . implode(', ', $names),
                'type' => 'FPX Online Banking',
                'bill_code' => $billCode,
                'reference_no' => 'REF-' . $adoptionDate->format('Ymd') . '-' . rand(1000, 9999),
                'userID' => $booking->userID,
                'created_at' => $adoptionDate->format('Y-m-d H:i:s'),
                'updated_at' => $adoptionDate->format('Y-m-d H:i:s'),
            ];

            $transactionMeta[] = [
                'billCode' => $billCode,
                'bookingID' => $booking->id,
                'animalIds' => $animalsToAdopt,
                'date' => $adoptionDate->format('Y-m-d H:i:s'),
            ];
        }

        $this->command->info("Creating " . count($allTransactions) . " transactions, " . count($adoptedAnimalIds) . " adoptions...");

        // Insert transactions in small chunks (no transaction wrapper)
        $chunkSize = 50;
        $transChunks = array_chunk($allTransactions, $chunkSize);
        foreach ($transChunks as $idx => $chunk) {
            DB::connection('danish')->table('transaction')->insert($chunk);
        }

        // Get transaction IDs by bill_code
        $billCodes = array_column($transactionMeta, 'billCode');
        $insertedTrans = DB::connection('danish')->table('transaction')
            ->whereIn('bill_code', $billCodes)
            ->get(['id', 'bill_code'])
            ->keyBy('bill_code');

        // Build adoption records
        $allAdoptions = [];
        foreach ($transactionMeta as $meta) {
            $trans = $insertedTrans->get($meta['billCode']);
            if (!$trans) continue;

            foreach ($meta['animalIds'] as $animalId) {
                $animal = $animalsData->get($animalId);
                if (!$animal) continue;

                $species = strtolower($animal->species);
                $base = $baseFees[$species] ?? 15;
                $medical = ($medicalCounts[$animalId] ?? 0) * $medicalRate;
                $vaccination = ($vaccinationCounts[$animalId] ?? 0) * $vaccinationRate;

                $allAdoptions[] = [
                    'fee' => $base + $medical + $vaccination,
                    'remarks' => $animal->name . ' successfully adopted',
                    'bookingID' => $meta['bookingID'],
                    'transactionID' => $trans->id,
                    'animalID' => $animalId,
                    'created_at' => $meta['date'],
                    'updated_at' => $meta['date'],
                ];
            }
        }

        // Insert adoptions
        $adoptionChunks = array_chunk($allAdoptions, $chunkSize);
        foreach ($adoptionChunks as $chunk) {
            DB::connection('danish')->table('adoption')->insert($chunk);
        }

        $this->command->info('Updating animal statuses in Shafiqah...');

        // Update animals in Shafiqah (small chunks)
        $animalChunks = array_chunk($adoptedAnimalIds, $chunkSize);
        foreach ($animalChunks as $chunk) {
            DB::connection('shafiqah')->table('animal')
                ->whereIn('id', $chunk)
                ->update([
                    'adoption_status' => 'Adopted',
                    'slotID' => null,
                    'updated_at' => now(),
                ]);
        }

        // Update slots in Atiqah if available
        if ($atiqahAvailable && !empty($affectedSlotIds)) {
            $uniqueSlots = array_unique($affectedSlotIds);
            $this->command->info("Updating " . count($uniqueSlots) . " slots in Atiqah...");

            $slots = DB::connection('atiqah')->table('slot')
                ->whereIn('id', $uniqueSlots)
                ->get(['id', 'capacity'])->keyBy('id');

            $animalCounts = DB::connection('shafiqah')->table('animal')
                ->whereIn('slotID', $uniqueSlots)
                ->selectRaw('slotID, COUNT(*) as cnt')
                ->groupBy('slotID')
                ->pluck('cnt', 'slotID')->toArray();

            $slotsToFree = [];
            foreach ($slots as $slotId => $slot) {
                $remaining = $animalCounts[$slotId] ?? 0;
                if ($remaining < $slot->capacity) {
                    $slotsToFree[] = $slotId;
                }
            }

            if (!empty($slotsToFree)) {
                DB::connection('atiqah')->table('slot')
                    ->whereIn('id', $slotsToFree)
                    ->update(['status' => 'available', 'updated_at' => now()]);
            }
        } elseif (!$atiqahAvailable && !empty($affectedSlotIds)) {
            $this->command->warn("Skipped " . count(array_unique($affectedSlotIds)) . " slot updates (Atiqah offline)");
        }

        // Stats
        $totalAdoptions = DB::connection('danish')->table('adoption')->count();
        $totalTrans = DB::connection('danish')->table('transaction')->count();

        $this->command->info('');
        $this->command->info('=== Adoption Seeder Complete ===');
        $this->command->info("Transactions: {$totalTrans}");
        $this->command->info("Adoptions: {$totalAdoptions}");
        $this->command->info("Animals adopted: " . count($adoptedAnimalIds));
    }
}
