{{-- Animal Card Component --}}
@props(['animal'])

<div class="animal-card bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-transparent hover:border-purple-200">
    <!-- Animal Image -->
    <div class="h-56 bg-gradient-to-br from-purple-200 via-purple-300 to-indigo-300 flex items-center justify-center overflow-hidden relative group">
        @if($animal->relationLoaded('images') && $animal->images && $animal->images->count() > 0)
            <img src="{{ $animal->images->first()->url }}"
                 alt="{{ $animal->name }}"
                 class="animal-image w-full h-full object-cover">
        @else
            <div class="animal-image">
                @if(strtolower($animal->species) == 'dog')
                    <span class="text-9xl">🐕</span>
                @elseif(strtolower($animal->species) == 'cat')
                    <span class="text-9xl">🐈</span>
                @else
                    <span class="text-9xl">🐾</span>
                @endif
            </div>
        @endif

        <!-- Image Overlay Badge -->
        <div class="absolute top-3 right-3">
            @if($animal->adoption_status == 'Not Adopted')
                <span class="inline-flex items-center px-3 py-1.5 bg-green-500 text-white rounded-full text-xs font-bold shadow-lg backdrop-blur-sm">
                    <i class="fas fa-heart mr-1"></i>
                    Available for Adoption
                </span>
            @elseif($animal->adoption_status == 'Adopted')
                <span class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-full text-xs font-bold shadow-lg backdrop-blur-sm">
                    <i class="fas fa-home mr-1"></i>
                    Adopted
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white rounded-full text-xs font-bold shadow-lg backdrop-blur-sm">
                    {{ $animal->adoption_status }}
                </span>
            @endif
        </div>

        <!-- Quick View Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="bg-white text-purple-700 px-4 py-2 rounded-full font-bold shadow-xl">
                    <i class="fas fa-eye mr-2"></i>View Details
                </div>
            </div>
        </div>
    </div>

    <!-- Card Content -->
    <div class="p-6">
        <!-- Name -->
        <div class="mb-4">
            <h3 class="text-2xl font-bold text-gray-800 mb-1 flex items-center gap-2">
                {{ $animal->name }}
                @if(strtolower($animal->gender) == 'male')
                    <span class="text-blue-500 text-lg">♂</span>
                @else
                    <span class="text-pink-500 text-lg">♀</span>
                @endif
            </h3>
        </div>

        <!-- Details Grid -->
        <div class="space-y-3 mb-4">
            <div class="flex items-center gap-3 text-sm">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <i class="fas fa-paw text-purple-600"></i>
                </div>
                <div class="flex-1">
                    <span class="text-gray-600 text-xs font-semibold uppercase">Species</span>
                    <p class="text-gray-900 font-bold">{{ $animal->species }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <div class="bg-indigo-100 p-2 rounded-lg">
                    <i class="fas fa-birthday-cake text-indigo-600"></i>
                </div>
                <div class="flex-1">
                    <span class="text-gray-600 text-xs font-semibold uppercase">Age</span>
                    <p class="text-gray-900 font-bold">{{ $animal->age }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <span class="text-gray-600 text-xs font-semibold uppercase">Location</span>
                    <p class="text-gray-900 font-bold text-xs">
                        @if($animal->relationLoaded('slot') && $animal->slot)
                            Slot {{ $animal->slot->name ?? $animal->slot->id }} - {{ $animal->slot->section->name ?? 'Unknown' }}
                        @else
                            <span class="text-gray-400 italic">Not Assigned</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Health Details -->
        @if($animal->health_details)
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-3 rounded-xl mb-4 border border-purple-100">
                <p class="text-gray-700 text-sm line-clamp-2 leading-relaxed">
                    <i class="fas fa-info-circle text-purple-600 mr-1"></i>
                    {{ Str::limit($animal->health_details, 80) }}
                </p>
            </div>
        @endif

        <!-- Action Button -->
        <a href="{{ route('animal-management.show', $animal->id) }}"
           class="block w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white py-3 rounded-xl font-bold transition-all duration-300 text-center shadow-md hover:shadow-xl transform hover:-translate-y-1">
            <i class="fas fa-arrow-right mr-2"></i>
            View Full Profile
        </a>
    </div>
</div>
