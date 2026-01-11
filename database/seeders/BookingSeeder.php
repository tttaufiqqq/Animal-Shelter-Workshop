<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * OPTIMIZED FOR SQL SERVER - No long transactions, small batches
     */
    public function run()
    {
        $this->command->info('Starting Booking Seeder (600 Bookings - SQL Server Optimized)...');

        // Get NON-ADMIN users from Taufiq's database
        $adminRole = DB::connection('taufiq')->table('roles')->where('name', 'admin')->first();
        $adminUserIds = $adminRole
            ? DB::connection('taufiq')->table('model_has_roles')
                ->where('role_id', $adminRole->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id')->toArray()
            : [];

        $users = DB::connection('taufiq')->table('users')
            ->whereNotIn('id', $adminUserIds)
            ->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->error('No non-admin users found.');
            return;
        }
        $this->command->info("Found " . count($users) . " non-admin users");

        // Get animals from Shafiqah's database
        $animals = DB::connection('shafiqah')->table('animal')->pluck('id')->toArray();
        if (empty($animals)) {
            $this->command->error('No animals found.');
            return;
        }
        $this->command->info('Found ' . count($animals) . ' animals');

        $appointmentTimes = ['09:00:00','09:30:00','10:00:00','10:30:00','11:00:00','11:30:00',
            '12:00:00','12:30:00','13:00:00','13:30:00','14:00:00','14:30:00',
            '15:00:00','15:30:00','16:00:00','16:30:00','17:00:00'];

        // Status distribution: Completed 45%, Cancelled 15%, Confirmed 20%, Pending 20%
        $statusQueue = array_merge(
            array_fill(0, 270, 'Completed'),
            array_fill(0, 90, 'Cancelled'),
            array_fill(0, 120, 'Confirmed'),
            array_fill(0, 120, 'Pending')
        );
        shuffle($statusQueue);

        $this->command->info('Generating booking data...');

        // Pre-generate all data in memory (fast)
        $allBookings = [];
        $bookingAnimals = []; // bookingIndex => [animalIds]

        for ($i = 0; $i < 600; $i++) {
            $status = $statusQueue[$i];

            // Date based on status
            $bookingDate = match($status) {
                'Completed' => Carbon::now()->subDays(rand(7, 365)),
                'Cancelled' => Carbon::now()->subDays(rand(1, 180)),
                'Confirmed' => Carbon::now()->addDays(rand(1, 14)),
                'Pending' => Carbon::now()->addDays(rand(7, 60)),
            };

            // Animal count: 60% one, 30% two, 10% three
            $roll = rand(1, 100);
            $animalCount = $roll <= 60 ? 1 : ($roll <= 90 ? 2 : 3);

            // Pick random animals
            $shuffled = $animals;
            shuffle($shuffled);
            $selectedAnimals = array_slice($shuffled, 0, min($animalCount, count($shuffled)));

            $createdAt = $bookingDate->copy()->subDays(rand(1, 5));

            $allBookings[] = [
                'appointment_date' => $bookingDate->format('Y-m-d'),
                'appointment_time' => $appointmentTimes[array_rand($appointmentTimes)],
                'status' => $status,
                'remarks' => 'Visit appointment',
                'userID' => $users[array_rand($users)],
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $bookingDate->format('Y-m-d H:i:s'),
            ];
            $bookingAnimals[$i] = $selectedAnimals;
        }

        $this->command->info('Inserting bookings (no transaction for SQL Server stability)...');

        // Insert bookings in small chunks WITHOUT transaction
        $chunkSize = 50; // Small chunks for SQL Server over SSH
        $chunks = array_chunk($allBookings, $chunkSize);
        $insertedIds = [];

        foreach ($chunks as $idx => $chunk) {
            DB::connection('danish')->table('booking')->insert($chunk);
            $this->command->info("  Bookings: " . (($idx + 1) * $chunkSize) . "/600");
        }

        // Get all booking IDs (ordered by id to match insertion order)
        $insertedIds = DB::connection('danish')->table('booking')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->toArray();

        // Take only the last 600 (in case there were existing records)
        $insertedIds = array_slice($insertedIds, -600);

        $this->command->info('Inserting animal-booking relationships...');

        // Build pivot records
        $pivotRecords = [];
        foreach ($bookingAnimals as $bookingIndex => $animalIds) {
            if (!isset($insertedIds[$bookingIndex])) continue;
            $bookingId = $insertedIds[$bookingIndex];

            foreach ($animalIds as $animalId) {
                $pivotRecords[] = [
                    'bookingID' => $bookingId,
                    'animalID' => $animalId,
                    'remarks' => null,
                    'created_at' => $allBookings[$bookingIndex]['created_at'],
                    'updated_at' => $allBookings[$bookingIndex]['updated_at'],
                ];
            }
        }

        // Insert pivot records in small chunks
        $pivotChunks = array_chunk($pivotRecords, $chunkSize);
        foreach ($pivotChunks as $idx => $chunk) {
            DB::connection('danish')->table('animal_booking')->insert($chunk);
        }

        // Quick stats
        $totalBookings = DB::connection('danish')->table('booking')->count();
        $totalPivots = DB::connection('danish')->table('animal_booking')->count();

        $this->command->info('');
        $this->command->info('=== Booking Seeder Complete ===');
        $this->command->info("Bookings: {$totalBookings}");
        $this->command->info("Animal-Booking links: {$totalPivots}");
    }
}
