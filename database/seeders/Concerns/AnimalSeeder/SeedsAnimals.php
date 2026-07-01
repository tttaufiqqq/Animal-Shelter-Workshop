<?php

namespace Database\Seeders\Concerns\AnimalSeeder;

use Illuminate\Support\Facades\DB;

trait SeedsAnimals
{
    public function run(): void
    {
        $this->command->info('Starting Animal Seeder...');
        $this->command->info('========================================');

        $catImages = [];
        $dogImages = [];
        for ($i = 1; $i <= 8; $i++) { $catImages[] = "animal_images/cat{$i}.jpg"; }
        for ($i = 1; $i <= 6; $i++) { $dogImages[] = "animal_images/dog{$i}.jpg"; }

        $this->command->info("Fetching successful rescues from Eilya - Stray Reporting database...");
        $successfulRescues = DB::connection('reporting')->table('rescue')->where('status', 'Success')->get();
        if ($successfulRescues->isEmpty()) {
            $this->command->error('No successful rescues found. Please run RescueSeeder first.');
            return;
        }
        $this->command->info("Found " . $successfulRescues->count() . " successful rescues");

        $this->command->info("Fetching available slots from Atiqah - Shelter Management database...");
        $availableSlots = DB::connection('shelter')->table('slot')->where('status', 'available')->get();
        if ($availableSlots->isEmpty()) {
            $this->command->error('No available slots found. Please run SectionSlotSeeder first.');
            return;
        }
        $this->command->info("Found " . $availableSlots->count() . " available slots");

        $this->command->info("Fetching vets from Shafiqah - Stray Animal database...");
        $vets = DB::connection('animals')->table('vet')->get();
        if ($vets->isEmpty()) {
            $this->command->warn('No vets found. Vaccination records will not be created. Please run ClinicVetSeeder first.');
        } else {
            $this->command->info("Found " . $vets->count() . " vets");
        }

        [$animals, $rescueGroups] = $this->buildAnimalRecords($successfulRescues, $availableSlots);

        $createdAnimals = [];
        DB::connection('animals')->beginTransaction();
        try {
            $this->command->info('');
            $this->command->info("Inserting animals into Shafiqah - Stray Animal database...");
            foreach ($animals as $animalData) {
                $animalId = DB::connection('animals')->table('animal')->insertGetId($animalData);
                $animal   = (object) $animalData;
                $animal->id = $animalId;
                $createdAnimals[] = $animal;
            }

            $dbNotAdopted = DB::connection('animals')->table('animal')->where('adoption_status', 'Not Adopted')->count();
            $this->command->info("VERIFICATION - All animals marked as 'Not Adopted': {$dbNotAdopted}");

            if ($vets->isNotEmpty()) {
                $this->createVaccinationRecords($createdAnimals, $vets);
                $this->createMedicalRecords($createdAnimals, $vets);
            }

            DB::connection('animals')->commit();
            $this->command->info("✓ Shafiqah - Stray Animal transaction committed successfully");
        } catch (\Exception $e) {
            DB::connection('animals')->rollBack();
            $this->command->error("Failed to create animals in Shafiqah - Stray Animal - Stray Animal: " . $e->getMessage());
            throw $e;
        }

        $assignedSlotIds = $this->updateSlotStatuses($animals);

        try {
            $this->command->info('');
            $this->command->info('Uploading animal seed images to Cloudinary...');
            $this->command->info('(This may take a moment for first-time uploads)');
            $this->assignImagesToAnimals($createdAnimals, $catImages, $dogImages);
            $this->command->info("✓ Images assigned successfully in Eilya - Stray Reporting - Stray Reporting database");
        } catch (\Exception $e) {
            $this->command->error("Failed to assign images in Eilya - Stray Reporting - Stray Reporting: " . $e->getMessage());
        }

        $ageCount        = array_count_values(array_column($animals, 'age'));
        $animalsPerRescue = [];
        foreach ($animals as $animal) {
            $rescueId = $animal['rescueID'];
            $animalsPerRescue[$rescueId] = ($animalsPerRescue[$rescueId] ?? 0) + 1;
        }

        $this->command->info('');
        $this->command->info('=================================');
        $this->command->info('✓ Animal Seeding Completed!');
        $this->command->info('=================================');
        $this->command->info("Total animals created: " . count($animals));
        $this->command->info("Rescue operations used: " . count($rescueGroups));
        $this->command->info("Animals per rescue: " . implode(', ', $animalsPerRescue));
        $this->command->info('');
        $this->command->info("Initial Status (Realistic Shelter):");
        $this->command->info("  - ALL animals start as 'Not Adopted': " . count($animals));
        $this->command->info("  - Slots occupied: " . count($assignedSlotIds));
        $this->command->info("  - Adoptions will be processed by AdoptionSeeder (30-40% expected)");
        $this->command->info('');
        $this->command->info('Age Distribution:');
        foreach ($ageCount as $category => $count) {
            $this->command->info("  - " . ucfirst($category) . ": {$count}");
        }
        $this->command->info('');
        $this->command->info("Database: Shafiqah - Stray Animal (MySQL) - Animals, Medical, Vaccinations");
        $this->command->info("Cross-references: Eilya - Stray Reporting (Rescues, Images), Atiqah - Shelter Management (Slots)");
        $this->command->info('=================================');
    }
}
