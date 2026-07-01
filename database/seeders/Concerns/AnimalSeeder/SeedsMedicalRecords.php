<?php

namespace Database\Seeders\Concerns\AnimalSeeder;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait SeedsMedicalRecords
{
    private function createMedicalRecords(array $animals, $vets): void
    {
        $treatmentTypes = [
            'Cat' => [
                'Routine Check-up', 'Dental Cleaning', 'Spay/Neuter Surgery',
                'Upper Respiratory Infection', 'Flea/Tick Treatment',
                'Urinary Tract Infection', 'Wound Care', 'Ear Infection',
                'Gastrointestinal Issues', 'Skin Allergies',
            ],
            'Dog' => [
                'Routine Check-up', 'Dental Cleaning', 'Spay/Neuter Surgery',
                'Kennel Cough', 'Flea/Tick Treatment', 'Hip Dysplasia',
                'Ear Infection', 'Hot Spots', 'Arthritis Treatment',
                'Wound Care', 'Gastric Issues',
            ],
        ];

        $diagnoses = [
            'Routine Check-up'            => ['Healthy - No issues found', 'Overall good health', 'Minor concerns noted'],
            'Dental Cleaning'             => ['Tartar buildup', 'Mild gingivitis', 'Plaque removal needed', 'Tooth extraction required'],
            'Spay/Neuter Surgery'         => ['Pre-operative assessment completed', 'Surgery performed successfully', 'Post-operative recovery'],
            'Upper Respiratory Infection' => ['Viral upper respiratory infection', 'Bacterial infection diagnosed', 'Sneezing and nasal discharge'],
            'Kennel Cough'                => ['Bordetella bronchiseptica infection', 'Mild to moderate kennel cough'],
            'Flea/Tick Treatment'         => ['Flea infestation detected', 'Tick removal required', 'Preventative treatment applied'],
            'default'                     => ['Diagnosed after examination', 'Symptoms observed and treated', 'Follow-up recommended'],
        ];

        $actions = [
            'Routine Check-up'    => ['Physical examination performed', 'Vital signs checked - all normal', 'Weight recorded and monitored'],
            'Dental Cleaning'     => ['Professional dental cleaning performed', 'Scaling and polishing completed', 'Tooth extraction performed', 'Dental X-rays taken'],
            'Spay/Neuter Surgery' => ['Surgery performed under general anesthesia', 'Post-operative pain management administered', 'Sutures applied, follow-up in 10-14 days'],
            'Flea/Tick Treatment' => ['Topical flea/tick medication applied', 'Oral medication prescribed', 'Environmental treatment recommended'],
            'default'             => ['Medication prescribed', 'Treatment administered', 'Observation and monitoring', 'Symptomatic treatment provided', 'Antibiotics prescribed', 'Pain management initiated'],
        ];

        $costRanges = [
            'Routine Check-up'    => [30, 80],
            'Dental Cleaning'     => [150, 400],
            'Spay/Neuter Surgery' => [200, 500],
            'Upper Respiratory Infection' => [80, 200],
            'Kennel Cough'        => [80, 180],
            'Flea/Tick Treatment' => [40, 120],
            'Hip Dysplasia'       => [200, 600],
            'Arthritis Treatment' => [100, 300],
            'default'             => [50, 250],
        ];

        $remarksOptions = [
            'Animal responded well to treatment', 'Follow-up appointment scheduled',
            'Owner advised on home care', 'Medication prescribed for 7-14 days',
            'Condition improving with treatment', 'Monitor for any changes',
            'Preventative measures discussed', 'No complications observed',
            'Animal in stable condition', 'Treatment completed successfully',
        ];

        $totalMedicalRecords = 0;

        foreach ($animals as $animal) {
            $numRecords        = rand(1, 4);
            $availableTreatments = $treatmentTypes[$animal->species];

            for ($i = 0; $i < $numRecords; $i++) {
                $vet           = $vets->random();
                $treatmentType = $availableTreatments[array_rand($availableTreatments)];

                $diagnosis  = ($diagnoses[$treatmentType] ?? $diagnoses['default'])[array_rand($diagnoses[$treatmentType] ?? $diagnoses['default'])];
                $action     = ($actions[$treatmentType] ?? $actions['default'])[array_rand($actions[$treatmentType] ?? $actions['default'])];
                $costRange  = $costRanges[$treatmentType] ?? $costRanges['default'];
                $recordDate = Carbon::parse($animal->created_at)->addDays(rand(7, 180));

                DB::connection('animals')->table('medical')->insert([
                    'treatment_type' => $treatmentType,
                    'diagnosis'      => $diagnosis,
                    'action'         => $action,
                    'remarks'        => $remarksOptions[array_rand($remarksOptions)],
                    'costs'          => rand($costRange[0], $costRange[1]),
                    'vetID'          => $vet->id,
                    'animalID'       => $animal->id,
                    'created_at'     => $recordDate,
                    'updated_at'     => $recordDate,
                ]);

                $totalMedicalRecords++;
            }
        }

        $avg = count($animals) > 0 ? round($totalMedicalRecords / count($animals), 1) : 0;
        $this->command->info("Total medical records created: {$totalMedicalRecords}");
        $this->command->info("Average medical records per animal: {$avg}");
    }
}
