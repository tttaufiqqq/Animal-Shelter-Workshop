<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Adoption;
use App\Models\Animal;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public $selectedYear;
    public $years = [];

    public function mount()
    {
        $this->selectedYear = date('Y');

        $this->years = Booking::selectRaw($this->getYearExpression('created_at') . ' as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            // Key Metrics
            'totalBookings' => $this->getTotalBookings(),
            'successfulBookings' => $this->getSuccessfulBookings(),
            'cancelledBookings' => $this->getCancelledBookings(),
            'pendingBookings' => $this->getPendingBookings(),
            'bookingSuccessRate' => $this->getBookingSuccessRate(),
            'repeatCustomerRate' => $this->getRepeatCustomerRate(),

            // Financial Metrics
            'totalRevenue' => $this->getTotalRevenue(),
            'totalAdoptions' => $this->getTotalAdoptions(),
            'avgAdoptionFee' => $this->getAverageAdoptionFee(),

            // Charts Data
            'topAnimals' => $this->getTopAnimalsByRevenue(),
            'bookingTypeBreakdown' => $this->getBookingTypeBreakdown(),
            'bookingsByMonth' => $this->getBookingsByMonth(),
            'volumeVsValue' => $this->getVolumeVsAverageValue(),
            'revenueByMonth' => $this->getRevenueByMonth(),
            'transactionStatus' => $this->getTransactionStatusBreakdown(),
        ]);
    }

    // ==================== HELPER METHODS ====================

    private function getYearExpression($column)
    {
        $driver = DB::connection()->getDriverName();
        return match($driver) {
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'sqlite' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
            default => "YEAR({$column})",
        };
    }

    private function getMonthExpression($column)
    {
        $driver = DB::connection()->getDriverName();
        return match($driver) {
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            default => "MONTH({$column})",
        };
    }

    private function whereYear($query, $column)
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            return $query->whereRaw("EXTRACT(YEAR FROM {$column}) = ?", [$this->selectedYear]);
        }
        return $query->whereYear($column, $this->selectedYear);
    }

    // ==================== BOOKING METRICS ====================

    private function getTotalBookings()
    {
        return $this->whereYear(Booking::query(), 'created_at')->count();
    }

    private function getSuccessfulBookings()
    {
        return $this->whereYear(Booking::query(), 'created_at')
            ->where('status', 'Completed')
            ->count();
    }

    private function getCancelledBookings()
    {
        return $this->whereYear(Booking::query(), 'created_at')
            ->where('status', 'Cancelled')
            ->count();
    }

    private function getPendingBookings()
    {
        return $this->whereYear(Booking::query(), 'created_at')
            ->where('status', 'Pending')
            ->count();
    }

    private function getBookingSuccessRate()
    {
        $total = $this->getTotalBookings();
        $successful = $this->getSuccessfulBookings();
        return $total > 0 ? round(($successful / $total) * 100, 2) : 0;
    }

    private function getRepeatCustomerRate()
    {
        $query = $this->whereYear(Booking::query(), 'created_at');

        $totalCustomers = (clone $query)->distinct()->count('userID');
        $repeatCustomers = (clone $query)
            ->select('userID')
            ->groupBy('userID')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 2) : 0;
    }

    // ==================== FINANCIAL METRICS ====================

    private function getTotalRevenue()
    {
        return $this->whereYear(Transaction::query(), 'created_at')
            ->where('status', 'Completed')
            ->sum('amount');
    }

    private function getTotalAdoptions()
    {
        return $this->whereYear(Adoption::query(), 'created_at')->count();
    }

    private function getAverageAdoptionFee()
    {
        return $this->whereYear(Adoption::query(), 'created_at')->avg('fee') ?? 0;
    }

    // ==================== CHART DATA ====================

    private function getTopAnimalsByRevenue()
    {
        $query = Adoption::query()
            ->join('booking', 'adoption.bookingID', '=', 'booking.id')
            ->join('AnimalBooking', 'booking.id', '=', 'AnimalBooking.bookingID')
            ->join('animal', 'AnimalBooking.animalID', '=', 'animal.id');

        $query = $this->whereYear($query, 'adoption.created_at');

        $results = $query
            ->select('animal.species as name', DB::raw('SUM(adoption.fee) as total_revenue'))
            ->groupBy('animal.species')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        $totalRevenue = $this->whereYear(Adoption::query(), 'created_at')->sum('fee');

        return [
            'animals' => $results->map(function($item) use ($totalRevenue) {
                $item->percentage = $totalRevenue > 0 ?
                    round(($item->total_revenue / $totalRevenue) * 100, 2) : 0;
                return $item;
            }),
            'totalRevenue' => $totalRevenue
        ];
    }

    private function getBookingTypeBreakdown()
    {
        $query = $this->whereYear(Booking::query(), 'created_at');
        $total = (clone $query)->count();

        return (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function($item) use ($total) {
                $item->percentage = $total > 0 ? round(($item->count / $total) * 100, 2) : 0;
                return $item;
            });
    }

    private function getBookingsByMonth()
    {
        $monthExpr = $this->getMonthExpression('created_at');
        $query = $this->whereYear(Booking::query(), 'created_at');

        return $query
            ->selectRaw("{$monthExpr} as month")
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw($monthExpr))
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'month' => $item->month,
                'month_name' => Carbon::create()->month((int)$item->month)->format('F'),
                'count' => $item->count
            ]);
    }

    private function getVolumeVsAverageValue()
    {
        $monthExpr = $this->getMonthExpression('adoption.created_at');

        $query = Adoption::join('booking', 'adoption.bookingID', '=', 'booking.id');
        $query = $this->whereYear($query, 'adoption.created_at');

        return $query
            ->selectRaw("{$monthExpr} as month")
            ->selectRaw('COUNT(*) as volume')
            ->selectRaw('AVG(adoption.fee) as avg_value')
            ->groupBy(DB::raw($monthExpr))
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'month' => $item->month,
                'month_name' => Carbon::create()->month((int)$item->month)->format('M'),
                'volume' => $item->volume,
                'avg_value' => round($item->avg_value, 2)
            ]);
    }

    private function getRevenueByMonth()
    {
        $monthExpr = $this->getMonthExpression('created_at');

        $query = $this->whereYear(Transaction::query(), 'created_at')
            ->where('status', 'Completed');

        return $query
            ->selectRaw("{$monthExpr} as month")
            ->selectRaw('SUM(amount) as revenue')
            ->selectRaw('COUNT(*) as transactions')
            ->groupBy(DB::raw($monthExpr))
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'month' => $item->month,
                'month_name' => Carbon::create()->month((int)$item->month)->format('M'),
                'revenue' => round($item->revenue, 2),
                'transactions' => $item->transactions
            ]);
    }

    private function getTransactionStatusBreakdown()
    {
        $query = $this->whereYear(Transaction::query(), 'created_at');
        $total = (clone $query)->count();

        return (clone $query)
            ->select('status', DB::raw('count(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('status')
            ->get()
            ->map(function($item) use ($total) {
                $item->percentage = $total > 0 ? round(($item->count / $total) * 100, 2) : 0;
                return $item;
            });
    }
}
