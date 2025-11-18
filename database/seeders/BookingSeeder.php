<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run()
    {
        $users = DB::table('users')->pluck('id')->toArray();
        $animalIds = DB::table('animal')->pluck('id')->toArray();
        $statuses = ['Pending', 'Completed', 'Confirmed', 'Cancelled'];

        for ($i = 0; $i < 200; $i++) {

            $date = now()->subDays(rand(0, 180));

            DB::table('booking')->insert([
                'appointment_date' => $date->format('Y-m-d'),
                'appointment_time' => sprintf("%02d:%02d:00", rand(8, 17), rand(0, 59)),
                'status'           => $statuses[array_rand($statuses)],
                'remarks'          => 'N/A',
                'animalID'         => $animalIds[array_rand($animalIds)],
                'userID'           => $users[array_rand($users)],
                'created_at'       => $date,
                'updated_at'       => $date,
            ]);
        }
    }

}
