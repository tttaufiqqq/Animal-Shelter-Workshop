@props([
    'route' => '#',
    'icon' => '',
    'label' => '',
    'active' => false,
    'badge' => null,
])

@php
    $isActive = $active || request()->routeIs($route);
    $classes = $isActive
        ? 'bg-purple-700 text-white shadow-lg'
        : 'text-purple-100 hover:bg-purple-700/50 hover:text-white';
@endphp

<a href="{{ route($route) }}"
   class="{{ $classes }} flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group relative"
   title="{{ $label }}">
    <!-- Icon -->
    <div class="flex-shrink-0 w-5 h-5">
        {!! $icon !!}
    </div>

    <!-- Label -->
    <span class="font-medium text-sm flex-1">{{ $label }}</span>

    <!-- Badge (optional) -->
    @if($badge)
        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
            {{ $badge }}
        </span>
    @endif

    <!-- Active Indicator -->
    @if($isActive)
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"></div>
    @endif
</a>
