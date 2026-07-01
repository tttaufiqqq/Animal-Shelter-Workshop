            <!-- Section 2: Health & Location -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b-2 border-purple-200">
                    <div class="bg-purple-100 text-purple-700 w-10 h-10 rounded-full flex items-center justify-center font-bold">
                        2
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Health & Location</h2>
                </div>

                <!-- Health Details -->
                <div>
                    <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                        <i class="fas fa-heartbeat text-purple-600"></i>
                        Health Condition <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <select name="health_details"
                                class="w-full border-2 border-gray-200 rounded-xl shadow-sm px-4 py-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition appearance-none cursor-pointer bg-white"
                                required>
                            <option value="" disabled {{ !old('health_details') ? 'selected' : '' }}>Select condition</option>
                            <option value="Healthy" {{ old('health_details') == 'Healthy' ? 'selected' : '' }}>✅ Healthy</option>
                            <option value="Sick" {{ old('health_details') == 'Sick' ? 'selected' : '' }}>🤒 Sick</option>
                            <option value="Need Observation" {{ old('health_details') == 'Need Observation' ? 'selected' : '' }}>👁️ Need Observation</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    @error('health_details')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Slot Assignment -->
                <div>
                    <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                        <i class="fas fa-map-marker-alt text-purple-600"></i>
                        Assign Shelter Slot
                        <span class="text-gray-500 text-sm font-normal">(Optional)</span>
                    </label>
                    <div class="relative">
                        <select name="slotID"
                                class="w-full border-2 border-gray-200 rounded-xl shadow-sm px-4 py-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition appearance-none cursor-pointer bg-white">
                            <option value="" selected>No slot assigned</option>
                            @forelse($slots as $slot)
                                <option value="{{ $slot->id }}" {{ old('slotID') == $slot->id ? 'selected' : '' }}>
                                    📍 Slot {{ $slot->name ?? $slot->id }} - {{ $slot->section->name ?? 'No Section' }}
                                </option>
                            @empty
                                <option value="" disabled>No available slots</option>
                            @endforelse
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i>
                        Only available slots are shown. You can assign later if needed.
                    </p>
                    @error('slotID')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Section 3: Photos -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b-2 border-purple-200">
                    <div class="bg-purple-100 text-purple-700 w-10 h-10 rounded-full flex items-center justify-center font-bold">
                        3
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Animal Photos</h2>
                </div>

                <!-- File Upload -->
                <div>
                    <div class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                        <i class="fas fa-camera text-purple-600"></i>
                        Upload Images <span class="text-red-600">*</span>
                    </div>

                    <div class="file-upload-wrapper">
                        <label for="imageInput" class="border-2 border-dashed border-purple-300 rounded-xl p-8 text-center bg-purple-50 hover:bg-purple-100 transition cursor-pointer block">
                            <i class="fas fa-cloud-upload-alt text-5xl text-purple-600 mb-3 block"></i>
                            <p class="text-gray-700 font-semibold mb-1">Click to upload images</p>
                            <p class="text-sm text-gray-500">or drag and drop</p>
                            <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF up to 10MB each</p>
                        </label>
                        <input type="file"
                               name="images[]"
                               id="imageInput"
                               multiple
                               accept="image/*"
                               class="hidden"
                               required>
                    </div>

                    <p class="text-sm text-gray-600 mt-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-purple-600"></i>
                        <span>You can select multiple images at once (Ctrl/Cmd + Click)</span>
                    </p>

                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3"></div>

                    @error('images')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t-2 border-gray-100">
                <a href="{{ route('animal-management.index') }}"
                   class="flex-1 sm:flex-none px-8 py-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition text-center">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit"
                        id="submitBtn"
                        class="flex-1 px-8 py-4 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-check mr-2"></i>
                    <span id="submitText">Add Animal to Shelter</span>
                    <i class="fas fa-spinner fa-spin ml-2 hidden" id="submitSpinner"></i>
                </button>
            </div>
        </form>
    </div>
</div>
