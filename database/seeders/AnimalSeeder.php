<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AnimalSeeder extends Seeder
{
    public function run()
    {
        $species = ['Cat', 'Dog'];
        $genders = ['Male', 'Female'];

        $catNames = ['Milo', 'Coco', 'Luna', 'Oyen', 'Mimi', 'Snowy', 'Kitty', 'Nala', 'Bella'];
        $dogNames = ['Buddy', 'Rocky', 'Max', 'Shadow', 'Charlie', 'Bella', 'Duke', 'Lucky', 'Hunter'];

        $animals = [];

        for ($i = 0; $i < 200; $i++) {

            $chosenSpecies = $species[array_rand($species)];

            // Pick name based on species
            $name = $chosenSpecies == 'Cat'
                ? $catNames[array_rand($catNames)]
                : $dogNames[array_rand($dogNames)];

            // Timestamp between Jan 2025 and now
            $createdAt = Carbon::create(2025, 1, 1)
                ->addDays(rand(0, now()->diffInDays('2025-01-01')));

            $animals[] = [
                'name'            => $name . ' ' . Str::upper(Str::random(3)), // Make names unique
                'species'         => $chosenSpecies,
                'health_details'  => 'Healthy',
                'age'             => rand(1, 10) . ' years',
                'weight'          => rand(2, 35),
                'gender'          => $genders[array_rand($genders)],
                'adoption_status' => 'Available',
                'rescueID'        => null,
                'slotID'          => null,
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ];
        }

        DB::table('animal')->insert($animals);
    }
}
