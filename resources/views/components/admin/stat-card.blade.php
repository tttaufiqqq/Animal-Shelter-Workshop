@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'color' => 'purple',
])

@php
    $colorClasses = [
        'purple' => 'bg-purple-50 text-purple-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-green-50 text-green-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'red' => 'bg-red-50 text-red-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
    ];
    $bgColor = $colorClasses[$color] ?? $colorClasses['purple'];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-200">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 mb-2">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>

            @if($trend)
                <div class="flex items-center gap-1 mt-2">
                    @if($trendUp)
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    @endif
                    <span class="text-sm font-medium {{ $trendUp ? 'text-green-600' : 'text-red-600' }}">
                        {{ $trend }}
                    </span>
                </div>
            @endif
        </div>

        @if($icon)
            <div class="w-12 h-12 {{ $bgColor }} rounded-lg flex items-center justify-center flex-shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
