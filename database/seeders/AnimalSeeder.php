<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Animal;
use App\Models\Rescue;
use App\Models\Slot;
use App\Models\Vet;
use App\Models\Vaccination;
use App\Models\Medical;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AnimalSeeder extends Seeder
{
    /**
     * Cache for uploaded Cloudinary paths to avoid re-uploading the same image
     */
    private $cloudinaryCache = [];

    /**
     * Upload a local seed image to Cloudinary
     * Returns the Cloudinary path, or null if upload fails
     */
    private function uploadToCloudinary($localPath)
    {
        // Check cache first
        if (isset($this->cloudinaryCache[$localPath])) {
            return $this->cloudinaryCache[$localPath];
        }

        try {
            $fullPath = storage_path('app/public/' . $localPath);

            if (!file_exists($fullPath)) {
                $this->command->warn("  Image not found: {$localPath}");
                return null;
            }

            // Extract folder and filename
            $folder = dirname($localPath); // 'reports' or 'animal_images'
            $fileName = pathinfo($localPath, PATHINFO_FILENAME); // Without extension

            // Upload to Cloudinary using the Upload API
            $result = cloudinary()->uploadApi()->upload($fullPath, [
                'folder' => $folder,
                'public_id' => $fileName,
            ]);

            // Store the public_id (Cloudinary path) instead of full URL
            // Format: folder/filename
            $cloudinaryPath = $folder . '/' . $fileName;

            // Cache the result
            $this->cloudinaryCache[$localPath] = $cloudinaryPath;

            return $cloudinaryPath;

        } catch (\Exception $e) {
            $this->command->error("  Failed to upload {$localPath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Run the database seeds.
     * Animals, Medical Records, and Vaccinations are in Shafiqah's database
     * Cross-database references to:
     * - Rescues (Eilya's database)
     * - Slots (Atiqah's database)
     * - Images (Eilya's database)
     */
    public function run()
    {
        $this->command->info('Starting Animal Seeder...');
        $this->command->info('========================================');

        $species = ['Cat', 'Dog'];
        $genders = ['Male', 'Female'];

        // Consonants and vowels for name generation
        $consonants = ['B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'V', 'W', 'Z'];
        $vowels = ['a', 'e', 'i', 'o', 'u'];

        // Define available images by species
        $catImages = [];
        $dogImages = [];

        for ($i = 1; $i <= 8; $i++) {
            $catImages[] = "animal_images/cat{$i}.jpg";
        }

        for ($i = 1; $i <= 6; $i++) {
            $dogImages[] = "animal_images/dog{$i}.jpg";
        }

        // Get all successful rescues from Eilya's database (cross-database)
        $this->command->info('Fetching successful rescues from Eilya\'s database...');
        $successfulRescues = DB::connection('eilya')
            ->table('rescue')
            ->where('status', 'Success')
            ->get();

        if ($successfulRescues->isEmpty()) {
            $this->command->error('No successful rescues found. Please run RescueSeeder first.');
            return;
        }

        $this->command->info("Found " . $successfulRescues->count() . " successful rescues");

        // Get all available slots from Atiqah's database (cross-database)
        $this->command->info('Fetching available slots from Atiqah\'s database...');
        $availableSlots = DB::connection('atiqah')
            ->table('slot')
            ->where('status', 'available')
            ->get();

        if ($availableSlots->isEmpty()) {
            $this->command->error('No available slots found. Please run SectionSlotSeeder first.');
            return;
        }

        $this->command->info("Found " . $availableSlots->count() . " available slots");

        // Get all vets from Shafiqah's database for vaccination records
        $this->command->info('Fetching vets from Shafiqah\'s database...');
        $vets = DB::connection('shafiqah')->table('vet')->get();
        if ($vets->isEmpty()) {
            $this->command->warn('No vets found. Vaccination records will not be created. Please run ClinicVetSeeder first.');
        } else {
            $this->command->info("Found " . $vets->count() . " vets");
        }

        $animals = [];
        $totalAnimals = 100;
        $notAdoptedCount = 50; // Exactly 50% not adopted
        $adoptedCount = 50; // Exactly 50% adopted

        // Shuffle slots for random assignment
        $shuffledSlots = $availableSlots->shuffle();
        $slotIndex = 0;

        // Create rescue groups - distribute all animals across all rescues
        // Each rescue brings 1-5 animals
        $rescueGroups = [];
        $animalsDistributed = 0;
        $rescueIndex = 0;

        while ($animalsDistributed < $totalAnimals) {
            // Cycle through rescues if we run out
            if ($rescueIndex >= $successfulRescues->count()) {
                $rescueIndex = 0;
            }

            $rescue = $successfulRescues[$rescueIndex];
            $animalsInThisRescue = rand(1, 5); // Each rescue brings 1-5 animals

            // Don't exceed our target
            if ($animalsDistributed + $animalsInThisRescue > $totalAnimals) {
                $animalsInThisRescue = $totalAnimals - $animalsDistributed;
            }

            $rescueGroups[] = [
                'rescue' => $rescue,
                'count' => $animalsInThisRescue,
                'timestamp' => Carbon::parse($rescue->created_at),
            ];

            $animalsDistributed += $animalsInThisRescue;
            $rescueIndex++;
        }

        $this->command->info("Distributing {$totalAnimals} animals across " . count($rescueGroups) . " rescue operations");
        $this->command->info("Target: {$notAdoptedCount} Not Adopted, {$adoptedCount} Adopted");

        // ===== CREATE ALL ANIMALS WITH THEIR RESCUE INFO =====
        $animalIndex = 0;

        foreach ($rescueGroups as $group) {
            $rescue = $group['rescue'];
            $rescueTimestamp = $group['timestamp'];
            $totalInGroup = $group['count'];

            // Create all animals from this rescue
            for ($i = 0; $i < $totalInGroup; $i++) {
                $chosenSpecies = $species[array_rand($species)];

                // Generate random name (3 or 5 letters)
                $nameLength = rand(0, 1) === 0 ? 3 : 5;
                $name = '';
                for ($j = 0; $j < $nameLength; $j++) {
                    if ($j % 2 === 0) {
                        // Even positions: consonant
                        $name .= $consonants[array_rand($consonants)];
                    } else {
                        // Odd positions: vowel
                        $name .= $vowels[array_rand($vowels)];
                    }
                }
                // Capitalize first letter only
                $name = ucfirst(strtolower($name));

                $ageCategories = $chosenSpecies === 'Cat'
                    ? ['kitten', 'adult', 'senior']
                    : ['puppy', 'adult', 'senior'];

                $age = $ageCategories[array_rand($ageCategories)];
                $weight = $chosenSpecies === 'Cat' ? rand(2, 8) : rand(5, 35);

                // Determine adoption status: First 50 are 'Not Adopted', rest are 'Adopted'
                $adoptionStatus = $animalIndex < $notAdoptedCount ? 'Not Adopted' : 'Adopted';

                // Assign slot ONLY for NOT ADOPTED animals
                $slotID = null;
                if ($adoptionStatus === 'Not Adopted') {
                    if ($slotIndex < $shuffledSlots->count()) {
                        $slotID = $shuffledSlots[$slotIndex]->id;
                        $slotIndex++;
                    } else {
                        $this->command->warn("Ran out of available slots. Some not adopted animals will not have slots.");
                    }
                }

                // Determine timestamps
                $createdAt = $rescueTimestamp;
                $updatedAt = $rescueTimestamp;

                // If adopted, set updated_at to adoption date (7-90 days after rescue)
                if ($adoptionStatus === 'Adopted') {
                    $updatedAt = Carbon::parse($rescueTimestamp)->addDays(rand(7, 90));
                }

                // All animals from same rescue have EXACT SAME created_at timestamp
                $animals[] = [
                    'name'            => $name,
                    'species'         => $chosenSpecies,
                    'age'             => $age,
                    'health_details'  => fake()->randomElement([
                        'Healthy',
                        'Sick',
                        'Need Observation'
                    ]),
                    'weight'          => $weight,
                    'gender'          => $genders[array_rand($genders)],
                    'adoption_status' => $adoptionStatus,
                    'rescueID'        => $rescue->id,
                    'slotID'          => $slotID,
                    'created_at'      => $createdAt,
                    'updated_at'      => $updatedAt,
                ];

                $animalIndex++;
            }
        }

        // Use transaction for Shafiqah's database (animals, medical, vaccinations)
        DB::connection('shafiqah')->beginTransaction();

        try {
            $this->command->info('');
            $this->command->info('Inserting animals into Shafiqah\'s database...');

            // Insert all animals into Shafiqah's database
            $createdAnimals = [];
            foreach ($animals as $animalData) {
                $animalId = DB::connection('shafiqah')->table('animal')->insertGetId($animalData);

                // Create stdClass object to mimic Animal model for helper methods
                $animal = (object) $animalData;
                $animal->id = $animalId;
                $createdAnimals[] = $animal;
            }

            // Verify what was actually inserted into database
            $dbNotAdopted = DB::connection('shafiqah')
                ->table('animal')
                ->where('adoption_status', 'Not Adopted')
                ->count();
            $dbAdopted = DB::connection('shafiqah')
                ->table('animal')
                ->where('adoption_status', 'Adopted')
                ->count();
            $this->command->info("VERIFICATION - Database counts: Not Adopted = {$dbNotAdopted}, Adopted = {$dbAdopted}");

            // ===== CREATE VACCINATION AND MEDICAL RECORDS (Shafiqah database) =====
            if ($vets->isNotEmpty()) {
                $this->createVaccinationRecords($createdAnimals, $vets);
                $this->createMedicalRecords($createdAnimals, $vets);
            }

            // Commit Shafiqah transaction BEFORE touching other databases
            DB::connection('shafiqah')->commit();
            $this->command->info("✓ Shafiqah transaction committed successfully");

        } catch (\Exception $e) {
            DB::connection('shafiqah')->rollBack();
            $this->command->error("Failed to create animals in Shafiqah: " . $e->getMessage());
            throw $e;
        }

        // ===== UPDATE SLOTS IN ATIQAH DATABASE (separate operation after Shafiqah commit) =====
        try {
            $assignedSlotIds = array_filter(array_column($animals, 'slotID'));
            if (!empty($assignedSlotIds)) {
                DB::connection('atiqah')
                    ->table('slot')
                    ->whereIn('id', $assignedSlotIds)
                    ->update(['status' => 'occupied', 'updated_at' => now()]);
                $this->command->info("✓ Updated " . count($assignedSlotIds) . " slots to 'occupied' status in Atiqah's database");
            }
        } catch (\Exception $e) {
            $this->command->error("Failed to update slots in Atiqah: " . $e->getMessage());
            // Don't throw - animals already created successfully
        }

        // ===== ASSIGN IMAGES TO ANIMALS IN EILYA DATABASE (separate operation) =====
        try {
            $this->command->info('');
            $this->command->info('Uploading animal seed images to Cloudinary...');
            $this->command->info('(This may take a moment for first-time uploads)');
            $this->assignImagesToAnimals($createdAnimals, $catImages, $dogImages);
            $this->command->info("✓ Images assigned successfully in Eilya's database");
        } catch (\Exception $e) {
            $this->command->error("Failed to assign images in Eilya: " . $e->getMessage());
            // Don't throw - animals already created successfully
        }

        // Statistics
        $actualNotAdoptedCount = count(array_filter($animals, fn($a) => $a['adoption_status'] === 'Not Adopted'));
        $actualAdoptedCount = count(array_filter($animals, fn($a) => $a['adoption_status'] === 'Adopted'));
        $ageCount = array_count_values(array_column($animals, 'age'));

        // Count animals per rescue for verification
        $animalsPerRescue = [];
        foreach ($animals as $animal) {
            $rescueId = $animal['rescueID'];
            if (!isset($animalsPerRescue[$rescueId])) {
                $animalsPerRescue[$rescueId] = 0;
            }
            $animalsPerRescue[$rescueId]++;
        }

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Animal Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total animals created: " . count($animals));
            $this->command->info("All animals from rescues: " . count($animals));
            $this->command->info("Rescue operations used: " . count($rescueGroups));
            $this->command->info("Animals per rescue: " . implode(', ', $animalsPerRescue));
            $this->command->info('');
            $this->command->info('Adoption Status:');
            $this->command->info("  - Not Adopted (currently at shelter): {$actualNotAdoptedCount}");
            $this->command->info("  - Adopted (no longer at shelter): {$actualAdoptedCount}");
            $this->command->info("  - Slots occupied: " . count($assignedSlotIds));
            $this->command->info('');
            $this->command->info('Age Distribution:');
            foreach ($ageCount as $category => $count) {
                $this->command->info("  - " . ucfirst($category) . ": {$count}");
            }
            $this->command->info('');
            $this->command->info('Database: Shafiqah (MySQL) - Animals, Medical, Vaccinations');
            $this->command->info('Cross-references: Eilya (Rescues, Images), Atiqah (Slots)');
            $this->command->info('=================================');
    }

    /**
     * Assign images to animals based on their species
     * Images are stored in Eilya's database (cross-database to animal in Shafiqah)
     */
    private function assignImagesToAnimals($animals, $catImages, $dogImages)
    {
        $images = [];
        $totalImages = 0;

        foreach ($animals as $animal) {
            // Randomly assign 1-3 images per animal
            $numImages = rand(1, 3);

            // Select appropriate images based on species
            $availableImages = $animal->species === 'Cat' ? $catImages : $dogImages;

            // Randomly select images for this animal
            $selectedImages = array_rand(array_flip($availableImages), min($numImages, count($availableImages)));

            // Handle case where only 1 image is selected (array_rand returns string, not array)
            if (!is_array($selectedImages)) {
                $selectedImages = [$selectedImages];
            }

            foreach ($selectedImages as $imagePath) {
                // Upload image to Cloudinary
                $cloudinaryPath = $this->uploadToCloudinary($imagePath);

                // Skip if upload failed
                if ($cloudinaryPath === null) {
                    $this->command->warn("  Skipping image for animal {$animal->id}: {$imagePath}");
                    continue;
                }

                $images[] = [
                    'image_path' => $cloudinaryPath, // Store Cloudinary path
                    'animalID'   => $animal->id, // Cross-database reference to Shafiqah
                    'reportID'   => null,
                    'clinicID'   => null,
                    'created_at' => $animal->created_at,
                    'updated_at' => $animal->created_at,
                ];
                $totalImages++;
            }
        }

        // Insert all images into Eilya's database
        $this->command->info('Inserting animal images into Eilya\'s database...');
        foreach (array_chunk($images, 300) as $chunk) {
            DB::connection('eilya')->table('image')->insert($chunk);
        }

        $this->command->info("Total images assigned to animals: {$totalImages}");
        $avgImages = round($totalImages / count($animals), 1);
        $this->command->info("Average images per animal: {$avgImages}");
    }

    /**
     * Create vaccination and medical records for animals
     */
    private function createMedicalRecords($animals, $vets)
    {
        $treatmentTypes = [
            'Cat' => [
                'Routine Check-up',
                'Dental Cleaning',
                'Spay/Neuter Surgery',
                'Upper Respiratory Infection',
                'Flea/Tick Treatment',
                'Urinary Tract Infection',
                'Wound Care',
                'Ear Infection',
                'Gastrointestinal Issues',
                'Skin Allergies',
            ],
            'Dog' => [
                'Routine Check-up',
                'Dental Cleaning',
                'Spay/Neuter Surgery',
                'Kennel Cough',
                'Flea/Tick Treatment',
                'Hip Dysplasia',
                'Ear Infection',
                'Hot Spots',
                'Arthritis Treatment',
                'Wound Care',
                'Gastric Issues',
            ],
        ];

        $diagnoses = [
            'Routine Check-up' => [
                'Healthy - No issues found',
                'Overall good health',
                'Minor concerns noted',
            ],
            'Dental Cleaning' => [
                'Tartar buildup',
                'Mild gingivitis',
                'Plaque removal needed',
                'Tooth extraction required',
            ],
            'Spay/Neuter Surgery' => [
                'Pre-operative assessment completed',
                'Surgery performed successfully',
                'Post-operative recovery',
            ],
            'Upper Respiratory Infection' => [
                'Viral upper respiratory infection',
                'Bacterial infection diagnosed',
                'Sneezing and nasal discharge',
            ],
            'Kennel Cough' => [
                'Bordetella bronchiseptica infection',
                'Mild to moderate kennel cough',
            ],
            'Flea/Tick Treatment' => [
                'Flea infestation detected',
                'Tick removal required',
                'Preventative treatment applied',
            ],
            'default' => [
                'Diagnosed after examination',
                'Symptoms observed and treated',
                'Follow-up recommended',
            ],
        ];

        $actions = [
            'Routine Check-up' => [
                'Physical examination performed',
                'Vital signs checked - all normal',
                'Weight recorded and monitored',
            ],
            'Dental Cleaning' => [
                'Professional dental cleaning performed',
                'Scaling and polishing completed',
                'Tooth extraction performed',
                'Dental X-rays taken',
            ],
            'Spay/Neuter Surgery' => [
                'Surgery performed under general anesthesia',
                'Post-operative pain management administered',
                'Sutures applied, follow-up in 10-14 days',
            ],
            'Flea/Tick Treatment' => [
                'Topical flea/tick medication applied',
                'Oral medication prescribed',
                'Environmental treatment recommended',
            ],
            'default' => [
                'Medication prescribed',
                'Treatment administered',
                'Observation and monitoring',
                'Symptomatic treatment provided',
                'Antibiotics prescribed',
                'Pain management initiated',
            ],
        ];

        $totalMedicalRecords = 0;

        foreach ($animals as $animal) {
            // Random number of medical records per animal (1-4)
            $numRecords = rand(1, 4);

            $availableTreatments = $treatmentTypes[$animal->species];

            for ($i = 0; $i < $numRecords; $i++) {
                $vet = $vets->random();
                $treatmentType = $availableTreatments[array_rand($availableTreatments)];

                // Get specific diagnosis for treatment type or use default
                $diagnosisOptions = $diagnoses[$treatmentType] ?? $diagnoses['default'];
                $diagnosis = $diagnosisOptions[array_rand($diagnosisOptions)];

                // Get specific action for treatment type or use default
                $actionOptions = $actions[$treatmentType] ?? $actions['default'];
                $action = $actionOptions[array_rand($actionOptions)];

                // Medical record date is after animal creation date
                $recordDate = Carbon::parse($animal->created_at)
                    ->addDays(rand(7, 180));

                // Cost varies by treatment type
                $costRanges = [
                    'Routine Check-up' => [30, 80],
                    'Dental Cleaning' => [150, 400],
                    'Spay/Neuter Surgery' => [200, 500],
                    'Upper Respiratory Infection' => [80, 200],
                    'Kennel Cough' => [80, 180],
                    'Flea/Tick Treatment' => [40, 120],
                    'Hip Dysplasia' => [200, 600],
                    'Arthritis Treatment' => [100, 300],
                    'default' => [50, 250],
                ];

                $costRange = $costRanges[$treatmentType] ?? $costRanges['default'];
                $cost = rand($costRange[0], $costRange[1]);

                $remarksOptions = [
                    'Animal responded well to treatment',
                    'Follow-up appointment scheduled',
                    'Owner advised on home care',
                    'Medication prescribed for 7-14 days',
                    'Condition improving with treatment',
                    'Monitor for any changes',
                    'Preventative measures discussed',
                    'No complications observed',
                    'Animal in stable condition',
                    'Treatment completed successfully',
                ];

                // Insert medical record into Shafiqah's database
                DB::connection('shafiqah')->table('medical')->insert([
                    'treatment_type' => $treatmentType,
                    'diagnosis' => $diagnosis,
                    'action' => $action,
                    'remarks' => $remarksOptions[array_rand($remarksOptions)],
                    'costs' => $cost,
                    'vetID' => $vet->id,
                    'animalID' => $animal->id,
                    'created_at' => $recordDate,
                    'updated_at' => $recordDate,
                ]);

                $totalMedicalRecords++;
            }
        }

        $this->command->info("Total medical records created: {$totalMedicalRecords}");
        $avgRecords = round($totalMedicalRecords / count($animals), 1);
        $this->command->info("Average medical records per animal: {$avgRecords}");
    }

    private function createVaccinationRecords($animals, $vets)
    {
        $vaccineTypes = [
            'Cat' => [
                ['name' => 'FVRCP', 'type' => 'Core', 'interval' => 365],
                ['name' => 'Rabies', 'type' => 'Core', 'interval' => 365],
                ['name' => 'FeLV', 'type' => 'Non-core', 'interval' => 365],
                ['name' => 'Bordetella', 'type' => 'Non-core', 'interval' => 180],
            ],
            'Dog' => [
                ['name' => 'DHPP', 'type' => 'Core', 'interval' => 365],
                ['name' => 'Rabies', 'type' => 'Core', 'interval' => 365],
                ['name' => 'Bordetella', 'type' => 'Non-core', 'interval' => 180],
                ['name' => 'Leptospirosis', 'type' => 'Non-core', 'interval' => 365],
                ['name' => 'Canine Influenza', 'type' => 'Non-core', 'interval' => 365],
            ],
        ];

        $totalVaccinations = 0;

        foreach ($animals as $animal) {
            // Random number of vaccination records per animal (1-5)
            $numVaccinations = rand(1, 5);

            $availableVaccines = $vaccineTypes[$animal->species];
            $selectedVaccines = [];

            // Ensure core vaccines are included first
            $coreVaccines = array_filter($availableVaccines, fn($v) => $v['type'] === 'Core');
            $nonCoreVaccines = array_filter($availableVaccines, fn($v) => $v['type'] === 'Non-core');

            // Add core vaccines
            foreach ($coreVaccines as $vaccine) {
                if (count($selectedVaccines) < $numVaccinations) {
                    $selectedVaccines[] = $vaccine;
                }
            }

            // Add random non-core vaccines if needed
            shuffle($nonCoreVaccines);
            foreach ($nonCoreVaccines as $vaccine) {
                if (count($selectedVaccines) < $numVaccinations) {
                    $selectedVaccines[] = $vaccine;
                }
            }

            // Create vaccination records
            foreach ($selectedVaccines as $index => $vaccine) {
                $vet = $vets->random();

                // Vaccination date is after animal creation date
                $vaccinationDate = Carbon::parse($animal->created_at)
                    ->addDays(rand(1, 30))
                    ->subDays($index * rand(30, 90)); // Space out vaccinations

                // Next due date based on vaccine interval
                $nextDueDate = Carbon::parse($vaccinationDate)->addDays($vaccine['interval']);

                // Cost varies by vaccine type
                $cost = $vaccine['type'] === 'Core'
                    ? rand(50, 150)
                    : rand(80, 200);

                $remarksOptions = [
                    'Vaccination completed successfully',
                    'No adverse reactions observed',
                    'Animal tolerated vaccine well',
                    'Follow-up scheduled',
                    'Booster required in ' . ($vaccine['interval'] / 30) . ' months',
                    'Part of standard vaccination protocol',
                ];

                // Insert vaccination record into Shafiqah's database
                DB::connection('shafiqah')->table('vaccination')->insert([
                    'name' => $vaccine['name'],
                    'type' => $vaccine['type'],
                    'next_due_date' => $nextDueDate,
                    'remarks' => $remarksOptions[array_rand($remarksOptions)],
                    'weight' => $animal->weight,
                    'costs' => $cost,
                    'animalID' => $animal->id,
                    'vetID' => $vet->id,
                    'created_at' => $vaccinationDate,
                    'updated_at' => $vaccinationDate,
                ]);

                $totalVaccinations++;
            }
        }

        $this->command->info("Total vaccination records created: {$totalVaccinations}");
        $avgVaccinations = round($totalVaccinations / count($animals), 1);
        $this->command->info("Average vaccinations per animal: {$avgVaccinations}");
    }
}
