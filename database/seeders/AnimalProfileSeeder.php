<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnimalProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Cross-database references to:
     * - Animals (Shafiqah's database)
     */
    public function run()
    {
        $this->command->info('Starting Animal Profile Seeder...');
        $this->command->info('========================================');

        // Get adopter profiles from Taufiq's database
        $this->command->info('Fetching adopter profiles from Taufiq\'s database...');
        $adopters = DB::connection('users')->table('adopter_profile')->get();

        // Get animals with 'Not Adopted' OR 'Adopted' status from Shafiqah's database
        $this->command->info('Fetching animals from Shafiqah\'s database...');
        $animals = DB::connection('animals')
            ->table('animal')
            ->whereIn('adoption_status', ['Not Adopted', 'Adopted'])
            ->get();

        if ($animals->isEmpty()) {
            $this->command->error('No animals found. Please run AnimalSeeder first.');
            return;
        }

        $this->command->info("Found " . $animals->count() . " animals");

        // Use transaction for Shafiqah's database
        DB::connection('animals')->beginTransaction();

        try {
            $this->command->info('');
            $this->command->info('Creating animal profiles in Shafiqah\'s database...');

            $profileCount = 0;

            foreach ($animals as $animal) {
                // Check if profile already exists in Shafiqah's database
                $existingProfile = DB::connection('animals')
                    ->table('animal_profile')
                    ->where('animalID', $animal->id)
                    ->exists();

                if ($existingProfile) continue;

                // Try to find matching adopter based on species
                $matchingAdopter = $adopters->firstWhere('preferred_species', strtolower($animal->species));

                if ($matchingAdopter) {
                    // Better matched profile
                    $profileData = $this->generateMatchedProfile($animal, $matchingAdopter);
                } else {
                    // Fallback: random realistic profile
                    $profileData = $this->generateRandomProfile($animal);
                }

                // Add timestamps
                $profileData['created_at'] = now();
                $profileData['updated_at'] = now();

                // Insert into Shafiqah's database
                DB::connection('animals')->table('animal_profile')->insert($profileData);
                $profileCount++;
            }

            DB::connection('animals')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Animal Profile Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Animal profiles created: {$profileCount}");
            $this->command->info('Database: Shafiqah (MySQL)');
            $this->command->info('Cross-references: Shafiqah (Animals), Taufiq (Adopter Profiles)');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('animals')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding animal profiles: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }

    /**
     * Generate profile that aligns with adopter preferences
     */
    private function generateMatchedProfile($animal, $adopter)
    {
        $size = $adopter->preferred_size ?? $this->getSizeForSpecies($animal->species);
        $energy = $this->matchEnergyToAdopter($adopter->activity_level);

        return [
            'animalID'       => $animal->id,
            'age'            => $this->getAgeCategory($animal->species),

            'size'           => $size,
            'energy_level'   => $energy,
            'good_with_kids' => $adopter->has_children ? 1 : rand(0, 1),
            'good_with_pets' => $adopter->has_other_pets ? 1 : rand(0, 1),
            'temperament'    => $this->temperamentBasedOnEnergy($energy),
            'medical_needs'  => $this->randomMedicalNeeds(),
        ];
    }


    /**
     * Fallback: random but realistic profile
     */
    private function generateRandomProfile($animal)
    {
        return [
            'animalID'       => $animal->id,
            'age'            => $this->getAgeCategory($animal->species),

            'size'           => $this->getSizeForSpecies($animal->species),
            'energy_level'   => $this->randomEnergy(),
            'good_with_kids' => rand(0, 1),
            'good_with_pets' => rand(0, 1),
            'temperament'    => $this->randomTemperament(),
            'medical_needs'  => $this->randomMedicalNeeds(),
        ];
    }


    private function matchEnergyToAdopter($activity)
    {
        return match ($activity) {
            'low'    => 'low',
            'medium' => rand(0, 1) ? 'medium' : 'low',
            'high'   => rand(0, 1) ? 'medium' : 'high',
            default  => 'medium'
        };
    }

    private function getSizeForSpecies($species)
    {
        $species = strtolower($species);

        if ($species === 'cat') {
            return collect(['small', 'medium'])->random();
        }

        if ($species === 'dog') {
            return collect(['small', 'medium', 'large'])->random();
        }

        return 'medium';
    }

    private function temperamentBasedOnEnergy($energy)
    {
        return match ($energy) {
            'low'    => collect(['calm', 'shy', 'independent'])->random(),
            'medium' => collect(['friendly', 'calm'])->random(),
            'high'   => collect(['active', 'friendly'])->random(),
            default  => 'friendly'
        };
    }

    private function randomEnergy()
    {
        return collect(['low', 'medium', 'high'])->random();
    }

    private function randomTemperament()
    {
        return collect([
            'calm', 'active', 'shy', 'friendly', 'independent'
        ])->random();
    }

    private function randomMedicalNeeds()
    {
        return collect(['none', 'minor', 'moderate', 'special'])->random();
    }

    private function getAgeCategory($species)
    {
        $species = strtolower($species);

        if ($species === 'cat' || $species === 'dog') {
            return collect(['kitten/puppy', 'young', 'adult', 'senior'])->random();
        }

        return collect(['young', 'adult'])->random();
    }
}
