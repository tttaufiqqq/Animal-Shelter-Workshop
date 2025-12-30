<div class="bg-gradient-to-br from-{{ $colorScheme }}-50 to-{{ $colorScheme }}-100 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-{{ $colorScheme }}-200 group hover:scale-105">
    <div class="flex items-center justify-between mb-3">
        <p class="text-{{ $colorScheme }}-700 text-sm font-semibold uppercase tracking-wide">{{ $title }}</p>
        <div class="bg-gradient-to-br from-{{ $colorScheme }}-500 to-{{ $colorScheme }}-600 rounded-lg p-2.5 shadow-md group-hover:shadow-lg transition-shadow">
            <i class="{{ $icon }} text-white text-lg"></i>
        </div>
    </div>
    <p class="text-4xl font-bold text-{{ $colorScheme }}-900 mb-1">{{ $value }}</p>
    <p class="text-xs text-{{ $colorScheme }}-600 font-medium">{{ $description }}</p>
</div>
