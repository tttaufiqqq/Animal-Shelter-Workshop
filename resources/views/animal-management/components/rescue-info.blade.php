<!-- Rescue Information -->
@if($animal->rescue)
    <div class="fade-in bg-gradient-to-br from-white to-pink-50/30 rounded-2xl shadow-xl p-4 border border-pink-100 hover-scale">
        <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
            <div class="bg-gradient-to-br from-pink-500 to-rose-600 p-2 rounded-lg shadow-lg">
                <i class="fas fa-hand-holding-heart text-white text-lg"></i>
            </div>
            <span>Rescue Information</span>
        </h2>
        <div class="bg-white rounded-xl p-4 shadow-inner border border-gray-100 space-y-3">
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <div class="bg-pink-100 p-2 rounded-lg">
                    <i class="fas fa-hashtag text-pink-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase">Rescue ID</p>
                    <p class="text-gray-800 font-bold">#{{ $animal->rescue->id }}</p>
                </div>
            </div>
            @if($animal->rescue->report)
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <div class="bg-pink-100 p-2 rounded-lg">
                        <i class="fas fa-map-marker-alt text-pink-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-semibold uppercase">Location</p>
                        <p class="text-gray-800 font-medium">{{ $animal->rescue->report->address }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <div class="bg-pink-100 p-2 rounded-lg">
                        <i class="fas fa-city text-pink-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-semibold uppercase">City</p>
                        <p class="text-gray-800 font-medium">{{ $animal->rescue->report->city }}, {{ $animal->rescue->report->state }}</p>
                    </div>
                </div>
            @endif
            <div class="flex items-center gap-3">
                <div class="bg-pink-100 p-2 rounded-lg">
                    <i class="fas fa-calendar text-pink-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase">Rescue Date</p>
                    <p class="text-gray-800 font-bold">{{ $animal->rescue->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
