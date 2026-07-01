<!-- Location Card -->
<div class="bg-white rounded-lg shadow-lg p-4">
    <h2 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
        <i class="fas fa-map-marker-alt text-purple-600 mr-2"></i>
        Assigned Slot
    </h2>

    @if($animal->relationLoaded('slot') && $animal->slot)
        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-700 font-semibold">Name</span>
                <span class="text-purple-700 font-bold text-lg">
                {{ $animal->slot->name ?? 'Slot ' . $animal->slot->id }}
            </span>
            </div>

            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-700 font-semibold">Section</span>
                <span class="text-gray-800">{{ $animal->slot->section->name ?? 'Unknown Section' }}</span>
            </div>

            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-700 font-semibold">Status</span>

                @php
                    $status = $animal->slot->status;
                    $colorClass = match($status) {
                        'available' => 'bg-green-200 text-green-700',
                        'occupied' => 'bg-red-200 text-red-700',
                        default => 'bg-gray-100 text-gray-700'
                    };
                @endphp

                <span class="px-2 py-1 rounded text-xs font-medium {{ $colorClass }}">
                 {{ ucfirst($status) }}
            </span>
            </div>

            {{-- Reassign Slot Form --}}
            @role('caretaker')
            <div class="border-t border-purple-200 pt-4 mt-4">
                <form action="{{ route('animals.assignSlot', $animal->id) }}" method="POST" id="reassignSlotForm" onsubmit="handleReassignSlotSubmit(event)">
                    @csrf

                    <label class="block text-gray-700 font-semibold mb-2">Reassign Slot</label>
                    <select name="slot_id"
                            class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            required>
                        <option value="">Select Slot</option>
                        @foreach($slots as $slot)
                            <option value="{{ $slot->id }}"
                                {{ $animal->slotID == $slot->id ? 'selected' : '' }}>
                                Slot {{ $slot->name ?? $slot->id }} - {{ $slot->section->name ?? 'Unknown Section' }} ({{ ucfirst($slot->status) }})
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" id="reassignSlotBtn"
                            class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="reassignSlotBtnText">
                            <i class="fas fa-sync-alt mr-2"></i>Update Slot
                        </span>
                        <span id="reassignSlotBtnLoading" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Reassigning...
                        </span>
                    </button>
                </form>
            </div>
            @endrole
        </div>
    @else
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <i class="fas fa-map-marker-slash text-gray-400 text-4xl mb-3"></i>
            <p class="text-gray-600 font-medium mb-4">No slot assigned</p>

            {{-- Assign Slot Form --}}
            @role('caretaker')
            <form action="{{ route('animals.assignSlot', $animal->id) }}" method="POST" class="mt-4" id="assignSlotForm" onsubmit="handleAssignSlotSubmit(event)">
                @csrf

                <label class="block text-gray-700 font-semibold mb-2">Assign Slot</label>
                <select name="slot_id"
                        class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        required>
                    <option value="">Select Slot</option>
                    @foreach($slots as $slot)
                        <option value="{{ $slot->id }}">
                            Slot {{ $slot->name ?? $slot->id }} - {{ $slot->section->name ?? 'Unknown Section' }} ({{ ucfirst($slot->status) }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" id="assignSlotBtn"
                        class="w-full bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="assignSlotBtnText">
                        <i class="fas fa-plus mr-2"></i>Assign Slot
                    </span>
                    <span id="assignSlotBtnLoading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Assigning...
                    </span>
                </button>
            </form>
            @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center mt-4">
                <i class="fas fa-lock text-gray-400 mb-2"></i>
                <p class="text-xs text-gray-500">Only caretakers can assign slots</p>
            </div>
            @endrole
        </div>
    @endif
</div>
