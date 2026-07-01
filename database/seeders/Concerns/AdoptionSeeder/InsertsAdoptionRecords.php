<?php

namespace Database\Seeders\Concerns\AdoptionSeeder;

use Illuminate\Support\Facades\DB;

trait InsertsAdoptionRecords
{
    private function insertAdoptionRecords(
        array $allTransactions,
        array $transactionMeta,
        $animalsData,
        array $medicalCounts,
        array $vaccinationCounts,
        int $chunkSize = 50
    ): void {
        foreach (array_chunk($allTransactions, $chunkSize) as $chunk) {
            DB::connection('booking')->table('transaction')->insert($chunk);
        }

        $billCodes     = array_column($transactionMeta, 'billCode');
        $insertedTrans = DB::connection('booking')->table('transaction')
            ->whereIn('bill_code', $billCodes)
            ->get(['id', 'bill_code'])
            ->keyBy('bill_code');

        $baseFees        = ['dog' => 20, 'cat' => 10];
        $medicalRate     = 10;
        $vaccinationRate = 20;
        $allAdoptions    = [];

        foreach ($transactionMeta as $meta) {
            $trans = $insertedTrans->get($meta['billCode']);
            if (!$trans) {
                continue;
            }

            foreach ($meta['animalIds'] as $animalId) {
                $animal = $animalsData->get($animalId);
                if (!$animal) {
                    continue;
                }

                $species     = strtolower($animal->species);
                $base        = $baseFees[$species] ?? 15;
                $medical     = ($medicalCounts[$animalId] ?? 0) * $medicalRate;
                $vaccination = ($vaccinationCounts[$animalId] ?? 0) * $vaccinationRate;

                $allAdoptions[] = [
                    'fee'           => $base + $medical + $vaccination,
                    'remarks'       => $animal->name . ' successfully adopted',
                    'bookingID'     => $meta['bookingID'],
                    'transactionID' => $trans->id,
                    'animalID'      => $animalId,
                    'created_at'    => $meta['date'],
                    'updated_at'    => $meta['date'],
                ];
            }
        }

        foreach (array_chunk($allAdoptions, $chunkSize) as $chunk) {
            DB::connection('booking')->table('adoption')->insert($chunk);
        }
    }
}
