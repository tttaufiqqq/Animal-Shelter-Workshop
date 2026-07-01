<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

trait LoadsBookingAnimals
{
    private function loadAnimalsForBookings($bookings, $loadImages = false)
    {
        if ($bookings->isEmpty()) {
            return;
        }

        if (!$this->isDatabaseAvailable('animals')) {
            $bookings->each(fn($booking) => $booking->setRelation('animals', collect([])));
            return;
        }

        $animalIds = $bookings->flatMap(fn($booking) => $booking->animalBookings->pluck('animalID'))->unique()->values()->toArray();

        if (empty($animalIds)) {
            $bookings->each(fn($booking) => $booking->setRelation('animals', collect([])));
            return;
        }

        $animalsQuery = Animal::whereIn('id', $animalIds)->with(['medicals', 'vaccinations']);
        if ($loadImages) {
            $animalsQuery->with('images');
        }
        $animals = $animalsQuery->get()->keyBy('id');

        $bookings->each(function($booking) use ($animals) {
            $bookingAnimals = $booking->animalBookings->map(function($animalBooking) use ($animals) {
                $animal = $animals->get($animalBooking->animalID);
                if ($animal) {
                    $animal->pivot = $animalBooking;
                }
                return $animal;
            })->filter();

            $booking->setRelation('animals', $bookingAnimals);
        });
    }

    private function loadAnimalsForVisitList($visitList, $withRelations = [])
    {
        if (!$this->isDatabaseAvailable('animals')) {
            return collect([]);
        }

        $animalIds = DB::connection('booking')
            ->table('visit_list_animal')
            ->where('listID', $visitList->id)
            ->pluck('animalID')
            ->toArray();

        if (empty($animalIds)) {
            return collect([]);
        }

        $query = Animal::whereIn('id', $animalIds);
        if (!empty($withRelations)) {
            $query->with($withRelations);
        }

        return $query->get();
    }

    private function getAnimalsWithBookingConflicts(array $animalIds, $appointmentDate, $appointmentTime)
    {
        $conflictingBookings = Booking::where('appointment_date', $appointmentDate)
            ->where('appointment_time', $appointmentTime)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->with('user')
            ->get();

        if ($conflictingBookings->isEmpty()) {
            return collect([]);
        }

        $bookingIds = $conflictingBookings->pluck('id')->toArray();
        $conflictingAnimalIds = DB::connection('booking')
            ->table('animal_booking')
            ->whereIn('bookingID', $bookingIds)
            ->whereIn('animalID', $animalIds)
            ->pluck('animalID')
            ->unique()
            ->toArray();

        if (empty($conflictingAnimalIds)) {
            return collect([]);
        }

        if (!$this->isDatabaseAvailable('animals')) {
            return collect([]);
        }

        $animals = Animal::whereIn('id', $conflictingAnimalIds)->get();

        $animals->each(function($animal) use ($conflictingBookings, $bookingIds) {
            $animalBookingIds = DB::connection('booking')
                ->table('animal_booking')
                ->where('animalID', $animal->id)
                ->whereIn('bookingID', $bookingIds)
                ->pluck('bookingID')
                ->toArray();

            $animal->setRelation('bookings', $conflictingBookings->whereIn('id', $animalBookingIds));
        });

        return $animals;
    }

    private function getBookedAnimalsErrorMessage($bookedAnimals, $currentUserId = null)
    {
        $messages = [];

        foreach ($bookedAnimals as $animal) {
            $booking = $animal->bookings->first();
            $isOwnBooking = $currentUserId && $booking->userID == $currentUserId;
            $userInfo = $isOwnBooking ? 'by you' : 'by ' . ($booking->user->name ?? 'another user');

            $messages[] = sprintf(
                '<strong>%s</strong>: Already booked %s on <strong>%s at %s</strong> (Booking #%d - %s)',
                $animal->name,
                $userInfo,
                \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y'),
                \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A'),
                $booking->id,
                $booking->status
            );
        }

        return $messages;
    }
}
