<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdoptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * REALISTIC ADOPTION PROCESS:
     * - Process completed bookings chronologically
     * - Not all visits lead to adoption (30-40% success rate)
     * - Adoption probability factors:
     *   - Young animals: 70% chance
     *   - Adult animals: 35% chance
     *   - Senior animals: 20% chance
     *   - Healthy: +10% bonus
     *   - Sick/observation: -10% penalty
     *   - Time at shelter: slight bonus for longer stays
     * - When adopted: update animal status, free slot, create transaction
     */
    public function run()
    {
        $this->command->info('Starting Adoption Seeder (Realistic Progression)...');
        $this->command->info('========================================');

        // Get all "Completed" bookings chronologically
        $this->command->info('Fetching completed bookings from Danish\'s database...');
        $completedBookings = DB::connection('danish')
            ->table('booking')
            ->where('status', 'Completed')
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->command->warn('No completed bookings found. Adoptions cannot be created.');
            return;
        }

        $this->command->info("Found " . $completedBookings->count() . " completed bookings");

        // Get users for transactions
        $users = DB::connection('taufiq')->table('users')->pluck('id')->toArray();

        // Fee structure
        $speciesBaseFees = [
            'dog' => 20,
            'cat' => 10,
        ];
        $medicalRate = 10;
        $vaccinationRate = 20;

        // Use transaction for cross-database operations
        DB::connection('danish')->beginTransaction();
        DB::connection('shafiqah')->beginTransaction();
        DB::connection('atiqah')->beginTransaction();

        try {
            $this->command->info('');
            $this->command->info('Processing bookings for adoptions (30-40% success rate - BATCH MODE)...');

            // STEP 1: Preload all animal data with medical/vaccination counts (single query per type)
            $this->command->info('Preloading animal data...');

            $allAnimalIds = DB::connection('danish')
                ->table('animal_booking')
                ->distinct()
                ->pluck('animalID')
                ->toArray();

            // Get all animals at once
            $animalsData = DB::connection('shafiqah')
                ->table('animal')
                ->whereIn('id', $allAnimalIds)
                ->get()
                ->keyBy('id');

            // Get medical counts for all animals (single query)
            $medicalCounts = DB::connection('shafiqah')
                ->table('medical')
                ->whereIn('animalID', $allAnimalIds)
                ->selectRaw('animalID, COUNT(*) as count')
                ->groupBy('animalID')
                ->pluck('count', 'animalID')
                ->toArray();

            // Get vaccination counts for all animals (single query)
            $vaccinationCounts = DB::connection('shafiqah')
                ->table('vaccination')
                ->whereIn('animalID', $allAnimalIds)
                ->selectRaw('animalID, COUNT(*) as count')
                ->groupBy('animalID')
                ->pluck('count', 'animalID')
                ->toArray();

            $this->command->info('Loaded data for ' . count($animalsData) . ' animals');

            // STEP 2: Determine which animals get adopted (collect data for batch insert)
            $adoptionCount = 0;
            $processedAnimals = [];
            $ageBreakdown = ['young' => 0, 'adult' => 0, 'senior' => 0];

            $allTransactions = [];
            $allAdoptions = [];
            $adoptedAnimalIds = [];
            $affectedSlotIds = [];
            $adoptionDates = []; // Track adoption date per animal for updates

            foreach ($completedBookings as $booking) {
                // Get animals in this booking
                $bookingAnimals = DB::connection('danish')
                    ->table('animal_booking')
                    ->where('bookingID', $booking->id)
                    ->pluck('animalID')
                    ->toArray();

                foreach ($bookingAnimals as $animalId) {
                    // Skip if already adopted
                    if (in_array($animalId, $processedAnimals)) {
                        continue;
                    }

                    // Get animal from preloaded data
                    $animal = $animalsData->get($animalId);

                    if (!$animal) {
                        continue;
                    }

                    // Calculate adoption probability
                    $adoptionChance = $this->calculateAdoptionProbability($animal, $booking);

                    // Determine if adoption happens
                    $randomRoll = rand(1, 100);
                    if ($randomRoll > $adoptionChance) {
                        continue; // Not adopted this visit
                    }

                    // ===== ADOPTION HAPPENS! =====

                    // Calculate adoption fee using preloaded counts
                    $species = strtolower($animal->species);
                    $baseFee = $speciesBaseFees[$species] ?? 15;

                    $medicalCount = $medicalCounts[$animal->id] ?? 0;
                    $medicalFee = $medicalCount * $medicalRate;

                    $vaccinationCount = $vaccinationCounts[$animal->id] ?? 0;
                    $vaccinationFee = $vaccinationCount * $vaccinationRate;

                    $totalFee = $baseFee + $medicalFee + $vaccinationFee;

                    $adoptionDate = Carbon::parse($booking->appointment_date);

                    // Collect transaction data (will be batch inserted)
                    $allTransactions[] = [
                        'amount'       => $totalFee,
                        'status'       => 'Success',
                        'remarks'      => 'Adoption fee for ' . $animal->name,
                        'type'         => 'FPX Online Banking',
                        'bill_code'    => 'BILL-' . strtoupper(Str::random(8)),
                        'reference_no' => 'REF-' . $adoptionDate->format('Ymd') . '-' . rand(1000, 9999),
                        'userID'       => $booking->userID,
                        'created_at'   => $adoptionDate,
                        'updated_at'   => $adoptionDate,
                        // Temporary fields for adoption record creation
                        'adoption_fee' => $totalFee,
                        'adoption_remarks' => $animal->name . ' successfully adopted',
                        'bookingID'    => $booking->id,
                        'animalID'     => $animal->id,
                    ];

                    // Track adopted animals
                    $processedAnimals[] = $animal->id;
                    $adoptedAnimalIds[] = $animal->id;
                    $adoptionDates[$animal->id] = $adoptionDate;

                    // Track slots to update
                    if ($animal->slotID) {
                        $affectedSlotIds[] = $animal->slotID;
                    }

                    $adoptionCount++;

                    // Track age breakdown
                    if (in_array($animal->age, ['kitten', 'puppy'])) {
                        $ageBreakdown['young']++;
                    } elseif ($animal->age === 'senior') {
                        $ageBreakdown['senior']++;
                    } else {
                        $ageBreakdown['adult']++;
                    }
                }
            }

            // STEP 3: BATCH INSERT transactions and get IDs
            $this->command->info("Batch inserting {$adoptionCount} transactions...");
            // Use chunk size of 200 for SQL Server 2100 parameter limit
            // Each transaction has 9 fields, so 200 × 9 = 1800 parameters (safe)
            $chunkSize = 200;
            $transactionChunks = array_chunk($allTransactions, $chunkSize);
            $transactionIds = [];

            foreach ($transactionChunks as $chunkIndex => $chunk) {
                // Prepare transactions without temporary fields
                $transactionsToInsert = array_map(function($trans) {
                    return [
                        'amount'       => $trans['amount'],
                        'status'       => $trans['status'],
                        'remarks'      => $trans['remarks'],
                        'type'         => $trans['type'],
                        'bill_code'    => $trans['bill_code'],
                        'reference_no' => $trans['reference_no'],
                        'userID'       => $trans['userID'],
                        'created_at'   => $trans['created_at'],
                        'updated_at'   => $trans['updated_at'],
                    ];
                }, $chunk);

                // Batch insert
                DB::connection('danish')->table('transaction')->insert($transactionsToInsert);

                // Get inserted IDs (SQL Server approach - use bill_code for matching)
                $billCodes = array_column($chunk, 'bill_code');
                $insertedTransactions = DB::connection('danish')
                    ->table('transaction')
                    ->whereIn('bill_code', $billCodes)
                    ->orderBy('id', 'asc')
                    ->get(['id', 'bill_code']);

                // Map transaction IDs to original chunk order
                foreach ($chunk as $index => $trans) {
                    $matchingTrans = $insertedTransactions->firstWhere('bill_code', $trans['bill_code']);
                    if ($matchingTrans) {
                        // Create adoption record data
                        $allAdoptions[] = [
                            'fee'           => $trans['adoption_fee'],
                            'remarks'       => $trans['adoption_remarks'],
                            'bookingID'     => $trans['bookingID'],
                            'transactionID' => $matchingTrans->id,
                            'animalID'      => $trans['animalID'],
                            'created_at'    => $trans['created_at'],
                            'updated_at'    => $trans['updated_at'],
                        ];
                    }
                }

                $this->command->info("  Inserted transaction chunk " . ($chunkIndex + 1) . "/" . count($transactionChunks));
            }

            // STEP 4: BATCH INSERT adoptions
            $this->command->info("Batch inserting {$adoptionCount} adoption records...");
            $adoptionChunks = array_chunk($allAdoptions, $chunkSize);

            foreach ($adoptionChunks as $chunkIndex => $chunk) {
                DB::connection('danish')->table('adoption')->insert($chunk);
                $this->command->info("  Inserted adoption chunk " . ($chunkIndex + 1) . "/" . count($adoptionChunks));
            }

            // STEP 5: BATCH UPDATE animals (set slotID to NULL and status to Adopted)
            $this->command->info("Batch updating {$adoptionCount} animals to 'Adopted' status...");
            $animalChunks = array_chunk($adoptedAnimalIds, $chunkSize);

            foreach ($animalChunks as $chunkIndex => $chunk) {
                DB::connection('shafiqah')->table('animal')
                    ->whereIn('id', $chunk)
                    ->update([
                        'adoption_status' => 'Adopted',
                        'slotID'          => null, // Clear slot (animal leaves shelter)
                        'updated_at'      => now(),
                    ]);
                $this->command->info("  Updated animal chunk " . ($chunkIndex + 1) . "/" . count($animalChunks));
            }

            // STEP 6: BATCH UPDATE slots - Recalculate status based on remaining animals
            if (!empty($affectedSlotIds)) {
                $uniqueSlotIds = array_unique($affectedSlotIds);
                $this->command->info("Recalculating status for " . count($uniqueSlotIds) . " affected slots...");

                // Get slot capacities (1 query)
                $slots = DB::connection('atiqah')
                    ->table('slot')
                    ->whereIn('id', $uniqueSlotIds)
                    ->get(['id', 'capacity', 'name'])
                    ->keyBy('id');

                // Count remaining animals for ALL affected slots in ONE query (instead of N queries)
                $animalCounts = DB::connection('shafiqah')
                    ->table('animal')
                    ->whereIn('slotID', $uniqueSlotIds)
                    ->selectRaw('slotID, COUNT(*) as count')
                    ->groupBy('slotID')
                    ->pluck('count', 'slotID')
                    ->toArray();

                // Group slots by their new status for batch updates
                $slotsToOccupy = [];
                $slotsToFree = [];

                foreach ($slots as $slotId => $slot) {
                    $remainingAnimals = $animalCounts[$slotId] ?? 0;

                    // Determine new status
                    if ($remainingAnimals >= $slot->capacity) {
                        $slotsToOccupy[] = $slotId;
                    } else {
                        $slotsToFree[] = $slotId;
                    }
                }

                // Batch update slots by status (2 queries instead of N queries)
                if (!empty($slotsToOccupy)) {
                    DB::connection('atiqah')
                        ->table('slot')
                        ->whereIn('id', $slotsToOccupy)
                        ->update([
                            'status' => 'occupied',
                            'updated_at' => now(),
                        ]);
                }

                if (!empty($slotsToFree)) {
                    DB::connection('atiqah')
                        ->table('slot')
                        ->whereIn('id', $slotsToFree)
                        ->update([
                            'status' => 'available',
                            'updated_at' => now(),
                        ]);
                }

                $this->command->info("  Slot statuses recalculated (" . count($slotsToOccupy) . " occupied, " . count($slotsToFree) . " available)");
            }

            // Commit all transactions
            DB::connection('danish')->commit();
            DB::connection('shafiqah')->commit();
            DB::connection('atiqah')->commit();

            // Calculate statistics
            $totalAnimals = DB::connection('shafiqah')->table('animal')->count();
            $adoptionRate = round(($adoptionCount / $totalAnimals) * 100, 1);
            $stillAvailable = $totalAnimals - $adoptionCount;

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Adoption Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("{$adoptionCount} animals successfully adopted");
            $this->command->info("{$stillAvailable} animals still available at shelter");
            $this->command->info("Adoption rate: {$adoptionRate}% (realistic: 30-40%)");
            $this->command->info('');
            $this->command->info('Adoptions by Age:');
            $this->command->info("  - Young (kitten/puppy): {$ageBreakdown['young']}");
            $this->command->info("  - Adult: {$ageBreakdown['adult']}");
            $this->command->info("  - Senior: {$ageBreakdown['senior']}");
            $this->command->info('');
            $this->command->info('Updated Resources:');
            $this->command->info('  - Animal statuses updated (Shafiqah database)');
            $this->command->info('  - Slots freed up (Atiqah database)');
            $this->command->info('  - Transactions & adoptions created (Danish database)');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('danish')->rollBack();
            DB::connection('shafiqah')->rollBack();
            DB::connection('atiqah')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding adoptions: ' . $e->getMessage());
            $this->command->error('All transactions rolled back');

            throw $e;
        }
    }

    /**
     * Calculate adoption probability based on animal characteristics
     *
     * @param object $animal Animal record from database
     * @param object $booking Booking record from database
     * @return int Adoption probability percentage (0-100)
     */
    private function calculateAdoptionProbability($animal, $booking)
    {
        // Base probability by age
        if (in_array($animal->age, ['kitten', 'puppy'])) {
            $probability = 70; // Young animals: 70% base chance
        } elseif ($animal->age === 'senior') {
            $probability = 20; // Senior animals: 20% base chance
        } else {
            $probability = 35; // Adult animals: 35% base chance
        }

        // Health factor: +10% if healthy, -10% if sick/observation
        if ($animal->health_details === 'Healthy') {
            $probability += 10;
        } elseif (in_array($animal->health_details, ['Sick', 'Need Observation'])) {
            $probability -= 10;
        }

        // Time at shelter bonus: animals available longer get slight boost
        $arrivalDate = Carbon::parse($animal->created_at);
        $bookingDate = Carbon::parse($booking->appointment_date);
        $daysAtShelter = $arrivalDate->diffInDays($bookingDate);

        if ($daysAtShelter > 90) {
            $probability += 5; // +5% for animals waiting 90+ days
        } elseif ($daysAtShelter > 60) {
            $probability += 3; // +3% for animals waiting 60+ days
        }

        // Cap probability between 5% and 95%
        $probability = max(5, min(95, $probability));

        return $probability;
    }
}
