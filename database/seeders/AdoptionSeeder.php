<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdoptionSeeder extends Seeder
{
    public function run()
    {
        // Get only successful transactions
        $transactions = DB::table('transaction')
            ->where('status', 'Success')
            ->get();

        $adoptions = [];

        foreach ($transactions as $tx) {

            // Find the booking linked to this transaction, if exists
            $booking = DB::table('booking')
                ->where('userID', $tx->userID)
                ->first();

            // If no booking exists, skip
            if (!$booking) continue;

            // Get animal details
            $animal = DB::table('animal')->where('id', $booking->animalID)->first();
            if (!$animal) continue;

            // Create adoption record structure
            $adoptions[] = [
                'fee'           => $tx->amount,
                'remarks'       => $animal->name . ' Adopted',
                'bookingID'     => $booking->id,
                'transactionID' => $tx->id,
                'created_at'    => $tx->created_at,
                'updated_at'    => $tx->updated_at,
            ];
        }

        // Insert into adoption table
        if (!empty($adoptions)) {
            DB::table('adoption')->insert($adoptions);
        }
    }
}
