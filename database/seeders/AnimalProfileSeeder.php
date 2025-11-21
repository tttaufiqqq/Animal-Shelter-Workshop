<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Animal;
use App\Models\AnimalProfile;
use App\Models\AdopterProfile;

class AnimalProfileSeeder extends Seeder
{
    public function run()
    {
        $adopters = AdopterProfile::all();

        // Only animals not adopted
        $animals = Animal::where('adoption_status', 'Not Adopted')->get();

        foreach ($animals as $animal) {

            // Skip if profile already exists
            if ($animal->profile) continue;

            // Try to find matching adopter based on species
            $matchingAdopter = $adopters->firstWhere('preferred_species', strtolower($animal->species));

            if ($matchingAdopter) {
                // Better matched profile
                $profileData = $this->generateMatchedProfile($animal, $matchingAdopter);
            } else {
                // Fallback: random realistic profile
                $profileData = $this->generateRandomProfile($animal);
            }

            AnimalProfile::create($profileData);
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
