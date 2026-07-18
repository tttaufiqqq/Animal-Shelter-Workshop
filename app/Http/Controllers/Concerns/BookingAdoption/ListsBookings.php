<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ListsBookings
{
    public function index(Request $request)
    {
        $result = $this->safeQuery(function() use ($request) {
            $taufiqOnline = $this->isDatabaseAvailable('users');
            $relationships = ['adoptions', 'animalBookings'];
            if ($taufiqOnline) $relationships[] = 'user';

            $query = Booking::where('userID', Auth::id())->with($relationships);

            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(fn($q) => $q->where('id', 'LIKE', "%{$search}%")->orWhere('appointment_date', 'LIKE', "%{$search}%")->orWhere('remarks', 'LIKE', "%{$search}%"));
            }
            if ($request->filled('date_from')) $query->where('appointment_date', '>=', $request->date_from);
            if ($request->filled('date_to')) $query->where('appointment_date', '<=', $request->date_to);

            $bookings = $query->orderBy('created_at', 'desc')->paginate(40)->appends($request->query());

            $this->loadAnimalsForBookings($bookings, true);
            $bookings->each(function($booking) use ($taufiqOnline) {
                if (!$taufiqOnline && !$booking->relationLoaded('user')) $booking->setRelation('user', null);
            });

            $statusCounts = Booking::where('userID', Auth::id())->select('status', DB::connection('booking')->raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts', 'totalBookings');
        }, ['bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40), 'statusCounts' => collect([]), 'totalBookings' => 0], 'booking');

        return view('booking-adoption.main', $result);
    }

    public function indexAdmin(Request $request)
    {
        $result = $this->safeQuery(function() use ($request) {
            $taufiqOnline = $this->isDatabaseAvailable('users');
            $relationships = ['adoptions', 'animalBookings'];
            if ($taufiqOnline) $relationships[] = 'user';

            $query = Booking::with($relationships);

            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('user_search') && $taufiqOnline) {
                $userSearch = $request->user_search;
                $userIds = DB::connection('users')->table('users')->where(fn($q) => $q->where('name', 'LIKE', "%{$userSearch}%")->orWhere('email', 'LIKE', "%{$userSearch}%"))->pluck('id')->toArray();
                $query->when(!empty($userIds), fn($q) => $q->whereIn('userID', $userIds), fn($q) => $q->whereRaw('1 = 0'));
            }
            if ($request->filled('booking_id')) $query->where('id', $request->booking_id);
            if ($request->filled('date_from')) $query->where('appointment_date', '>=', $request->date_from);
            if ($request->filled('date_to')) $query->where('appointment_date', '<=', $request->date_to);

            $bookings = $query->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc')->paginate(40)->appends($request->query());

            $this->loadAnimalsForBookings($bookings, true);
            $bookings->each(function($booking) use ($taufiqOnline) {
                if (!$taufiqOnline && !$booking->relationLoaded('user')) $booking->setRelation('user', null);
            });

            $statusCounts = Booking::select('status', DB::connection('booking')->raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
            $totalBookings = $statusCounts->sum();

            return compact('bookings', 'statusCounts', 'totalBookings');
        }, ['bookings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40), 'statusCounts' => collect([]), 'totalBookings' => 0], 'booking');

        return view('booking-adoption.admin', $result);
    }
}
