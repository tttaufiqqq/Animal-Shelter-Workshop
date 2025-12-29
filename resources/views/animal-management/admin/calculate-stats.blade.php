@php
/**
 * Admin Dashboard Statistics Calculator
 *
 * Calculates and prepares all statistics needed for the admin dashboard.
 * Returns a $stats array and $speciesBreakdown collection.
 *
 * FAULT TOLERANCE: Gracefully handles database connection failures
 * in distributed architecture. Returns default values if database is offline.
 *
 * Usage: Include this file at the top of admin view components
 * Required: $animals variable must be passed from controller
 */

// Initialize with safe default values
$stats = [
    'totalAnimals' => 0,
    'availableCount' => 0,
    'adoptedCount' => 0,
    'medicalAttentionCount' => 0,
    'recentCount' => 0,
    'error' => false,
    'errorMessage' => null,
];

$speciesBreakdown = collect([]);

// Check if $animals variable exists
if (!isset($animals)) {
    $stats['error'] = true;
    $stats['errorMessage'] = 'Animals data not available';
    \Log::error('Animal Management Admin: $animals variable not defined in view');
} else {
    // Try to fetch statistics with fault tolerance
    try {
        // Test database connection first
        \DB::connection('shafiqah')->getPdo();

        // Calculate core statistics
        $stats['totalAnimals'] = $animals->total();
        $stats['availableCount'] = \App\Models\Animal::where('adoptionStatus', 'Available')->count();
        $stats['adoptedCount'] = \App\Models\Animal::where('adoptionStatus', 'Adopted')->count();
        $stats['medicalAttentionCount'] = \App\Models\Animal::where('medicalStatus', 'Under Treatment')->count();
        $stats['recentCount'] = \App\Models\Animal::where('created_at', '>=', now()->subDays(7))->count();

        // Species breakdown with counts
        $speciesBreakdown = \App\Models\Animal::select('species', \DB::raw('count(*) as count'))
            ->groupBy('species')
            ->pluck('count', 'species');

    } catch (\Exception $e) {
        // Database is offline - use graceful degradation
        $stats['error'] = true;
        $stats['errorMessage'] = 'Animal database (Shafiqah) is currently offline';

        // Try to get basic count from paginator if available
        try {
            if (isset($animals) && method_exists($animals, 'total')) {
                $stats['totalAnimals'] = $animals->total();
            }
        } catch (\Exception $e) {
            $stats['totalAnimals'] = 0;
        }

        // Log the error for admin awareness
        \Log::warning('Animal Management Admin: Database connection failed', [
            'connection' => 'shafiqah',
            'error' => $e->getMessage(),
        ]);
    }
}

// Ensure variables are available to parent and subsequent includes
// This is a workaround for Blade variable scoping issues
@endphp
