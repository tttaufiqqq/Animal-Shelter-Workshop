<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Track animal appointments to prevent double-booking at same time
     */
    private $animalAppointments = [];

    /**
     * Run the database seeds.
     *
     * Creates exactly 600 bookings with:
     * - Every booking has at least 1 animal (some have 2-3)
     * - Variety of statuses: Pending, Confirmed, Completed, Cancelled
     * - Realistic timeline-based distribution
     */
    public function run()
    {
        $this->command->info('Starting Booking Seeder (600 Bookings)...');
        $this->command->info('========================================');

        // Get users from Taufiq's database
        $this->command->info('Fetching users from Taufiq\'s database...');
        $users = DB::connection('taufiq')->table('users')->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $this->command->info("Found " . count($users) . " users");

        // Get ALL animals from Shafiqah's database
        $this->command->info('Fetching animals from Shafiqah\'s database...');
        $animals = DB::connection('shafiqah')
            ->table('animal')
            ->select('id', 'created_at', 'name')
            ->get()
            ->toArray();

        if (empty($animals)) {
            $this->command->error('No animals found. Please run AnimalSeeder first.');
            return;
        }

        $this->command->info('Found ' . count($animals) . ' animals');

        // Define appointment times (9am to 5pm, 30-minute intervals)
        $appointmentTimes = [
            '09:00:00', '09:30:00', '10:00:00', '10:30:00',
            '11:00:00', '11:30:00', '12:00:00', '12:30:00',
            '13:00:00', '13:30:00', '14:00:00', '14:30:00',
            '15:00:00', '15:30:00', '16:00:00', '16:30:00', '17:00:00'
        ];

        $currentDate = Carbon::now();
        $targetBookings = 600;

        // Status distribution for variety:
        // - Completed: 45% (past appointments that happened)
        // - Cancelled: 15% (past appointments that were cancelled)
        // - Confirmed: 20% (upcoming confirmed appointments)
        // - Pending: 20% (future pending appointments)
        $statusDistribution = [
            'Completed' => 270,  // 45%
            'Cancelled' => 90,   // 15%
            'Confirmed' => 120,  // 20%
            'Pending' => 120,    // 20%
        ];

        DB::connection('danish')->beginTransaction();

        try {
            $this->command->info('');
            $this->command->info('Creating 600 bookings with proper animal relationships...');

            $bookingCount = 0;
            $bookingsByStatus = ['Pending' => 0, 'Confirmed' => 0, 'Completed' => 0, 'Cancelled' => 0];
            $allBookings = [];
            $statusQueue = [];

            // Create status queue for random distribution
            foreach ($statusDistribution as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $statusQueue[] = $status;
                }
            }
            shuffle($statusQueue);

            // Generate 600 bookings
            for ($i = 0; $i < $targetBookings; $i++) {
                $status = $statusQueue[$i];

                // Determine booking date based on status
                switch ($status) {
                    case 'Completed':
                        // Past: 7 to 365 days ago
                        $bookingDate = Carbon::now()->subDays(rand(7, 365));
                        break;
                    case 'Cancelled':
                        // Past: 1 to 180 days ago
                        $bookingDate = Carbon::now()->subDays(rand(1, 180));
                        break;
                    case 'Confirmed':
                        // Near future: 1 to 14 days
                        $bookingDate = Carbon::now()->addDays(rand(1, 14));
                        break;
                    case 'Pending':
                        // Future: 7 to 60 days
                        $bookingDate = Carbon::now()->addDays(rand(7, 60));
                        break;
                }

                $dateString = $bookingDate->format('Y-m-d');
                $time = $appointmentTimes[array_rand($appointmentTimes)];

                // Determine number of animals for this booking (1-3)
                // 60% single animal, 30% two animals, 10% three animals
                $animalCountRoll = rand(1, 100);
                if ($animalCountRoll <= 60) {
                    $animalCount = 1;
                } elseif ($animalCountRoll <= 90) {
                    $animalCount = 2;
                } else {
                    $animalCount = 3;
                }

                // Select random animals for this booking
                $selectedAnimals = [];
                $availableAnimals = $animals;
                shuffle($availableAnimals);

                for ($j = 0; $j < $animalCount && !empty($availableAnimals); $j++) {
                    $animal = array_pop($availableAnimals);

                    // Check for time conflict
                    if ($this->canAnimalBeBookedAtTime($animal->id, $dateString, $time)) {
                        $selectedAnimals[] = $animal->id;
                        $this->recordAnimalAppointment($animal->id, $dateString, $time);
                    }
                }

                // Ensure at least 1 animal (try more if needed)
                $attempts = 0;
                while (empty($selectedAnimals) && $attempts < 10) {
                    $animal = $animals[array_rand($animals)];
                    $altTime = $appointmentTimes[array_rand($appointmentTimes)];

                    if ($this->canAnimalBeBookedAtTime($animal->id, $dateString, $altTime)) {
                        $selectedAnimals[] = $animal->id;
                        $this->recordAnimalAppointment($animal->id, $dateString, $altTime);
                        $time = $altTime;
                    }
                    $attempts++;
                }

                // Skip if still no animals (very rare)
                if (empty($selectedAnimals)) {
                    continue;
                }

                $createdAt = $bookingDate->copy()->subDays(rand(1, 7));
                $uniqueMarker = uniqid('bk_', true);

                $allBookings[] = [
                    'appointment_date' => $dateString,
                    'appointment_time' => $time,
                    'status'           => $status,
                    'remarks'          => $uniqueMarker,
                    'userID'           => $users[array_rand($users)],
                    'created_at'       => $createdAt,
                    'updated_at'       => $bookingDate,
                    'animalIDs'        => $selectedAnimals,
                    'pivot_created_at' => $createdAt,
                    'pivot_updated_at' => $bookingDate,
                ];

                $bookingCount++;
                $bookingsByStatus[$status]++;
            }

            $this->command->info("Prepared {$bookingCount} bookings, inserting in batches...");

            // Batch insert bookings
            $chunkSize = 200;
            $chunks = array_chunk($allBookings, $chunkSize);
            $allPivotRecords = [];

            foreach ($chunks as $chunkIndex => $chunk) {
                // Prepare booking records
                $bookingsToInsert = array_map(function($booking) {
                    return [
                        'appointment_date' => $booking['appointment_date'],
                        'appointment_time' => $booking['appointment_time'],
                        'status'           => $booking['status'],
                        'remarks'          => $booking['remarks'],
                        'userID'           => $booking['userID'],
                        'created_at'       => $booking['created_at'],
                        'updated_at'       => $booking['updated_at'],
                    ];
                }, $chunk);

                // Insert bookings
                DB::connection('danish')->table('booking')->insert($bookingsToInsert);

                // Retrieve inserted bookings by unique markers
                $uniqueMarkers = array_column($chunk, 'remarks');
                $insertedBookings = DB::connection('danish')
                    ->table('booking')
                    ->whereIn('remarks', $uniqueMarkers)
                    ->get(['id', 'remarks'])
                    ->keyBy('remarks');

                $bookingIdsToUpdate = [];

                // Create pivot records for each booking's animals
                foreach ($chunk as $booking) {
                    $marker = $booking['remarks'];

                    if (isset($insertedBookings[$marker])) {
                        $bookingId = $insertedBookings[$marker]->id;
                        $bookingIdsToUpdate[] = $bookingId;

                        // Add pivot record for each animal
                        foreach ($booking['animalIDs'] as $animalId) {
                            $allPivotRecords[] = [
                                'bookingID'  => $bookingId,
                                'animalID'   => $animalId,
                                'remarks'    => null,
                                'created_at' => $booking['pivot_created_at'],
                                'updated_at' => $booking['pivot_updated_at'],
                            ];
                        }
                    }
                }

                // Update remarks back to proper value
                if (!empty($bookingIdsToUpdate)) {
                    DB::connection('danish')
                        ->table('booking')
                        ->whereIn('id', $bookingIdsToUpdate)
                        ->update(['remarks' => 'Visit appointment']);
                }

                $this->command->info("  Inserted chunk " . ($chunkIndex + 1) . "/" . count($chunks));
            }

            // Batch insert pivot records
            $this->command->info("Inserting " . count($allPivotRecords) . " animal-booking relationships...");
            $pivotChunks = array_chunk($allPivotRecords, $chunkSize);

            foreach ($pivotChunks as $chunkIndex => $pivotChunk) {
                DB::connection('danish')->table('animal_booking')->insert($pivotChunk);
            }

            DB::connection('danish')->commit();

            // Verification
            $actualBookings = DB::connection('danish')->table('booking')->count();
            $actualPivots = DB::connection('danish')->table('animal_booking')->count();
            $bookingsWithAnimals = DB::connection('danish')
                ->table('booking')
                ->join('animal_booking', 'booking.id', '=', 'animal_booking.bookingID')
                ->distinct()
                ->count('booking.id');

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('Booking Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total bookings: {$actualBookings}");
            $this->command->info("Total animal-booking links: {$actualPivots}");
            $this->command->info("Bookings with animals: {$bookingsWithAnimals}");
            $this->command->info('');
            $this->command->info('Status Distribution:');
            foreach ($bookingsByStatus as $status => $count) {
                $percentage = round(($count / $bookingCount) * 100, 1);
                $this->command->info("  - {$status}: {$count} ({$percentage}%)");
            }

            // Verify no orphan bookings
            $orphanBookings = DB::connection('danish')
                ->table('booking')
                ->leftJoin('animal_booking', 'booking.id', '=', 'animal_booking.bookingID')
                ->whereNull('animal_booking.bookingID')
                ->count();

            if ($orphanBookings > 0) {
                $this->command->warn("Warning: {$orphanBookings} bookings without animals!");
            } else {
                $this->command->info('All bookings have at least 1 animal attached!');
            }

        } catch (\Exception $e) {
            DB::connection('danish')->rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function canAnimalBeBookedAtTime($animalId, $date, $time)
    {
        if (!isset($this->animalAppointments[$animalId])) {
            return true;
        }

        foreach ($this->animalAppointments[$animalId] as $appointment) {
            if ($appointment['date'] === $date && $appointment['time'] === $time) {
                return false;
            }
        }

        return true;
    }

    private function recordAnimalAppointment($animalId, $date, $time)
    {
        if (!isset($this->animalAppointments[$animalId])) {
            $this->animalAppointments[$animalId] = [];
        }

        $this->animalAppointments[$animalId][] = [
            'date' => $date,
            'time' => $time,
        ];
    }
}
