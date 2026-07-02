{{-- main Orchestrator --}}
@php
    $bookingsDataArray = $bookings->map(function($booking) {
        return [
            'id' => $booking->id,
            'status' => $booking->status,
            'appointment_date' => $booking->appointment_date,
            'appointment_time' => $booking->appointment_time,
            'created_at' => $booking->created_at,
            'animals' => $booking->animals->map(function($animal) {
                return [
                    'id' => $animal->id,
                    'name' => $animal->name,
                    'species' => $animal->species,
                    'breed' => $animal->breed,
                    'gender' => $animal->gender,
                    'image_url' => $animal->images->first()?->url ?? null,
                ];
            })->values(),
            'adoptions' => $booking->adoptions->map(function($adoption) {
                return [
                    'id' => $adoption->id,
                    'fee' => $adoption->fee,
                    'remarks' => $adoption->remarks,
                    'created_at' => \Carbon\Carbon::parse($adoption->created_at)->format('M d, Y'),
                    'animal' => $adoption->animal ? [
                        'id' => $adoption->animal->id,
                        'name' => $adoption->animal->name,
                        'species' => $adoption->animal->species,
                        'breed' => $adoption->animal->breed,
                        'gender' => $adoption->animal->gender,
                        'image_url' => $adoption->animal->images->first()?->url ?? null,
                    ] : null,
                    'transaction' => $adoption->transaction ? [
                        'id' => $adoption->transaction->id,
                        'amount' => $adoption->transaction->amount,
                        'status' => $adoption->transaction->status,
                        'bill_code' => $adoption->transaction->bill_code,
                        'reference_no' => $adoption->transaction->reference_no,
                        'created_at' => \Carbon\Carbon::parse($adoption->transaction->created_at)->format('M d, Y h:i A'),
                    ] : null,
                ];
            })->values(),
        ];
    })->values();
@endphp
@include('booking-adoption.main.head')
@include('booking-adoption.main.connectivity-page-header')
@include('booking-adoption.main.stats-search')
@include('booking-adoption.main.bookings-table')
@include('booking-adoption.main.payment-modal')
@include('booking-adoption.main.loading-adoption-modal')
@include('booking-adoption.main.scripts-adoption-modal')
@include('booking-adoption.main.scripts-close')
