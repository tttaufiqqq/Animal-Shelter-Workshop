<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Revenue by Adopted Species</h2>
            <div class="text-right">
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-2xl font-bold text-purple-700">
                    RM {{ number_format($topAnimals->sum('total_revenue'), 2) }}
                </p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            @forelse($topAnimals as $animal)
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-gray-800">{{ $animal->name }}</span>
                        <span class="text-gray-600">{{ number_format($animal->percentage, 2) }}% (RM {{ number_format($animal->total_revenue, 2) }})</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-purple-600 h-3 rounded-full transition-all duration-500" style="width: {{ $animal->percentage }}%"></div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <p>No revenue data available</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
