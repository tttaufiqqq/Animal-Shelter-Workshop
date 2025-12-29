{{-- Animal Table Row Component --}}
@props(['animal'])

<tr class="hover:bg-purple-50 transition-colors duration-200">
    <!-- Animal Info -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 flex-shrink-0 rounded-full overflow-hidden border-2 border-purple-200 shadow-sm">
                @if($animal->relationLoaded('images') && $animal->images && $animal->images->count() > 0)
                    <img class="h-full w-full object-cover"
                         src="{{ $animal->images->first()->url }}"
                         alt="{{ $animal->name }}">
                @else
                    <div class="h-full w-full bg-gradient-to-br from-purple-200 to-indigo-200 flex items-center justify-center text-2xl">
                        @if(strtolower($animal->species) == 'dog')
                            🐕
                        @elseif(strtolower($animal->species) == 'cat')
                            🐈
                        @else
                            🐾
                        @endif
                    </div>
                @endif
            </div>
            <div>
                <div class="text-sm font-bold text-gray-900">{{ $animal->name }}</div>
                <div class="text-xs text-gray-500">ID: #{{ $animal->id }}</div>
            </div>
        </div>
    </td>

    <!-- Health Status -->
    <td class="px-6 py-4 whitespace-nowrap">
        @php
            $healthConfig = match($animal->health_details) {
                'Healthy' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-300', 'icon' => '✅'],
                'Sick' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-300', 'icon' => '🤒'],
                'Need Observation' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'border' => 'border-yellow-300', 'icon' => '👁️'],
                default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'icon' => '❓']
            };
        @endphp
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border-2 {{ $healthConfig['bg'] }} {{ $healthConfig['text'] }} {{ $healthConfig['border'] }} shadow-sm">
            <span class="mr-1.5">{{ $healthConfig['icon'] }}</span>
            {{ $animal->health_details ?? 'Unknown' }}
        </span>
    </td>

    <!-- Species & Gender -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900">{{ $animal->species }}</span>
            @if(strtolower($animal->gender) == 'male')
                <span class="text-blue-500 text-lg" title="Male">♂</span>
            @else
                <span class="text-pink-500 text-lg" title="Female">♀</span>
            @endif
        </div>
    </td>

    <!-- Age -->
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="text-sm text-gray-900 font-medium">{{ $animal->age }}</span>
    </td>

    <!-- Location -->
    <td class="px-6 py-4">
        <div class="text-sm text-gray-900">
            @if($animal->relationLoaded('slot') && $animal->slot)
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-map-pin text-purple-500 text-xs"></i>
                    <span class="font-medium">Slot {{ $animal->slot->name ?? $animal->slot->id }}</span>
                </div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $animal->slot->section->name ?? 'Unknown Section' }}</div>
            @else
                <span class="text-gray-400 italic text-xs">Not Assigned</span>
            @endif
        </div>
    </td>

    <!-- Adoption Status -->
    <td class="px-6 py-4 whitespace-nowrap">
        @if($animal->adoption_status == 'Not Adopted')
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 border-2 border-green-300 shadow-sm">
                <i class="fas fa-heart mr-1.5"></i>
                Available
            </span>
        @elseif($animal->adoption_status == 'Adopted')
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 border-2 border-blue-300 shadow-sm">
                <i class="fas fa-home mr-1.5"></i>
                Adopted
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border-2 border-gray-300">
                {{ $animal->adoption_status }}
            </span>
        @endif
    </td>

    <!-- Actions -->
    <td class="px-6 py-4 whitespace-nowrap text-right">
        <a href="{{ route('animal-management.show', $animal->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white text-sm font-bold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
            <i class="fas fa-eye"></i>
            <span>View</span>
        </a>
    </td>
</tr>
