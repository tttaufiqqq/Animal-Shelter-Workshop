@props([
    'text' => '',
    'position' => 'bottom',
    'size' => 'sm',
    'color' => 'purple',
    'arrow' => true,
])

<div class="relative" x-data="{ showTooltip: false }">
    <div @mouseenter="showTooltip = true"
         @mouseleave="showTooltip = false"
         class="inline-block">
        {{ $slot }}
    </div>

    <x-tooltip
        :text="$text"
        :position="$position"
        :size="$size"
        :color="$color"
        :arrow="$arrow"
    />
</div>
