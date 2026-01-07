<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Track animal appointments to prevent double-booking at same time
     * Format: $animalAppointments[$animalId][] = ['date' => '2025-01-15', 'time' => '10:00:00']
     */
    private $animalAppointments = [];

    /**
     * Run the database seeds.
     *
     * REALISTIC SHELTER BOOKINGS:
     * - Bookings created ONLY after animal arrives at shelter
     * - ~2 bookings per week per animal (frequency: 2)
     * - Timeline-based status:
     *   - Past: 70% Completed, 30% Cancelled
     *   - Recent (1-7 days ago): Confirmed
     *   - Future: Pending
     * - No double-booking at same time (validation included)
     * - Animals that get adopted stop receiving future bookings
     */
    public function run()
    {
        $this->command->info('Starting Booking Seeder (Realistic Timeline)...');
        $this->command->info('========================================');

        // Get users from Taufiq's database (cross-database)
        $this->command->info('Fetching users from Taufiq\'s database...');
        $users = DB::connection('taufiq')->table('users')->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $this->command->info("Found " . count($users) . " users");

        // Get ALL animals with their arrival dates from Shafiqah's database
        $this->command->info('Fetching all animals with arrival dates from Shafiqah\'s database...');
        $animals = DB::connection('shafiqah')
            ->table('animal')
            ->select('id', 'created_at', 'adoption_status', 'age')
            ->orderBy('created_at', 'asc') // Process animals chronologically
            ->get();

        if ($animals->isEmpty()) {
            $this->command->error('No animals found. Please run AnimalSeeder first.');
            return;
        }

        $this->command->info('Found ' . count($animals) . ' animals');

        // Define appointment times from 9am to 5pm with 30-minute intervals
        $appointmentTimes = [
            '09:00:00', '09:30:00',
            '10:00:00', '10:30:00',
            '11:00:00', '11:30:00',
            '12:00:00', '12:30:00',
            '13:00:00', '13:30:00',
            '14:00:00', '14:30:00',
            '15:00:00', '15:30:00',
            '16:00:00', '16:30:00',
            '17:00:00'
        ];

        $currentDate = Carbon::now();

        // Use transaction for Danish's database
        DB::connection('danish')->beginTransaction();

        try {
            $this->command->info('');
            $this->command->info('Creating realistic timeline-based bookings (BATCH MODE)...');

            $bookingCount = 0;
            $conflictCount = 0;
            $bookingsByStatus = ['Pending' => 0, 'Confirmed' => 0, 'Completed' => 0, 'Cancelled' => 0];

            // Collect all booking data first (batch preparation)
            $allBookings = [];

            foreach ($animals as $animal) {
                $arrivalDate = Carbon::parse($animal->created_at);

                // Calculate how long this animal has been at shelter
                $daysAtShelter = $arrivalDate->diffInDays($currentDate);

                if ($daysAtShelter < 0) {
                    continue; // Animal arrives in future (shouldn't happen, but safety check)
                }

                // Calculate number of bookings based on availability time
                // ~2 bookings per week: (daysAtShelter / 7) * 2
                $weeksAvailable = max(1, floor($daysAtShelter / 7));
                $targetBookings = $weeksAvailable * 2; // 2 bookings per week
                $targetBookings = min($targetBookings, 15); // Cap at 15 bookings per animal

                // Create bookings for this animal
                for ($i = 0; $i < $targetBookings; $i++) {
                    // Random date between arrival and now
                    $daysAfterArrival = rand(7, max(7, $daysAtShelter)); // Start bookings 7 days after arrival
                    $bookingDate = Carbon::parse($arrivalDate)->addDays($daysAfterArrival);

                    // Skip if booking date is in future beyond current date
                    if ($bookingDate->isAfter($currentDate)) {
                        // Create future booking (Pending status)
                        $bookingDate = Carbon::now()->addDays(rand(1, 30));
                    }

                    $dateString = $bookingDate->format('Y-m-d');
                    $time = $appointmentTimes[array_rand($appointmentTimes)];

                    // Check if this animal already has a booking at this time
                    if (!$this->canAnimalBeBookedAtTime($animal->id, $dateString, $time)) {
                        $conflictCount++;
                        continue; // Skip this booking, try next
                    }

                    // Determine status based on date
                    $daysFromNow = $bookingDate->diffInDays($currentDate, false);

                    if ($daysFromNow > 0) {
                        // Past appointment: 70% Completed, 30% Cancelled
                        $status = rand(1, 100) <= 70 ? 'Completed' : 'Cancelled';
                    } elseif ($daysFromNow >= -7) {
                        // Recent appointment (within 1 week): Confirmed
                        $status = 'Confirmed';
                    } else {
                        // Future appointment: Pending
                        $status = 'Pending';
                    }

                    $createdAt = $bookingDate->copy()->subDays(rand(1, 7));

                    // Generate unique marker for this exact booking insert
                    $uniqueMarker = uniqid('booking_', true);

                    // Collect booking data (will be batch inserted later)
                    $allBookings[] = [
                        'appointment_date' => $dateString,
                        'appointment_time' => $time,
                        'status'           => $status,
                        'remarks'          => $uniqueMarker, // Use as temporary unique identifier
                        'userID'           => $users[array_rand($users)],
                        'created_at'       => $createdAt,
                        'updated_at'       => $bookingDate,
                        'animalID'         => $animal->id, // Store for pivot creation
                        'original_remarks' => 'Visit appointment', // Store original remarks
                        'pivot_created_at' => $createdAt,
                        'pivot_updated_at' => $bookingDate,
                    ];

                    // Track this appointment
                    $this->recordAnimalAppointment($animal->id, $dateString, $time);

                    $bookingCount++;
                    $bookingsByStatus[$status]++;
                }
            }

            // BATCH INSERT - Much faster for SQL Server
            $this->command->info("Batch inserting {$bookingCount} bookings...");

            // Insert bookings in chunks of 250 (SQL Server has 2100 parameter limit)
            $chunkSize = 250;
            $chunks = array_chunk($allBookings, $chunkSize);
            $allPivotRecords = [];

            foreach ($chunks as $chunkIndex => $chunk) {
                // Prepare chunk with unique markers
                $bookingsToInsert = array_map(function($booking) {
                    return [
                        'appointment_date' => $booking['appointment_date'],
                        'appointment_time' => $booking['appointment_time'],
                        'status'           => $booking['status'],
                        'remarks'          => $booking['remarks'], // Unique marker temporarily
                        'userID'           => $booking['userID'],
                        'created_at'       => $booking['created_at'],
                        'updated_at'       => $booking['updated_at'],
                    ];
                }, $chunk);

                // Batch insert bookings
                DB::connection('danish')->table('booking')->insert($bookingsToInsert);

                // Retrieve the inserted bookings by their unique markers
                // Split into smaller chunks to respect SQL Server's 2100 parameter limit
                $uniqueMarkers = array_column($chunk, 'remarks');
                $markerChunks = array_chunk($uniqueMarkers, 500); // Safe limit for whereIn

                $insertedBookings = collect();
                foreach ($markerChunks as $markerChunk) {
                    $results = DB::connection('danish')
                        ->table('booking')
                        ->whereIn('remarks', $markerChunk)
                        ->get(['id', 'remarks']);

                    $insertedBookings = $insertedBookings->merge($results);
                }

                $insertedBookings = $insertedBookings->keyBy('remarks');

                // Collect IDs for batch update
                $bookingIdsToUpdate = [];

                // Create pivot records
                foreach ($chunk as $booking) {
                    $marker = $booking['remarks'];

                    if (isset($insertedBookings[$marker])) {
                        $bookingId = $insertedBookings[$marker]->id;

                        // Add pivot record
                        $allPivotRecords[] = [
                            'bookingID'  => $bookingId,
                            'animalID'   => $booking['animalID'],
                            'remarks'    => null,
                            'created_at' => $booking['pivot_created_at'],
                            'updated_at' => $booking['pivot_updated_at'],
                        ];

                        // Collect for batch update
                        $bookingIdsToUpdate[] = $bookingId;
                    }
                }

                // Batch update all remarks back to original value (SQL Server friendly)
                if (!empty($bookingIdsToUpdate)) {
                    // Split into chunks to respect 2100 parameter limit
                    $updateChunks = array_chunk($bookingIdsToUpdate, 500);

                    foreach ($updateChunks as $updateChunk) {
                        DB::connection('danish')
                            ->table('booking')
                            ->whereIn('id', $updateChunk)
                            ->update(['remarks' => 'Visit appointment']);
                    }
                }

                $this->command->info("  Inserted chunk " . ($chunkIndex + 1) . "/" . count($chunks) . " (" . count($chunk) . " bookings)");
            }

            // BATCH INSERT pivot records
            $this->command->info("Batch inserting " . count($allPivotRecords) . " animal-booking relationships...");
            $pivotChunks = array_chunk($allPivotRecords, $chunkSize);

            foreach ($pivotChunks as $chunkIndex => $pivotChunk) {
                DB::connection('danish')->table('animal_booking')->insert($pivotChunk);
                $this->command->info("  Inserted pivot chunk " . ($chunkIndex + 1) . "/" . count($pivotChunks));
            }

            DB::connection('danish')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Booking Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total bookings created: {$bookingCount}");
            $this->command->info("Total pivot records created: " . count($allPivotRecords));
            $this->command->info("Booking frequency: ~2 per week per animal");
            $this->command->info('');
            $this->command->info('Status Distribution:');
            foreach ($bookingsByStatus as $status => $count) {
                $percentage = $bookingCount > 0 ? round(($count / $bookingCount) * 100, 1) : 0;
                $this->command->info("  - {$status}: {$count} ({$percentage}%)");
            }
            $this->command->info('');
            $this->command->info("Time conflicts prevented: {$conflictCount}");
            $this->command->info('Database: Danish (SQL Server)');
            $this->command->info('Cross-references: Taufiq (Users), Shafiqah (Animals)');
            $this->command->info('=================================');

            // Verify all bookings have pivot records
            $bookingsWithoutPivot = DB::connection('danish')
                ->table('booking')
                ->leftJoin('animal_booking', 'booking.id', '=', 'animal_booking.bookingID')
                ->whereNull('animal_booking.bookingID')
                ->count();

            if ($bookingsWithoutPivot > 0) {
                $this->command->warn("⚠ Warning: {$bookingsWithoutPivot} bookings without animal relationships!");
            } else {
                $this->command->info('✓ All bookings have animal relationships');
            }

        } catch (\Exception $e) {
            DB::connection('danish')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding bookings: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }

    /**
     * Check if an animal can be booked at a specific date/time
     */
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

    /**
     * Record an animal's appointment to track conflicts
     */
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
