{{-- Slots View --}}
<div id="slotsContent" class="content-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    @if($slots->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Slot Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Section
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Capacity
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Occupancy
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Animals
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Inventory
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($slots as $slot)
                    @php
                        $statusColors = [
                            'available' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-500', 'progress' => 'bg-green-500'],
                            'occupied' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-500', 'progress' => 'bg-orange-500'],
                            'maintenance' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-500', 'progress' => 'bg-red-500'],
                        ];
                        $colors = $statusColors[$slot->status] ?? $statusColors['available'];
                        // Safely check if animals relationship is loaded (cross-database - may be unavailable)
                        $occupancy = $slot->relationLoaded('animals') ? $slot->animals->count() : null;
                        $occupancyPercent = ($occupancy !== null && $slot->capacity > 0) ? ($occupancy / $slot->capacity) * 100 : 0;
                    @endphp
                    <tr class="slot-row hover:bg-gray-50 transition-colors duration-150"
                        data-slot-name="{{ strtolower($slot->name) }}"
                        data-slot-status="{{ $slot->status }}"
                        data-slot-section="{{ $slot->section->name ?? '' }}">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-bold text-gray-900">{{ $slot->name }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-layer-group text-gray-400 mr-2"></i>
                                <span class="text-sm text-gray-700">{{ $slot->section->name ?? 'No Section' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                                   <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors['bg'] }} {{ $colors['text'] }}">
                                       {{ ucfirst($slot->status) }}
                                   </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">
                            {{ $slot->capacity }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($occupancy !== null)
                            <div class="flex items-center">
                                <div class="w-full max-w-[120px]">
                                    <div class="flex items-center justify-between mb-1">
                                               <span class="text-xs font-medium {{ $colors['text'] }}">
                                                   {{ $occupancy }} / {{ $slot->capacity }}
                                               </span>
                                        <span class="text-xs text-gray-500">
                                                   {{ number_format($occupancyPercent, 0) }}%
                                               </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="{{ $colors['progress'] }} h-2 rounded-full transition-all duration-500"
                                             style="width: {{ min($occupancyPercent, 100) }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="flex items-center">
                                <span class="text-xs text-gray-400 italic">Data unavailable</span>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-paw text-gray-400 mr-2"></i>
                                <span class="text-sm font-semibold text-gray-700">{{ $occupancy ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-box text-gray-400 mr-2"></i>
                                <span class="text-sm font-semibold text-gray-700">{{ $slot->inventories->count() }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="viewSlotDetails({{ $slot->id }})"
                                        class="text-indigo-600 hover:text-indigo-900 transition duration-150"
                                        title="View Details">
                                    <i class="fas fa-info-circle text-lg"></i>
                                </button>
                                @role('admin')
                                <button onclick="editSlot({{ $slot->id }})"
                                        class="text-gray-600 hover:text-gray-900 transition duration-150"
                                        title="Edit Slot">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <button onclick="deleteSlot({{ $slot->id }})"
                                        class="text-red-600 hover:text-red-900 transition duration-150"
                                        title="Delete Slot">
                                    <i class="fas fa-trash text-lg"></i>
                                </button>
                                @endrole
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        {{-- Empty State --}}
        <div class="p-12 text-center">
            <div class="text-6xl mb-4">🏠</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No Slots Yet</h3>
            <p class="text-gray-600 mb-6">Start by adding your first shelter slot.</p>
            @role('admin')
            <button onclick="openSlotModal()" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition duration-300">
                <i class="fas fa-plus mr-2"></i>Add Your First Slot
            </button>
            @endrole
        </div>
    @endif
</div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $slots->links() }}
    </div>
</div>
