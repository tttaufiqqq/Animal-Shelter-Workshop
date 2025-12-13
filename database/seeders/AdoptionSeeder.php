<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\Adoption;
use App\Models\Animal;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdoptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Adoptions and Transactions are stored in Danish's database
     * Cross-database references to:
     * - Animals (Shafiqah's database - with medical/vaccination data)
     * - Bookings (Danish's database)
     * - Users (Taufiq's database)
     */
    public function run()
    {
        $this->command->info('Starting Adoption Seeder...');
        $this->command->info('========================================');

        // Get animals that are already marked as 'Adopted' from Shafiqah's database (cross-database)
        $this->command->info('Fetching adopted animals from Shafiqah\'s database...');
        $adoptedAnimals = DB::connection('shafiqah')
            ->table('animal')
            ->where('adoption_status', 'Adopted')
            ->get();

        if ($adoptedAnimals->isEmpty()) {
            $this->command->warn('No adopted animals found. Adoptions will not be created.');
            return;
        }

        $this->command->info("Found " . $adoptedAnimals->count() . " adopted animals");

        // Fee structure
        $speciesBaseFees = [
            'dog' => 20,
            'cat' => 10,
        ];
        $medicalRate = 10;
        $vaccinationRate = 20;

        $adoptionCount = 0;

        // Group adopted animals by creating fake user assignments
        // Each adoption typically has 1-2 animals
        $animalGroups = $adoptedAnimals->chunk(rand(1, 2));

        // Use transaction for Danish's database
        DB::connection('danish')->beginTransaction();

        try {
            $this->command->info('');
            $this->command->info('Creating adoption records in Danish\'s database...');

            foreach ($animalGroups as $animalGroup) {
                // Get a random completed booking from Danish's database
                // In real scenario, these animals would have been in the booking
                $completedBooking = DB::connection('danish')
                    ->table('booking')
                    ->whereIn('status', ['completed', 'Completed'])
                    ->inRandomOrder()
                    ->first();

                if (!$completedBooking) {
                    $this->command->warn('No completed bookings found. Skipping remaining adoptions.');
                    break;
                }

                // Calculate total fee based on actual fee structure
                $totalFee = 0;
                $animalFees = [];

                foreach ($animalGroup as $animal) {
                    // Base fee by species
                    $species = strtolower($animal->species);
                    $baseFee = $speciesBaseFees[$species] ?? 15; // Default to $15 if species not in list

                    // Medical records fee (count from Shafiqah's database)
                    $medicalCount = DB::connection('shafiqah')
                        ->table('medical')
                        ->where('animalID', $animal->id)
                        ->count();
                    $medicalFee = $medicalCount * $medicalRate;

                    // Vaccination fee (count from Shafiqah's database)
                    $vaccinationCount = DB::connection('shafiqah')
                        ->table('vaccination')
                        ->where('animalID', $animal->id)
                        ->count();
                    $vaccinationFee = $vaccinationCount * $vaccinationRate;

                    // Total fee for this animal
                    $animalFee = $baseFee + $medicalFee + $vaccinationFee;
                    $animalFees[$animal->id] = $animalFee;
                    $totalFee += $animalFee;
                }

                // Create a transaction for this adoption in Danish's database
                $adoptionDate = Carbon::parse($animalGroup->first()->updated_at); // Use animal's adoption date

                $transactionId = DB::connection('danish')->table('transaction')->insertGetId([
                    'amount'       => $totalFee,
                    'status'       => 'Success',
                    'remarks'      => 'Adoption fee for ' . $animalGroup->count() . ' animal(s)',
                    'type'         => 'FPX Online Banking',
                    'bill_code'    => 'BILL-' . strtoupper(Str::random(8)),
                    'reference_no' => 'REF-' . $adoptionDate->format('Ymd') . '-' . rand(1000, 9999),
                    'userID'       => $completedBooking->userID, // Cross-database reference to Taufiq
                    'created_at'   => $adoptionDate,
                    'updated_at'   => $adoptionDate,
                ]);

                // Create one adoption record per animal in this group in Danish's database
                foreach ($animalGroup as $animal) {
                    DB::connection('danish')->table('adoption')->insert([
                        'fee'           => $animalFees[$animal->id],
                        'remarks'       => $animal->name . ' Adopted',
                        'bookingID'     => $completedBooking->id,
                        'transactionID' => $transactionId,
                        'animalID'      => $animal->id,  // Cross-database reference to Shafiqah's animal
                        'created_at'    => $adoptionDate,
                        'updated_at'    => $adoptionDate,
                    ]);

                    // Animal is ALREADY marked as 'Adopted' from AnimalSeeder
                    // No need to update status again

                    $adoptionCount++;
                }
            }

            DB::connection('danish')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Adoption Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("{$adoptionCount} adoption records created for adopted animals");
            $this->command->info('Database: Danish (SQL Server) - Adoptions, Transactions');
            $this->command->info('Cross-references: Shafiqah (Animals), Taufiq (Users)');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('danish')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding adoptions: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }
}
