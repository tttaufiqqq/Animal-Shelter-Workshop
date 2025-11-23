<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $users = DB::table('users')->pluck('id')->toArray();
        $animals = DB::table('animal')->select('id', 'name')->get();

        $statuses = ['Failed', 'Success'];

        $records = [];

        foreach ($animals as $animal) {
            $date = Carbon::now()->subDays(rand(0, 180));

            $records[] = [
                'amount'       => rand(50, 300),
                'status'       => $statuses[array_rand($statuses)],
                'remarks'      => 'Adoption fee for ' . $animal->name,
                'type'         => 'FPX Online Banking',
                'bill_code'    => 'BILL-' . strtoupper(Str::random(8)),
                'reference_no' => 'REF-' . date('Ymd', $date->timestamp) . '-' . rand(1000, 9999),
                'userID'       => $users[array_rand($users)],
                'created_at'   => $date,
                'updated_at'   => $date,
            ];
        }

        DB::table('transaction')->insert($records);

        $this->command->info(count($records) . ' transactions created successfully!');
    }
}
