<?php

namespace App\Http\Controllers;

use App\DatabaseErrorHandler;
use App\Http\Controllers\Concerns\BookingAdoption\LoadsBookingAnimals;
use App\Http\Controllers\Concerns\BookingAdoption\ManagesVisitList;
use App\Http\Controllers\Concerns\BookingAdoption\ConfirmsAppointment;
use App\Http\Controllers\Concerns\BookingAdoption\ListsBookings;
use App\Http\Controllers\Concerns\BookingAdoption\ManagesBookings;
use App\Http\Controllers\Concerns\BookingAdoption\ConfirmsBooking;
use App\Http\Controllers\Concerns\BookingAdoption\CreatesPaymentBill;
use App\Http\Controllers\Concerns\BookingAdoption\HandlesPayment;
use App\Http\Controllers\Concerns\BookingAdoption\StoresBooking;
use App\Http\Controllers\Concerns\BookingAdoption\CalculatesAdoptionFee;

class BookingAdoptionController extends Controller
{
    use DatabaseErrorHandler,
        LoadsBookingAnimals,
        ManagesVisitList,
        ConfirmsAppointment,
        ListsBookings,
        ManagesBookings,
        ConfirmsBooking,
        CreatesPaymentBill,
        HandlesPayment,
        StoresBooking,
        CalculatesAdoptionFee;
}
