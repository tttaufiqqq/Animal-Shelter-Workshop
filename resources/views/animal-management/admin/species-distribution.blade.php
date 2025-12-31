@php
// Ensure required variables exist with safe defaults
if (!isset($stats)) {
    $stats = [
        'totalAnimals' => 0,
        'error' => true,
        'errorMessage' => 'Statistics could not be calculated',
    ];
}
if (!isset($speciesBreakdown)) {
    $speciesBreakdown = collect([]);
}
@endphp

{{-- Species Distribution Overview --}}
<div class="bg-white rounded-xl shadow-lg p-6 mb-6 {{ $stats['error'] ? 'opacity-60' : '' }}">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-chart-pie text-purple-600"></i>
            Species Distribution
            @if($stats['error'])
                <span class="ml-2 px-2 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                    <i class="fas fa-plug mr-1"></i>Offline
                </span>
            @endif
        </h2>
        <div class="text-sm text-gray-500">{{ $speciesBreakdown->count() }} species types</div>
    </div>

    @if($stats['error'])
        {{-- Offline State --}}
        <div class="text-center py-12 text-gray-500">
            <div class="bg-orange-50 border-2 border-orange-200 rounded-lg p-6 inline-block">
                <i class="fas fa-database text-5xl mb-3 text-orange-300"></i>
                <p class="text-sm font-medium text-gray-700">Species data unavailable</p>
                <p class="text-xs text-gray-500 mt-1">Database is offline</p>
            </div>
        </div>
    @elseif($speciesBreakdown->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($speciesBreakdown as $species => $count)
                @php
                    $percentage = $stats['totalAnimals'] > 0 ? round(($count / $stats['totalAnimals']) * 100, 1) : 0;
                    $colors = [
                        'Dog' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'Cat' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'Rabbit' => 'bg-pink-100 text-pink-800 border-pink-300',
                        'Bird' => 'bg-sky-100 text-sky-800 border-sky-300',
                        'Hamster' => 'bg-orange-100 text-orange-800 border-orange-300',
                    ];
                    $colorClass = $colors[$species] ?? 'bg-gray-100 text-gray-800 border-gray-300';

                    $emojis = [
                        'Dog' => '🐕',
                        'Cat' => '🐱',
                        'Rabbit' => '🐰',
                        'Bird' => '🐦',
                        'Hamster' => '🐹',
                    ];
                    $emoji = $emojis[$species] ?? '🐾';
                @endphp
                <div class="stat-card border-2 {{ $colorClass }} rounded-lg p-4 text-center">
                    <div class="text-2xl mb-1">{{ $emoji }}</div>
                    <div class="font-bold text-lg">{{ $count }}</div>
                    <div class="text-xs font-semibold">{{ $species }}</div>
                    <div class="text-xs opacity-75 mt-1">{{ $percentage }}%</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-paw text-4xl mb-3 opacity-30"></i>
            <p class="text-sm">No species data available yet</p>
        </div>
    @endif
</div>
