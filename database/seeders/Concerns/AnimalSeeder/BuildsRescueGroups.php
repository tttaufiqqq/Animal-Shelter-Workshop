<?php

namespace Database\Seeders\Concerns\AnimalSeeder;

use Carbon\Carbon;

trait BuildsRescueGroups
{
    private function buildAnimalRecords($successfulRescues, $availableSlots): array
    {
        $species    = ['Cat', 'Dog'];
        $genders    = ['Male', 'Female'];
        $consonants = ['B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'V', 'W', 'Z'];
        $vowels     = ['a', 'e', 'i', 'o', 'u'];

        $totalAnimals  = 100;
        $shuffledSlots = $availableSlots->shuffle();
        $slotIndex     = 0;

        $ageDistribution = [
            'young'  => round($totalAnimals * 0.30),
            'adult'  => round($totalAnimals * 0.50),
            'senior' => round($totalAnimals * 0.20),
        ];
        $agePool = array_merge(
            array_fill(0, $ageDistribution['young'], 'young'),
            array_fill(0, $ageDistribution['adult'], 'adult'),
            array_fill(0, $ageDistribution['senior'], 'senior')
        );
        shuffle($agePool);

        // Build rescue groups
        $rescueGroups       = [];
        $animalsDistributed = 0;
        $rescueIndex        = 0;

        while ($animalsDistributed < $totalAnimals) {
            if ($rescueIndex >= $successfulRescues->count()) {
                $rescueIndex = 0;
            }
            $rescue               = $successfulRescues[$rescueIndex];
            $animalsInThisRescue  = rand(3, 8);
            if ($animalsDistributed + $animalsInThisRescue > $totalAnimals) {
                $animalsInThisRescue = $totalAnimals - $animalsDistributed;
            }
            $rescueGroups[] = [
                'rescue'    => $rescue,
                'count'     => $animalsInThisRescue,
                'timestamp' => Carbon::parse($rescue->created_at),
            ];
            $animalsDistributed += $animalsInThisRescue;
            $rescueIndex++;
        }

        $this->command->info("Distributing {$totalAnimals} animals across " . count($rescueGroups) . " rescue operations");
        $this->command->info("Age Distribution: {$ageDistribution['young']} young, {$ageDistribution['adult']} adult, {$ageDistribution['senior']} senior");

        // Build animals array from rescue groups
        $animals     = [];
        $animalIndex = 0;

        foreach ($rescueGroups as $group) {
            $rescue          = $group['rescue'];
            $rescueTimestamp = $group['timestamp'];
            $totalInGroup    = $group['count'];

            for ($i = 0; $i < $totalInGroup; $i++) {
                $chosenSpecies = $species[array_rand($species)];

                $nameLength = rand(0, 1) === 0 ? 3 : 5;
                $name       = '';
                for ($j = 0; $j < $nameLength; $j++) {
                    $name .= $j % 2 === 0
                        ? $consonants[array_rand($consonants)]
                        : $vowels[array_rand($vowels)];
                }
                $name = ucfirst(strtolower($name));

                $ageCategory = $agePool[$animalIndex];
                $age = $ageCategory === 'young'
                    ? ($chosenSpecies === 'Cat' ? 'kitten' : 'puppy')
                    : $ageCategory;

                $weight = $chosenSpecies === 'Cat' ? rand(2, 8) : rand(5, 35);

                $slotID = null;
                if ($slotIndex < $shuffledSlots->count()) {
                    $slotID = $shuffledSlots[$slotIndex]->id;
                    $slotIndex++;
                } else {
                    $this->command->warn("Ran out of available slots. Some animals will not have slots.");
                }

                $animals[] = [
                    'name'            => $name,
                    'species'         => $chosenSpecies,
                    'age'             => $age,
                    'health_details'  => fake()->randomElement(['Healthy', 'Sick', 'Need Observation']),
                    'weight'          => $weight,
                    'gender'          => $genders[array_rand($genders)],
                    'adoption_status' => 'Not Adopted',
                    'rescueID'        => $rescue->id,
                    'slotID'          => $slotID,
                    'created_at'      => $rescueTimestamp,
                    'updated_at'      => $rescueTimestamp,
                ];

                $animalIndex++;
            }
        }

        return [$animals, $rescueGroups];
    }
}
