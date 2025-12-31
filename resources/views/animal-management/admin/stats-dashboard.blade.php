@php
// Ensure $stats exists with safe defaults
if (!isset($stats)) {
    $stats = [
        'totalAnimals' => 0,
        'availableCount' => 0,
        'adoptedCount' => 0,
        'medicalAttentionCount' => 0,
        'recentCount' => 0,
        'error' => true,
        'errorMessage' => 'Statistics could not be calculated',
    ];
}
@endphp

{{-- Admin Statistics Dashboard --}}
@if($stats['error'])
    {{-- Offline State Warning --}}
    <div class="mb-6 bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-300 rounded-xl p-6">
        <div class="flex items-start gap-4">
            <div class="bg-orange-500 text-white p-3 rounded-lg">
                <i class="fas fa-database text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-800 mb-2">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                    Statistics Unavailable
                </h3>
                <p class="text-gray-700 mb-3">{{ $stats['errorMessage'] }}</p>
                <div class="bg-white bg-opacity-60 rounded-lg p-3 text-sm text-gray-600">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    <strong>Distributed Architecture:</strong> The animal management database is temporarily offline.
                    The page remains functional, but real-time statistics cannot be displayed.
                    Other modules continue to operate normally.
                </div>
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Animals --}}
    <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white {{ $stats['error'] ? 'opacity-60' : '' }}">
        <div class="flex items-center justify-between mb-3">
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="fas fa-paw text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ $stats['totalAnimals'] }}</div>
                <div class="text-xs text-blue-100 uppercase tracking-wide">Total Animals</div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-blue-100">
            @if($stats['error'])
                <i class="fas fa-plug text-red-300"></i>
                <span>Offline</span>
            @else
                <i class="fas fa-chart-line"></i>
                <span>+{{ $stats['recentCount'] }} this week</span>
            @endif
        </div>
    </div>

    {{-- Available for Adoption --}}
    <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white {{ $stats['error'] ? 'opacity-60' : '' }}">
        <div class="flex items-center justify-between mb-3">
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="fas fa-heart text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ $stats['availableCount'] }}</div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Available</div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-green-100">
            @if($stats['error'])
                <i class="fas fa-plug text-red-300"></i>
                <span>Offline</span>
            @else
                <i class="fas fa-home"></i>
                <span>Ready for adoption</span>
            @endif
        </div>
    </div>

    {{-- Successfully Adopted --}}
    <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white {{ $stats['error'] ? 'opacity-60' : '' }}">
        <div class="flex items-center justify-between mb-3">
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ $stats['adoptedCount'] }}</div>
                <div class="text-xs text-purple-100 uppercase tracking-wide">Adopted</div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-purple-100">
            @if($stats['error'])
                <i class="fas fa-plug text-red-300"></i>
                <span>Offline</span>
            @else
                <i class="fas fa-smile"></i>
                <span>Happy homes found</span>
            @endif
        </div>
    </div>

    {{-- Medical Attention Needed --}}
    <div class="stat-card bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white {{ $stats['error'] ? 'opacity-60' : ($stats['medicalAttentionCount'] > 0 ? 'pulse-red' : '') }}">
        <div class="flex items-center justify-between mb-3">
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="fas fa-heartbeat text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ $stats['medicalAttentionCount'] }}</div>
                <div class="text-xs text-red-100 uppercase tracking-wide">Under Treatment</div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-red-100">
            @if($stats['error'])
                <i class="fas fa-plug text-red-300"></i>
                <span>Offline</span>
            @else
                <i class="fas fa-{{ $stats['medicalAttentionCount'] > 0 ? 'exclamation-triangle' : 'check' }}"></i>
                <span>{{ $stats['medicalAttentionCount'] > 0 ? 'Needs attention' : 'All healthy' }}</span>
            @endif
        </div>
    </div>
</div>
