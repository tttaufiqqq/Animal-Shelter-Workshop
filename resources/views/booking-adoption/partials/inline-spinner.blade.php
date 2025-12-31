{{-- Reusable Inline Spinner Component --}}
{{-- Usage: @include('booking-adoption.partials.inline-spinner', ['size' => 'sm|md|lg', 'color' => 'white|purple|gray']) --}}

@php
    $size = $size ?? 'md';
    $color = $color ?? 'white';

    $sizeClasses = [
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6',
        'xl' => 'w-8 h-8',
    ];

    $colorClasses = [
        'white' => 'text-white',
        'purple' => 'text-purple-600',
        'gray' => 'text-gray-600',
        'green' => 'text-green-600',
        'red' => 'text-red-600',
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $colorClass = $colorClasses[$color] ?? $colorClasses['white'];
@endphp

<svg class="animate-spin {{ $sizeClass }} {{ $colorClass }}" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>
