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
