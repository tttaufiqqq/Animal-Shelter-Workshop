<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Adoption;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public $selectedYear;
    public $selectedCategory = 'all';
    public $years = [];

    public function mount()
    {
        $this->selectedYear = date('Y');

        // Cross-database compatible year extraction
        $this->years = Booking::selectRaw($this->getYearExpression('created_at') . ' as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    public function render()
    {
        // Key Metrics
        $totalBookings = $this->getTotalBookings();
        $successfulBookings = $this->getSuccessfulBookings();
        $cancelledBookings = $this->getCancelledBookings();
        $bookingSuccessRate = $totalBookings > 0 ?
            round(($successfulBookings / $totalBookings) * 100, 2) : 0;
        $repeatCustomerRate = $this->getRepeatCustomerRate();

        // Top Animals by Revenue
        $topAnimals = $this->getTopAnimalsByRevenue();

        // Booking Type Breakdown
        $bookingTypeBreakdown = $this->getBookingTypeBreakdown();

        // Bookings by Month
        $bookingsByMonth = $this->getBookingsByMonth();

        // Booking Volume vs Average Value
        $volumeVsValue = $this->getVolumeVsAverageValue();

        return view('livewire.dashboard', [
            'totalBookings' => $totalBookings,
            'successfulBookings' => $successfulBookings,
            'cancelledBookings' => $cancelledBookings,
            'bookingSuccessRate' => $bookingSuccessRate,
            'repeatCustomerRate' => $repeatCustomerRate,
            'topAnimals' => $topAnimals,
            'bookingTypeBreakdown' => $bookingTypeBreakdown,
            'bookingsByMonth' => $bookingsByMonth,
            'volumeVsValue' => $volumeVsValue,
        ]);
    }

    /**
     * Get database-specific YEAR extraction expression
     */
    private function getYearExpression($column)
    {
        $driver = DB::getDriverName();

        return match($driver) {
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'mysql' => "YEAR({$column})",
            'sqlite' => "strftime('%Y', {$column})",
            default => "YEAR({$column})",
        };
    }

    /**
     * Get database-specific MONTH extraction expression
     */
    private function getMonthExpression($column)
    {
        $driver = DB::connection()->getDriverName();

        switch ($driver) {
            case 'pgsql':
                return "EXTRACT(MONTH FROM {$column})";
            case 'mysql':
                return "MONTH({$column})";
            case 'sqlite':
                return "CAST(strftime('%m', {$column}) AS INTEGER)";
            case 'sqlsrv':
                return "MONTH({$column})";
            default:
                return "MONTH({$column})";
        }
    }

    /**
     * Get WHERE clause for year filtering
     */
    private function whereYear($query, $column)
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return $query->whereRaw("EXTRACT(YEAR FROM {$column}) = ?", [$this->selectedYear]);
        } else {
            return $query->whereYear($column, $this->selectedYear);
        }
    }

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

    private function getRepeatCustomerRate()
    {
        $query = $this->whereYear(Booking::query(), 'created_at');

        // Clone query for total customers
        $totalCustomers = (clone $query)
            ->distinct()
            ->count('userID');

        // Get repeat customers using subquery (works across all databases)
        $repeatCustomers = (clone $query)
            ->select('userID')
            ->groupBy('userID')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return $totalCustomers > 0 ?
            round(($repeatCustomers / $totalCustomers) * 100, 2) : 0;
    }

    private function getTopAnimalsByRevenue()
    {
        $query = Adoption::join('booking', 'adoption.bookingID', '=', 'booking.id')
            ->join('animal_booking', 'booking.id', '=', 'animal_booking.bookingID')
            ->join('animal', 'animal_booking.animalID', '=', 'animal.id');

        $query = $this->whereYear($query, 'adoption.created_at');

        $results = $query
            ->select('animal.species as name', DB::raw('SUM(adoption.fee) as total_revenue'))
            ->groupBy('animal.species')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // Use the sum from the SAME joined data for consistent percentages
        $totalRevenue = $results->sum('total_revenue');

        return $results->map(function($item) use ($totalRevenue) {
            $item->percentage = $totalRevenue > 0
                ? round(($item->total_revenue / $totalRevenue) * 100, 2)
                : 0;
            return $item;
        });
    }

    private function getBookingTypeBreakdown()
    {
        $query = $this->whereYear(Booking::query(), 'created_at');
        $total = (clone $query)->count();

        $breakdown = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function($item) use ($total) {
                $item->percentage = $total > 0 ?
                    round(($item->count / $total) * 100, 2) : 0;
                return $item;
            });

        return $breakdown;
    }

    private function getBookingsByMonth()
    {
        $monthExpression = $this->getMonthExpression('created_at');

        $query = $this->whereYear(Booking::query(), 'created_at');

        return $query
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw($monthExpression))
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                $item->month_name = Carbon::create()->month((int)$item->month)->format('F');
                return $item;
            });
    }

    private function getVolumeVsAverageValue()
    {
        $monthExpression = $this->getMonthExpression('created_at');
        $yearExpression = $this->getYearExpression('created_at');

        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

        // Debug: check the date
        \Log::info('Six months ago: ' . $sixMonthsAgo);

        $results = Adoption::query()
            ->where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw("{$yearExpression} as year")
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('COUNT(*) as volume')
            ->selectRaw('AVG(fee) as avg_value')
            ->groupBy(DB::raw($yearExpression), DB::raw($monthExpression))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Debug: check results
        \Log::info('Results: ' . $results->toJson());

        return $results->map(function($item) {
            $item->month_name = Carbon::create($item->year, $item->month, 1)->format('M');
            $item->avg_value = round((float) $item->avg_value, 2);
            return $item;
        });
    }


}
