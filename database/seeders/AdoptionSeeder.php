<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\Animal;
use App\Models\Adoption;
use Illuminate\Support\Facades\DB;

class AdoptionSeeder extends Seeder
{
    public function run()
    {
        // Get all successful transactions
        $transactions = Transaction::where('status', 'Success')->get();

        $usedBookingIds = [];

        foreach ($transactions as $tx) {
            // Find a booking for this user that hasn't been adopted yet
            $booking = Booking::where('userID', $tx->userID)
                ->whereNotIn('id', $usedBookingIds)
                ->inRandomOrder() // Randomly pick a booking
                ->first();

            // Skip if no booking found
            if (!$booking) continue;

            // Mark this booking as used
            $usedBookingIds[] = $booking->id;

            // Get related animal via relationship
            $animal = $booking->animal;

            if (!$animal) continue;

            // Create adoption record
            Adoption::create([
                'fee'           => $tx->amount,
                'remarks'       => $animal->name . ' Adopted',
                'bookingID'     => $booking->id,
                'transactionID' => $tx->id,
                'created_at'    => $tx->created_at,
                'updated_at'    => $tx->updated_at,
            ]);
        }

        $this->command->info('Adoptions seeded successfully!');
    }
}
