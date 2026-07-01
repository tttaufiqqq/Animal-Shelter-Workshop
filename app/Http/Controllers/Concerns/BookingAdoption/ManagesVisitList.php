<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\VisitList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesVisitList
{
    public function userBookings()
    {
        $bookings = $this->safeQuery(
            fn() => \App\Models\Booking::with('animals')->where('userID', auth()->id())->orderBy('appointment_date', 'desc')->get(),
            collect([]),
            'booking'
        );

        return view('booking-adoption.main', compact('bookings'));
    }

    public function indexList()
    {
        $user = Auth::user();

        $animals = $this->safeQuery(function() use ($user) {
            $visitList = VisitList::firstOrCreate(['userID' => $user->id]);

            $withRelations = [];

            if ($this->isDatabaseAvailable('reporting')) {
                $withRelations[] = 'images';
            }

            if ($this->isDatabaseAvailable('booking')) {
                $withRelations['bookings'] = function($query) use ($user) {
                    $query->where('userID', $user->id)->where('status', 'Pending')->latest();
                };
            }

            return $this->loadAnimalsForVisitList($visitList, $withRelations);
        }, collect([]), 'booking');

        return view('booking-adoption.visit-list', compact('animals'));
    }

    public function addList($animalId)
    {
        $user = Auth::user();

        $animal = $this->safeQuery(fn() => Animal::find($animalId), null, 'animals');

        if (!$animal) {
            return back()->with('error', 'Animal does not exist or database connection unavailable.');
        }

        $result = $this->safeQuery(function() use ($user, $animalId, $animal) {
            $visitList = VisitList::firstOrCreate(['userID' => $user->id]);

            $exists = DB::connection('booking')
                ->table('visit_list_animal')
                ->where('listID', $visitList->id)
                ->where('animalID', $animalId)
                ->exists();

            if ($exists) {
                return ['error' => 'This animal is already in your visit list.'];
            }

            DB::connection('booking')->table('visit_list_animal')->insert([
                'listID' => $visitList->id,
                'animalID' => $animalId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['success' => "{$animal->name} has been added to your visit list. You can view the list at the top right"];
        }, ['error' => 'Database connection unavailable. Please try again later.'], 'booking');

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', $result['success']);
    }

    public function removeList($animalId)
    {
        try {
            $user = Auth::user();
            $userId = $user->id;

            \Log::info('Removing animal from visit list', ['user_id' => $userId, 'animal_id' => $animalId]);

            DB::connection('booking')->beginTransaction();

            try {
                $visitList = VisitList::on('booking')->where('userID', $userId)->first();

                if (!$visitList) {
                    DB::connection('booking')->rollBack();
                    \Log::warning('Visit list not found', ['user_id' => $userId]);
                    return back()->with('error', 'Visit list not found.');
                }

                DB::connection('booking')->table('visit_list_animal')
                    ->where('listID', $visitList->id)
                    ->where('animalID', $animalId)
                    ->delete();

                \Log::info('Animal removed successfully', ['visit_list_id' => $visitList->id, 'animal_id' => $animalId]);

                DB::connection('booking')->commit();

                return back()->with(['success' => 'Animal removed from your visit list.', 'open_visit_modal' => true]);

            } catch (\Exception $e) {
                DB::connection('booking')->rollBack();
                \Log::error('Database error in removeList transaction', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error removing animal from visit list: ' . $e->getMessage(), ['user_id' => Auth::id(), 'animal_id' => $animalId, 'trace' => $e->getTraceAsString()]);
            return back()->with(['error' => 'Failed to remove animal from visit list: ' . $e->getMessage() . ' [' . get_class($e) . ']', 'open_visit_modal' => true]);
        }
    }
}
