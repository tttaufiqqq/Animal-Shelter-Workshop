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
            $this->command->info('Processing bookings for adoptions (30-40% success rate)...');

            $adoptionCount = 0;
            $processedAnimals = []; // Track which animals have been adopted
            $ageBreakdown = ['young' => 0, 'adult' => 0, 'senior' => 0];

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

                    // Get animal details from Shafiqah's database
                    $animal = DB::connection('shafiqah')
                        ->table('animal')
                        ->where('id', $animalId)
                        ->first();

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

                    // Calculate adoption fee
                    $species = strtolower($animal->species);
                    $baseFee = $speciesBaseFees[$species] ?? 15;

                    $medicalCount = DB::connection('shafiqah')
                        ->table('medical')
                        ->where('animalID', $animal->id)
                        ->count();
                    $medicalFee = $medicalCount * $medicalRate;

                    $vaccinationCount = DB::connection('shafiqah')
                        ->table('vaccination')
                        ->where('animalID', $animal->id)
                        ->count();
                    $vaccinationFee = $vaccinationCount * $vaccinationRate;

                    $totalFee = $baseFee + $medicalFee + $vaccinationFee;

                    $adoptionDate = Carbon::parse($booking->appointment_date);

                    // Create transaction
                    $transactionId = DB::connection('danish')->table('transaction')->insertGetId([
                        'amount'       => $totalFee,
                        'status'       => 'Success',
                        'remarks'      => 'Adoption fee for ' . $animal->name,
                        'type'         => 'FPX Online Banking',
                        'bill_code'    => 'BILL-' . strtoupper(Str::random(8)),
                        'reference_no' => 'REF-' . $adoptionDate->format('Ymd') . '-' . rand(1000, 9999),
                        'userID'       => $booking->userID,
                        'created_at'   => $adoptionDate,
                        'updated_at'   => $adoptionDate,
                    ]);

                    // Create adoption record
                    DB::connection('danish')->table('adoption')->insert([
                        'fee'           => $totalFee,
                        'remarks'       => $animal->name . ' successfully adopted',
                        'bookingID'     => $booking->id,
                        'transactionID' => $transactionId,
                        'animalID'      => $animal->id,
                        'created_at'    => $adoptionDate,
                        'updated_at'    => $adoptionDate,
                    ]);

                    // Update animal status to "Adopted" in Shafiqah's database
                    DB::connection('shafiqah')->table('animal')
                        ->where('id', $animal->id)
                        ->update([
                            'adoption_status' => 'Adopted',
                            'updated_at'      => $adoptionDate,
                        ]);

                    // Free up the slot in Atiqah's database (if animal had a slot)
                    if ($animal->slotID) {
                        DB::connection('atiqah')->table('slot')
                            ->where('id', $animal->slotID)
                            ->update([
                                'status'     => 'available',
                                'updated_at' => $adoptionDate,
                            ]);
                    }

                    // Track this adoption
                    $processedAnimals[] = $animal->id;
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
