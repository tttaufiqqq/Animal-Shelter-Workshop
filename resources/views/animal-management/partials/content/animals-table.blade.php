{{-- Table View for Admin and Caretaker "My Rescues" --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        ID
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Image
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Species
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Age
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Gender
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Health
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Location
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($animals as $animal)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        {{-- ID --}}
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            #{{ $animal->id }}
                        </td>

                        {{-- Image --}}
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($animal->images && $animal->images->isNotEmpty())
                                <img src="{{ $animal->images->first()->url }}"
                                     alt="{{ $animal->name }}"
                                     class="w-12 h-12 rounded object-cover border border-gray-200">
                            @else
                                <div class="w-12 h-12 rounded bg-gray-200 flex items-center justify-center border border-gray-300">
                                    <i class="fas fa-paw text-gray-400"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Name --}}
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $animal->name }}</div>
                            <div class="text-xs text-gray-500">Weight: {{ $animal->weight }}kg</div>
                        </td>

                        {{-- Species --}}
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if(strtolower($animal->species) == 'dog')
                                    <i class="fas fa-dog text-purple-600"></i>
                                @elseif(strtolower($animal->species) == 'cat')
                                    <i class="fas fa-cat text-purple-600"></i>
                                @else
                                    <i class="fas fa-paw text-purple-600"></i>
                                @endif
                                <span class="text-sm text-gray-900">{{ $animal->species }}</span>
                            </div>
                        </td>

                        {{-- Age --}}
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $animal->age }}
                        </td>

                        {{-- Gender --}}
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($animal->gender == 'Male')
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-mars text-blue-600"></i>
                                    Male
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-venus text-pink-600"></i>
                                    Female
                                </span>
                            @endif
                        </td>

                        {{-- Health --}}
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($animal->health_details == 'Healthy')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800 border border-green-200">
                                    Healthy
                                </span>
                            @elseif($animal->health_details == 'Sick')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800 border border-red-200">
                                    Sick
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    Need Observation
                                </span>
                            @endif
                        </td>

                        {{-- Adoption Status --}}
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($animal->adoption_status == 'Not Adopted')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800 border border-blue-200">
                                    Available
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800 border border-gray-200">
                                    Adopted
                                </span>
                            @endif
                        </td>

                        {{-- Location (Slot) --}}
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($animal->slot)
                                <div class="text-xs">
                                    <div class="font-medium">{{ $animal->slot->section->name ?? 'N/A' }}</div>
                                    <div class="text-gray-500">{{ $animal->slot->name }}</div>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">Unassigned</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('animal-management.show', $animal->id) }}"
                               onclick="showLoadingOverlay()"
                               class="inline-flex items-center gap-1 px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded">
                                <i class="fas fa-eye"></i>
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Table Footer --}}
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between text-sm text-gray-700">
            <div>
                Showing <span class="font-bold">{{ $animals->count() }}</span> of <span class="font-bold">{{ $animals->total() }}</span> animals
            </div>
            <div class="text-xs text-gray-500">
                @if(request('rescued_by_me') === 'true')
                    <i class="fas fa-filter mr-1"></i>Filtered by your rescues
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Pagination for Table View --}}
<div class="mt-6 flex justify-center">
    <div class="bg-white rounded-lg shadow-sm p-4">
        {{ $animals->links() }}
    </div>
</div>
