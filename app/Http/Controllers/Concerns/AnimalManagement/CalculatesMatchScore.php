<?php

namespace App\Http\Controllers\Concerns\AnimalManagement;

trait CalculatesMatchScore
{
    public function calculateMatchScore($adopterProfile, $animalProfile, $animal)
    {
        $score = 0;
        $maxScore = 100;

        if ($adopterProfile->preferred_species === $animal->species || $adopterProfile->preferred_species === 'both') {
            $score += 20;
        }
        if ($adopterProfile->preferred_size === $animalProfile->size || $adopterProfile->preferred_size === 'any') {
            $score += 15;
        }
        $score += $this->matchEnergyLevel($adopterProfile->activity_level, $animalProfile->energy_level) * 20;
        $score += ($adopterProfile->has_children ? ($animalProfile->good_with_kids ? 15 : 0) : 15);
        $score += ($adopterProfile->has_other_pets ? ($animalProfile->good_with_pets ? 15 : 0) : 15);
        $score += $this->matchHousingType($adopterProfile->housing_type, $animalProfile);

        return round(($score / $maxScore) * 100);
    }

    public function matchEnergyLevel($activityLevel, $energyLevel)
    {
        $levels = ['low' => 1, 'medium' => 2, 'high' => 3];
        $difference = abs(($levels[$activityLevel] ?? 2) - ($levels[$energyLevel] ?? 2));
        if ($difference === 0) return 1.0;
        if ($difference === 1) return 0.6;
        return 0.2;
    }

    public function matchHousingType($housingType, $animalProfile)
    {
        if ($housingType === 'apartment') {
            return (in_array($animalProfile->size, ['small', 'medium']) && $animalProfile->energy_level !== 'high') ? 15 : 5;
        }
        if ($housingType === 'house_with_yard') return 15;
        return 10;
    }

    public function getMatchDetails($adopterProfile, $animalProfile, $animal)
    {
        $details = [];
        if ($adopterProfile->preferred_species === $animal->species || $adopterProfile->preferred_species === 'both') {
            $details[] = "Matches your preferred species";
        }
        if ($adopterProfile->preferred_size === $animalProfile->size || $adopterProfile->preferred_size === 'any') {
            $details[] = "Perfect size match";
        }
        if ($adopterProfile->has_children && $animalProfile->good_with_kids) {
            $details[] = "Great with children";
        }
        if ($adopterProfile->has_other_pets && $animalProfile->good_with_pets) {
            $details[] = "Gets along with other pets";
        }
        if ($this->matchEnergyLevel($adopterProfile->activity_level, $animalProfile->energy_level) >= 0.6) {
            $details[] = "Energy level matches your lifestyle";
        }
        return $details;
    }
}
