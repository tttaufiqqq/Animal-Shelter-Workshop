<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Animal;
use App\Models\Rescue;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AnimalSeeder extends Seeder
{
    public function run()
    {
        $species = ['Cat', 'Dog'];
        $genders = ['Male', 'Female'];
        $adoptionStatuses = ['Not Adopted', 'Adopted'];

        $catNames = ['Milo', 'Coco', 'Luna', 'Oyen', 'Mimi', 'Snowy', 'Kitty', 'Nala', 'Bella'];
        $dogNames = ['Buddy', 'Rocky', 'Max', 'Shadow', 'Charlie', 'Bella', 'Duke', 'Lucky', 'Hunter'];

        // Get all successful rescues
        $successfulRescues = Rescue::where('status', 'Success')->get();

        $animals = [];
        $totalAnimals = 200;
        $animalsFromRescue = (int)($totalAnimals * 0.8); // 80% from rescue

        for ($i = 0; $i < $totalAnimals; $i++) {
            $chosenSpecies = $species[array_rand($species)];
            $name = $chosenSpecies === 'Cat'
                ? $catNames[array_rand($catNames)]
                : $dogNames[array_rand($dogNames)];

            $rescueID = null;
            $createdAt = Carbon::now();

            if ($i < $animalsFromRescue && $successfulRescues->isNotEmpty()) {
                $rescue = $successfulRescues->random();
                $rescueID = $rescue->id;
                // Ensure animal created after rescue created
                $createdAt = Carbon::parse($rescue->created_at)->addHours(rand(1, 24));
            } else {
                // Direct intake, random date in 2024 or 2025
                $year = rand(2024, 2025);
                $month = rand(1, 12);
                $day = rand(1, 28); // safe for all months
                $createdAt = Carbon::create($year, $month, $day, rand(0, 23), rand(0, 59));
            }

            $animals[] = [
                'name'            => $name . ' ' . Str::upper(Str::random(3)),
                'species'         => $chosenSpecies,
                'health_details'  => fake()->randomElement([
                    'Healthy',
                    'Needs vaccination',
                    'Minor injuries',
                    'Recovering well',
                    'Excellent condition'
                ]),
                'age'             => rand(1, 10) . ' years',
                'weight'          => rand(2, 35),
                'gender'          => $genders[array_rand($genders)],
                'adoption_status' => $adoptionStatuses[array_rand($adoptionStatuses)],
                'rescueID'        => $rescueID,
                'slotID'          => null,
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ];
        }

        // Batch insert using Eloquent
        Animal::insert($animals);

        $fromRescueCount = count(array_filter($animals, fn($a) => $a['rescueID'] !== null));
        $this->command->info("Created {$totalAnimals} animals: {$fromRescueCount} from rescues, " . ($totalAnimals - $fromRescueCount) . " direct intakes.");
    }
}
