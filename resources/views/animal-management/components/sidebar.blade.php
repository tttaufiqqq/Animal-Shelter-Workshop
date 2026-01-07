<!-- Right Column - Details -->
<div class="space-y-4 lg:sticky lg:top-4 lg:self-start lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
    <!-- Status Card -->
    <div class="bg-white rounded-lg shadow-lg p-4 flex justify-between">
        <div class="flex-1">
            <div class="mb-4">
                @if($animal->adoption_status == 'Not Adopted')
                    <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                    <i class="fas fa-check-circle mr-1"></i>Available for Adoption
                </span>
                @elseif($animal->adoption_status == 'Adopted')
                    <span class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                    <i class="fas fa-home mr-1"></i>Adopted
                </span>
                @else
                    <span class="inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">
                    {{ $animal->adoption_status }}
                </span>
                @endif
            </div>
            <div class="flex justify-between items-center mb-4">
                <!-- Left: Title -->
                <h2 class="text-xl font-bold text-gray-800">Animal Information</h2>

                @role('caretaker')
                <!-- Right: Action Icons -->
                <div class="flex space-x-4">
                    <a onclick="openEditModal({{ $animal->id }})"
                       class="text-purple-600 hover:text-purple-800 cursor-pointer transition duration-300"
                       title="Edit">
                        <i class="fas fa-edit text-xl"></i>
                    </a>

                    <form action="{{ route('animal-management.destroy', $animal->id) }}"
                          method="POST"
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this animal record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-red-600 hover:text-red-800 cursor-pointer transition duration-300 border-0 bg-transparent p-0"
                                title="Delete">
                            <i class="fas fa-trash text-xl"></i>
                        </button>
                    </form>
                </div>
                @endrole
            </div>



            <!-- Animal Details -->
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Species</span>
                    <span class="text-gray-800">{{ $animal->species }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Weight</span>
                    <span class="text-gray-800">{{ $animal->weight }} kg</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Age</span>
                    <span class="text-gray-800">{{ $animal->age }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Gender</span>
                    <span class="text-gray-800">{{ $animal->gender }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Arrival Date</span>
                    <span class="text-gray-800">{{ \Carbon\Carbon::parse($animal->arrival_date)->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-4">
        <h2 class="text-lg font-bold text-gray-800 mb-3">
            <i class="fas fa-paw text-purple-600 mr-2"></i>
            Animal Profile
        </h2>

        @if($animalProfile)
            <!-- Header with Edit Button: This is displayed when the profile exists -->
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <!-- Button to open the modal for editing -->
                @role('caretaker')<button type="button" onclick="openAnimalModal()" class="text-sm text-purple-600 hover:text-purple-800 font-semibold transition flex items-center">
                    <i class="fas fa-edit mr-1"></i> Edit Profile
                </button>@endrole
            </div>

            <div class="space-y-3">

                <!-- Size -->
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Size</span>
                    <span class="text-gray-800">{{ ucfirst($animalProfile->size) }}</span>
                </div>

                <!-- Energy Level -->
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Energy Level</span>
                    <span class="text-gray-800">{{ ucfirst($animalProfile->energy_level) }}</span>
                </div>

                <!-- Good with Kids (Boolean to Yes/No) -->
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Good with Kids</span>
                    <span class="text-gray-800">{{ $animalProfile->good_with_kids ? 'Yes' : 'No' }}</span>
                </div>

                <!-- Good with Pets (Boolean to Yes/No) -->
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Good with Pets</span>
                    <span class="text-gray-800">{{ $animalProfile->good_with_pets ? 'Yes' : 'No' }}</span>
                </div>

                <!-- Temperament -->
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Temperament</span>
                    <span class="text-gray-800">{{ ucfirst($animalProfile->temperament) }}</span>
                </div>

                <!-- Medical Needs -->
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-semibold">Medical Needs</span>
                    <span class="text-gray-800">{{ ucfirst($animalProfile->medical_needs) }}</span>
                </div>
            </div>

        @else
            <!-- Case when no profile exists -->
            <p class="text-gray-500 mb-4">This animal does not have a profile yet.</p>

            <!-- Button to open the modal for creation -->
            @role('caretaker')
            <button type="button" onclick="openAnimalModal()" class="w-full bg-purple-100 text-purple-700 py-3 rounded-lg font-semibold hover:bg-purple-200 transition duration-300 shadow-sm">
                <i class="fas fa-plus mr-2"></i>Add Profile
            </button>
            @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                <i class="fas fa-lock text-gray-400 mb-2"></i>
                <p class="text-xs text-gray-500">Only caretakers can add animal profiles</p>
            </div>
            @endrole

        @endif

    </div>

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

    <!-- Action Card -->
    @if($animal->adoption_status == 'Not Adopted')
        @role('public user|caretaker|adopter')
        <div class="bg-white rounded-lg shadow-lg p-4">
            <h2 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-heart text-purple-600 mr-2"></i>
                Interested in Adopting?
            </h2>
            <div class="bg-purple-50 rounded-lg p-3">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-paw text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-gray-800 font-semibold">{{ $animal->name }}</p>
                        <p class="text-gray-500 text-sm">Give this friend a loving home</p>
                    </div>
                </div>

                <p class="text-gray-600 text-sm mb-4">
                    Add to your visit list and schedule an appointment to meet them in person!
                </p>

                <form action="{{ route('visit.list.add', $animal->id) }}" method="POST" id="addToVisitListForm" onsubmit="handleAddToVisitListSubmit(event)">
                    @csrf
                    <button type="submit" id="addToVisitListBtn"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="addToVisitListBtnText" class="flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i>
                            Add to Visit List
                        </span>
                        <span id="addToVisitListBtnLoading" class="hidden flex items-center justify-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Adding...
                        </span>
                    </button>
                </form>

                <p class="text-gray-400 text-xs text-center mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    No commitment required
                </p>
            </div>
        </div>
        @else
        <div class="bg-white rounded-lg shadow-lg p-4">
            <h2 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-heart text-purple-600 mr-2"></i>
                Interested in Adopting?
            </h2>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                <i class="fas fa-lock text-gray-400 text-2xl mb-2"></i>
                <p class="text-sm text-gray-600 font-semibold mb-1">Login Required</p>
                <p class="text-xs text-gray-500">Please login as a user or adopter to add animals to your visit list</p>
            </div>
        </div>
        @endrole
    @endif
</div>

<script>
// Handle reassign slot form submission
function handleReassignSlotSubmit(event) {
    const submitBtn = document.getElementById('reassignSlotBtn');
    const btnText = document.getElementById('reassignSlotBtnText');
    const btnLoading = document.getElementById('reassignSlotBtnLoading');

    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    // Form will submit normally, button stays disabled
}

// Handle assign slot form submission
function handleAssignSlotSubmit(event) {
    const submitBtn = document.getElementById('assignSlotBtn');
    const btnText = document.getElementById('assignSlotBtnText');
    const btnLoading = document.getElementById('assignSlotBtnLoading');

    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    // Form will submit normally, button stays disabled
}

// Handle add to visit list form submission
function handleAddToVisitListSubmit(event) {
    const submitBtn = document.getElementById('addToVisitListBtn');
    const btnText = document.getElementById('addToVisitListBtnText');
    const btnLoading = document.getElementById('addToVisitListBtnLoading');

    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    // Form will submit normally, button stays disabled
}
</script>
