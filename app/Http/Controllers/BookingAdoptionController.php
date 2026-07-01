<?php

namespace App\Http\Controllers;

use App\DatabaseErrorHandler;
use App\Http\Controllers\Concerns\BookingAdoption\LoadsBookingAnimals;
use App\Http\Controllers\Concerns\BookingAdoption\ManagesVisitList;
use App\Http\Controllers\Concerns\BookingAdoption\ConfirmsAppointment;
use App\Http\Controllers\Concerns\BookingAdoption\ManagesBookings;
use App\Http\Controllers\Concerns\BookingAdoption\HandlesPayment;

class BookingAdoptionController extends Controller
{
    use DatabaseErrorHandler,
        LoadsBookingAnimals,
        ManagesVisitList,
        ConfirmsAppointment,
        ManagesBookings,
        HandlesPayment;
}
