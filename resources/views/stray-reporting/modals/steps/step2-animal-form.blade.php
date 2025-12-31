<!-- Step 2: Add Animal Form (Dynamic, shown per animal) -->
<div id="step2Content" class="hidden">
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500 p-4 mb-6 rounded-r shadow-sm">
        <div class="flex items-center gap-3">
            <div class="bg-purple-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                <i class="fas fa-paw"></i>
            </div>
            <p class="text-sm font-bold text-purple-900">
                <span id="animalProgress">Adding Animal 1 of 1</span>
            </p>
        </div>
    </div>

    <form id="animalForm" class="space-y-6">
        <!-- Animal Name -->
        <div>
            <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                <i class="fas fa-signature text-purple-600"></i>
                Animal Name <span class="text-red-600">*</span>
            </label>
            <input type="text" id="animalName" required
                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition"
                   placeholder="e.g., Buddy, Whiskers, Max...">
        </div>

        <!-- Species & Gender Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                    <i class="fas fa-paw text-purple-600"></i>
                    Species <span class="text-red-600">*</span>
                </label>
                <div class="relative">
                    <select id="animalSpecies" required onchange="updateAnimalAgeCategories()"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition appearance-none cursor-pointer bg-white">
                        <option value="">Select species</option>
                        <option value="Dog">🐕 Dog</option>
                        <option value="Cat">🐈 Cat</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                    <i class="fas fa-venus-mars text-purple-600"></i>
                    Gender <span class="text-red-600">*</span>
                </label>
                <div class="relative">
                    <select id="animalGender" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition appearance-none cursor-pointer bg-white">
                        <option value="">Select gender</option>
                        <option value="Male">♂️ Male</option>
                        <option value="Female">♀️ Female</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Age Category -->
        <div>
            <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                <i class="fas fa-birthday-cake text-purple-600"></i>
                Age Category <span class="text-red-600">*</span>
            </label>
            <div class="relative">
                <select id="animalAge" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition appearance-none cursor-pointer bg-white">
                    <option value="">Select age category</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                <i class="fas fa-info-circle text-purple-600"></i>
                <span>Age options change based on the selected species</span>
            </p>
        </div>

        <!-- Weight -->
        <div>
            <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                <i class="fas fa-weight text-purple-600"></i>
                Weight (kg) <span class="text-red-600">*</span>
            </label>
            <input type="number" id="animalWeight" min="0" step="0.1" required
                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition"
                   placeholder="e.g., 5.5">
            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                <i class="fas fa-info-circle text-purple-600"></i>
                <span>Enter the animal's approximate weight in kilograms</span>
            </p>
        </div>

        <!-- Health Details -->
        <div>
            <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                <i class="fas fa-heartbeat text-purple-600"></i>
                Health Condition <span class="text-red-600">*</span>
            </label>
            <div class="relative">
                <select id="animalHealthDetails" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition appearance-none cursor-pointer bg-white">
                    <option value="">Select health condition</option>
                    <option value="Healthy">✅ Healthy</option>
                    <option value="Sick">🤒 Sick</option>
                    <option value="Need Observation">👁️ Need Observation</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Shelter Slot Assignment -->
        <div>
            <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                <i class="fas fa-home text-purple-600"></i>
                Assign to Shelter Slot <span class="text-red-600">*</span>
            </label>
            <div class="relative">
                <select id="animalSlot" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition appearance-none cursor-pointer bg-white">
                    <option value="">Select available slot</option>
                    @if(isset($availableSlots) && $availableSlots->count() > 0)
                        @foreach($availableSlots as $slot)
                            <option value="{{ $slot->id }}">
                                🏠 {{ $slot->section->name ?? 'Unknown Section' }} - Slot {{ $slot->slot_number }}
                                (Capacity: {{ $slot->capacity }})
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>No available slots</option>
                    @endif
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                <i class="fas fa-info-circle text-purple-600"></i>
                <span>Only available slots are shown</span>
            </p>
        </div>

        <!-- Animal Images -->
        <div>
            <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                <i class="fas fa-camera text-purple-600"></i>
                Animal Photos <span class="text-red-600">*</span>
            </label>

            <div class="file-upload-wrapper">
                <label for="animalImages" class="border-2 border-dashed border-purple-300 rounded-xl p-6 text-center bg-purple-50 hover:bg-purple-100 transition cursor-pointer block">
                    <i class="fas fa-cloud-upload-alt text-4xl text-purple-600 mb-2 block"></i>
                    <p class="text-gray-700 font-semibold mb-1">Click to upload images</p>
                    <p class="text-sm text-gray-500">or drag and drop</p>
                    <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF up to 10MB each (Min: 1 image)</p>
                </label>
                <input type="file"
                       id="animalImages"
                       multiple
                       accept="image/*"
                       class="hidden"
                       required
                       onchange="handleAnimalImagePreview(event)">
            </div>

            <p class="text-sm text-gray-600 mt-3 flex items-center gap-2">
                <i class="fas fa-info-circle text-purple-600"></i>
                <span>You can select multiple images at once (Ctrl/Cmd + Click)</span>
            </p>

            <!-- Image Preview -->
            <div id="animalImagePreview" class="mt-4 grid grid-cols-3 gap-3"></div>
        </div>
    </form>
</div>
