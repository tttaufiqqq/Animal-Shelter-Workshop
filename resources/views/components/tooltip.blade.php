@props([
    'text' => '',
    'position' => 'bottom', // bottom, top, left, right
    'size' => 'sm', // xs, sm, md
    'color' => 'purple', // purple, gray, blue
    'arrow' => true,
    'delay' => 0,
    'fixed' => false, // Use fixed positioning (for sidebar tooltips)
])

@php
    // Position classes
    $positionClasses = [
        'bottom' => 'left-1/2 transform -translate-x-1/2 top-full mt-2',
        'top' => 'left-1/2 transform -translate-x-1/2 bottom-full mb-2',
        'left' => 'right-full top-1/2 transform -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 transform -translate-y-1/2 ml-2',
    ];

    // Arrow position classes
    $arrowClasses = [
        'bottom' => 'absolute left-1/2 transform -translate-x-1/2 -top-1 w-2 h-2 rotate-45',
        'top' => 'absolute left-1/2 transform -translate-x-1/2 -bottom-1 w-2 h-2 rotate-45',
        'left' => 'absolute top-1/2 transform -translate-y-1/2 -right-1 w-2 h-2 rotate-45',
        'right' => 'absolute top-1/2 transform -translate-y-1/2 -left-1 w-2 h-2 rotate-45',
    ];

    // Size classes
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-2 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
    ];

    // Color classes
    $colorClasses = [
        'purple' => 'bg-white text-gray-700 border-purple-200',
        'gray' => 'bg-white text-gray-700 border-gray-200',
        'blue' => 'bg-white text-gray-700 border-blue-200',
    ];

    $textColorClasses = [
        'purple' => 'text-purple-900',
        'gray' => 'text-gray-900',
        'blue' => 'text-blue-900',
    ];

    $arrowColorClasses = [
        'purple' => 'bg-white border-l border-t border-purple-200',
        'gray' => 'bg-white border-l border-t border-gray-200',
        'blue' => 'bg-white border-l border-t border-blue-200',
    ];
@endphp

<div x-show="showTooltip"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-1"
     class="{{ $fixed ? 'fixed' : 'absolute' }} {{ $positionClasses[$position] }} {{ $sizeClasses[$size] }} {{ $colorClasses[$color] }} font-medium rounded-lg shadow-xl border max-w-xs text-center {{ $fixed ? 'z-[9999]' : 'z-[100]' }}"
     style="display: none; {{ $fixed ? 'pointer-events: none;' : '' }}">
    <span class="{{ $textColorClasses[$color] }}">{!! $text !!}</span>

    @if($arrow)
        <div class="{{ $arrowClasses[$position] }} {{ $arrowColorClasses[$color] }}"></div>
    @endif
</div>
