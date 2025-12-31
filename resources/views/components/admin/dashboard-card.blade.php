@props([
    'title' => '',
    'icon' => null,
    'iconColor' => 'text-purple-600',
])

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
    <!-- Card Header -->
    @if($title)
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                @if($icon)
                    <div class="{{ $iconColor }}">
                        {!! $icon !!}
                    </div>
                @endif
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ $title }}</h3>
            </div>
        </div>
    @endif

    <!-- Card Content -->
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
