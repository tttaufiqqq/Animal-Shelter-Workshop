<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Concerns\AdoptionSeeder\BuildsAdoptionData;
use Database\Seeders\Concerns\AdoptionSeeder\InsertsAdoptionRecords;
use Database\Seeders\Concerns\AdoptionSeeder\UpdatesAdoptedSlots;

class AdoptionSeeder extends Seeder
{
    use BuildsAdoptionData, InsertsAdoptionRecords, UpdatesAdoptedSlots;

    public function run(): void
    {
        $this->command->info('Starting Adoption Seeder (SQL Server Optimized)...');

        $atiqahAvailable = false;
        try {
            DB::connection('shelter')->getPdo();
            $atiqahAvailable = true;
            $this->command->info('Atiqah: ONLINE');
        } catch (\Exception $e) {
            $this->command->warn('Atiqah: OFFLINE (slot updates skipped)');
        }

        $completedBookings = DB::connection('booking')->table('booking')
            ->where('status', 'Completed')->orderBy('id', 'asc')->get();

        if ($completedBookings->isEmpty()) {
            $this->command->warn('No completed bookings found.');
            return;
        }
        $this->command->info("Found " . $completedBookings->count() . " completed bookings");

        $bookingIds    = $completedBookings->pluck('id')->toArray();
        $animalBookings = DB::connection('booking')->table('animal_booking')
            ->whereIn('bookingID', $bookingIds)->get();

        $animalsByBooking = [];
        foreach ($animalBookings as $ab) {
            $animalsByBooking[$ab->bookingID][] = $ab->animalID;
        }

        $allAnimalIds = $animalBookings->pluck('animalID')->unique()->toArray();
        $animalsData  = DB::connection('animals')->table('animal')
            ->whereIn('id', $allAnimalIds)->get()->keyBy('id');

        $medicalCounts = DB::connection('animals')->table('medical')
            ->whereIn('animalID', $allAnimalIds)
            ->selectRaw('animalID, COUNT(*) as cnt')->groupBy('animalID')
            ->pluck('cnt', 'animalID')->toArray();

        $vaccinationCounts = DB::connection('animals')->table('vaccination')
            ->whereIn('animalID', $allAnimalIds)
            ->selectRaw('animalID, COUNT(*) as cnt')->groupBy('animalID')
            ->pluck('cnt', 'animalID')->toArray();

        $this->command->info('Generating adoption data...');
        [$allTransactions, $transactionMeta, $adoptedAnimalIds, $affectedSlotIds] = $this->buildAdoptionData(
            $completedBookings, $animalsByBooking, $animalsData, $medicalCounts, $vaccinationCounts
        );

        $this->command->info("Creating " . count($allTransactions) . " transactions, " . count($adoptedAnimalIds) . " adoptions...");
        $this->insertAdoptionRecords($allTransactions, $transactionMeta, $animalsData, $medicalCounts, $vaccinationCounts);

        $this->command->info('Updating animal statuses in Shafiqah...');
        foreach (array_chunk($adoptedAnimalIds, 50) as $chunk) {
            DB::connection('animals')->table('animal')->whereIn('id', $chunk)
                ->update(['adoption_status' => 'Adopted', 'slotID' => null, 'updated_at' => now()]);
        }

        $this->updateAdoptedSlots($affectedSlotIds, $atiqahAvailable);

        $totalAdoptions = DB::connection('booking')->table('adoption')->count();
        $totalTrans     = DB::connection('booking')->table('transaction')->count();

        $this->command->info('');
        $this->command->info('=== Adoption Seeder Complete ===');
        $this->command->info("Transactions: {$totalTrans}");
        $this->command->info("Adoptions: {$totalAdoptions}");
        $this->command->info("Animals adopted: " . count($adoptedAnimalIds));
    }
}
