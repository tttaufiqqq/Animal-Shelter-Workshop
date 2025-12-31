<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Adoption;
use App\Models\Animal;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\DatabaseErrorHandler;

class Dashboard extends Component
{
    use DatabaseErrorHandler;
    public $selectedYear;
    public $selectedCategory = 'all';
    public $years = [];

    public function mount()
    {
        $this->selectedYear = date('Y');

        // Get years from Danish's database (bookings) with error handling
        $this->years = $this->safeQuery(
            fn() => Booking::selectRaw($this->getYearExpression('created_at', 'danish') . ' as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray(),
            [date('Y')],
            'danish' // Pre-check danish database
        );
    }

    public function render()
    {
        // Key Metrics (all from Danish's database)
        $totalBookings = $this->getTotalBookings();
        $successfulBookings = $this->getSuccessfulBookings();
        $cancelledBookings = $this->getCancelledBookings();
        $bookingSuccessRate = $totalBookings > 0 ?
            round(($successfulBookings / $totalBookings) * 100, 2) : 0;
        $repeatCustomerRate = $this->getRepeatCustomerRate();

        // Top Animals by Revenue (cross-database query: Danish + Shafiqah)
        $topAnimals = $this->getTopAnimalsByRevenue();

        // Booking Type Breakdown (Danish's database)
        $bookingTypeBreakdown = $this->getBookingTypeBreakdown();

        // Bookings by Month (Danish's database)
        $bookingsByMonth = $this->getBookingsByMonth();

        // Booking Volume vs Average Value (Danish's database)
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
    private function getYearExpression($column, $connection = 'danish')
    {
        $driver = DB::connection($connection)->getDriverName();

        return match($driver) {
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'mysql' => "YEAR({$column})",
            'sqlite' => "strftime('%Y', {$column})",
            'sqlsrv' => "YEAR({$column})",
            default => "YEAR({$column})",
        };
    }

    /**
     * Get database-specific MONTH extraction expression
     */
    private function getMonthExpression($column, $connection = 'danish')
    {
        $driver = DB::connection($connection)->getDriverName();

        return match($driver) {
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            'mysql' => "MONTH({$column})",
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'sqlsrv' => "MONTH({$column})",
            default => "MONTH({$column})",
        };
    }

    /**
     * Get WHERE clause for year filtering
     */
    private function whereYear($query, $column, $connection = 'danish')
    {
        $driver = DB::connection($connection)->getDriverName();

        if ($driver === 'pgsql') {
            return $query->whereRaw("EXTRACT(YEAR FROM {$column}) = ?", [$this->selectedYear]);
        } elseif ($driver === 'sqlsrv') {
            return $query->whereRaw("YEAR({$column}) = ?", [$this->selectedYear]);
        } else {
            return $query->whereYear($column, $this->selectedYear);
        }
    }

    /**
     * Get total bookings from Danish's database
     */
    private function getTotalBookings()
    {
        return $this->safeQuery(
            fn() => $this->whereYear(Booking::query(), 'created_at', 'danish')->count(),
            0,
            'danish' // Pre-check danish database
        );
    }

    /**
     * Get successful bookings from Danish's database
     */
    private function getSuccessfulBookings()
    {
        return $this->safeQuery(
            fn() => $this->whereYear(Booking::query(), 'created_at', 'danish')
                ->where('status', 'Completed')
                ->count(),
            0,
            'danish' // Pre-check danish database
        );
    }

    /**
     * Get cancelled bookings from Danish's database
     */
    private function getCancelledBookings()
    {
        return $this->safeQuery(
            fn() => $this->whereYear(Booking::query(), 'created_at', 'danish')
                ->where('status', 'Cancelled')
                ->count(),
            0,
            'danish' // Pre-check danish database
        );
    }

    /**
     * Get repeat customer rate from Danish's database
     */
    private function getRepeatCustomerRate()
    {
        return $this->safeQuery(function() {
            $query = $this->whereYear(Booking::query(), 'created_at', 'danish');

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
        }, 0, 'danish'); // Pre-check danish database
    }

    /**
     * Get top animals by revenue
     * Complex cross-database query: Danish (adoptions, bookings) + Shafiqah (animals)
     *
     * Note: This requires manual data fetching and processing due to cross-database joins
     */
    private function getTopAnimalsByRevenue()
    {
        // Pre-check both databases before attempting query
        if (!$this->isDatabaseAvailable('danish') || !$this->isDatabaseAvailable('shafiqah')) {
            Log::debug('Skipping getTopAnimalsByRevenue - required databases offline');
            return collect();
        }

        try {
            // Step 1: Get adoptions with animal IDs from Danish's database
            $adoptionsQuery = Adoption::on('danish')
                ->join('animal_booking', 'adoption.bookingID', '=', 'animal_booking.bookingID');

            $adoptionsQuery = $this->whereYear($adoptionsQuery, 'adoption.created_at', 'danish');

            $adoptionData = $adoptionsQuery
                ->select('animal_booking.animalID', 'adoption.fee')
                ->get();

            // Step 2: Group by animal ID and sum fees
            $animalRevenues = $adoptionData->groupBy('animalID')->map(function($group) {
                return $group->sum('fee');
            });

            // Step 3: Get animal details from Shafiqah's database
            $animalIds = $animalRevenues->keys()->toArray();

            if (empty($animalIds)) {
                return collect();
            }

            $animals = Animal::on('shafiqah')
                ->whereIn('id', $animalIds)
                ->get()
                ->keyBy('id');

            // Step 4: Combine data and group by species
            $speciesRevenue = collect();
            foreach ($animalRevenues as $animalId => $revenue) {
                $animal = $animals->get($animalId);
                if ($animal) {
                    $species = $animal->species;
                    $current = $speciesRevenue->get($species, 0);
                    $speciesRevenue->put($species, $current + $revenue);
                }
            }

            // Step 5: Sort and limit
            $topSpecies = $speciesRevenue
                ->sortDesc()
                ->take(5);

            // Step 6: Calculate percentages
            $totalRevenue = $topSpecies->sum();

            return $topSpecies->map(function($revenue, $species) use ($totalRevenue) {
                return (object) [
                    'name' => $species,
                    'total_revenue' => $revenue,
                    'percentage' => $totalRevenue > 0
                        ? round(($revenue / $totalRevenue) * 100, 2)
                        : 0,
                ];
            })->values();

        } catch (\Exception $e) {
            Log::error('Error getting top animals by revenue: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return collect();
        }
    }

    /**
     * Get booking type breakdown from Danish's database
     */
    private function getBookingTypeBreakdown()
    {
        return $this->safeQuery(function() {
            $query = $this->whereYear(Booking::on('danish'), 'created_at', 'danish');
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
        }, collect([]), 'danish'); // Pre-check danish database
    }

    /**
     * Get bookings by month from Danish's database
     */
    private function getBookingsByMonth()
    {
        return $this->safeQuery(function() {
            $monthExpression = $this->getMonthExpression('created_at', 'danish');

            $query = $this->whereYear(Booking::on('danish'), 'created_at', 'danish');

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
        }, collect([]), 'danish'); // Pre-check danish database
    }

    /**
     * Get volume vs average value from Danish's database
     */
    private function getVolumeVsAverageValue()
    {
        return $this->safeQuery(function() {
            $monthExpression = $this->getMonthExpression('created_at', 'danish');
            $yearExpression = $this->getYearExpression('created_at', 'danish');

            $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

            // Debug: check the date
            Log::info('Six months ago: ' . $sixMonthsAgo);

            // Query Danish's database for adoptions
            $results = Adoption::on('danish')
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
            Log::info('Volume vs Value Results: ' . $results->toJson());

            return $results->map(function($item) {
                $item->month_name = Carbon::create($item->year, $item->month, 1)->format('M Y');
                $item->avg_value = round((float) $item->avg_value, 2);
                return $item;
            });
        }, collect([]), 'danish'); // Pre-check danish database
    }

    /**
     * Get additional statistics for enhanced dashboard
     * All from Danish's database
     */
    public function getAdditionalStats()
    {
        try {
            $query = $this->whereYear(Booking::on('danish'), 'created_at', 'danish');

            return [
                'pending_bookings' => (clone $query)->where('status', 'Pending')->count(),
                'confirmed_bookings' => (clone $query)->where('status', 'Confirmed')->count(),
                'total_revenue' => Adoption::on('danish')
                    ->whereYear('created_at', $this->selectedYear)
                    ->sum('fee'),
                'average_booking_value' => Adoption::on('danish')
                    ->whereYear('created_at', $this->selectedYear)
                    ->avg('fee'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting additional stats: ' . $e->getMessage());

            return [
                'pending_bookings' => 0,
                'confirmed_bookings' => 0,
                'total_revenue' => 0,
                'average_booking_value' => 0,
            ];
        }
    }
}
