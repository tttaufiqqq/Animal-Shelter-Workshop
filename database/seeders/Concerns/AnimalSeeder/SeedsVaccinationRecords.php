<?php

namespace Database\Seeders\Concerns\AnimalSeeder;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait SeedsVaccinationRecords
{
    private function createVaccinationRecords(array $animals, $vets): void
    {
        $vaccineTypes = [
            'Cat' => [
                ['name' => 'FVRCP',      'type' => 'Core',     'interval' => 365],
                ['name' => 'Rabies',     'type' => 'Core',     'interval' => 365],
                ['name' => 'FeLV',       'type' => 'Non-core', 'interval' => 365],
                ['name' => 'Bordetella', 'type' => 'Non-core', 'interval' => 180],
            ],
            'Dog' => [
                ['name' => 'DHPP',              'type' => 'Core',     'interval' => 365],
                ['name' => 'Rabies',            'type' => 'Core',     'interval' => 365],
                ['name' => 'Bordetella',        'type' => 'Non-core', 'interval' => 180],
                ['name' => 'Leptospirosis',     'type' => 'Non-core', 'interval' => 365],
                ['name' => 'Canine Influenza',  'type' => 'Non-core', 'interval' => 365],
            ],
        ];

        $totalVaccinations = 0;

        foreach ($animals as $animal) {
            $numVaccinations  = rand(1, 5);
            $availableVaccines = $vaccineTypes[$animal->species];

            $coreVaccines    = array_values(array_filter($availableVaccines, fn($v) => $v['type'] === 'Core'));
            $nonCoreVaccines = array_values(array_filter($availableVaccines, fn($v) => $v['type'] === 'Non-core'));

            $selectedVaccines = [];
            foreach ($coreVaccines as $vaccine) {
                if (count($selectedVaccines) < $numVaccinations) {
                    $selectedVaccines[] = $vaccine;
                }
            }
            shuffle($nonCoreVaccines);
            foreach ($nonCoreVaccines as $vaccine) {
                if (count($selectedVaccines) < $numVaccinations) {
                    $selectedVaccines[] = $vaccine;
                }
            }

            foreach ($selectedVaccines as $index => $vaccine) {
                $vet            = $vets->random();
                $vaccinationDate = Carbon::parse($animal->created_at)
                    ->addDays(rand(1, 30))
                    ->subDays($index * rand(30, 90));

                $nextDueDate = Carbon::parse($vaccinationDate)->addDays($vaccine['interval']);
                if ($nextDueDate->isPast()) {
                    $nextDueDate = Carbon::now()->addDays(rand(30, 365));
                }

                $cost = $vaccine['type'] === 'Core' ? rand(50, 150) : rand(80, 200);

                $remarksOptions = [
                    'Vaccination completed successfully',
                    'No adverse reactions observed',
                    'Animal tolerated vaccine well',
                    'Follow-up scheduled',
                    'Booster required in ' . ($vaccine['interval'] / 30) . ' months',
                    'Part of standard vaccination protocol',
                ];

                DB::connection('animals')->table('vaccination')->insert([
                    'name'          => $vaccine['name'],
                    'type'          => $vaccine['type'],
                    'next_due_date' => $nextDueDate,
                    'remarks'       => $remarksOptions[array_rand($remarksOptions)],
                    'weight'        => $animal->weight,
                    'costs'         => $cost,
                    'animalID'      => $animal->id,
                    'vetID'         => $vet->id,
                    'created_at'    => $vaccinationDate,
                    'updated_at'    => $vaccinationDate,
                ]);

                $totalVaccinations++;
            }
        }

        $avg = count($animals) > 0 ? round($totalVaccinations / count($animals), 1) : 0;
        $this->command->info("Total vaccination records created: {$totalVaccinations}");
        $this->command->info("Average vaccinations per animal: {$avg}");
    }
}
