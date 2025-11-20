{{-- Edit Animal Modal --}}
<div id="editAnimalModal-{{ $animal->id }}" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        
        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 sticky top-0 z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <span class="text-3xl mr-3">🐾</span>
                    <h2 class="text-2xl font-bold">Edit Animal</h2>
                </div>
                <button onclick="closeEditModal({{ $animal->id }})" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <p class="text-purple-100 mt-2">Update information for {{ $animal->name }}</p>
        </div>

        {{-- Modal Body --}}
        <div class="p-8">
            <form action="{{ route('animal-management.update', $animal->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $animal->name ?? '') }}" 
                           class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-purple-500 focus:ring focus:ring-purple-200 transition text-gray-900 bg-white" 
                           placeholder="Give name to the rescued animal" required>
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Weight --}}
               <div>
                  <label class="block text-gray-800 font-semibold mb-2">
                     Weight (kg) <span class="text-red-600">*</span>
                  </label>
                  <input type="number" name="weight" min="0" step="0.1" value="{{ old('weight', $animal->weight ?? '') }}" 
                        class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-purple-500 focus:ring focus:ring-purple-200 transition text-gray-900 bg-white" 
                        placeholder="Enter animal weight in kg" required>
                  @error('weight')
                     <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                  @enderror
               </div>

                {{-- Species --}}
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Species <span class="text-red-600">*</span>
                    </label>
                    <select name="species" required
                        class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-purple-500 focus:ring focus:ring-purple-200 transition text-gray-900 bg-white">
                        <option value="" disabled>-- Select Species --</option>
                        <option value="Dog" {{ old('species', $animal->species) == 'Dog' ? 'selected' : '' }}>Dog</option>
                        <option value="Cat" {{ old('species', $animal->species) == 'Cat' ? 'selected' : '' }}>Cat</option>
                    </select>
                    @error('species')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Age and Gender in Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Age Category --}}
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            Age Category <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            @php
                                // Map existing age to category for pre-selection
                                $existingAge = $animal->age ?? '';
                                $ageCategory = '';
                                
                                if (str_contains(strtolower($existingAge), 'kitten')) {
                                    $ageCategory = 'kitten';
                                } elseif (str_contains(strtolower($existingAge), 'puppy')) {
                                    $ageCategory = 'puppy';
                                } elseif (str_contains(strtolower($existingAge), 'senior')) {
                                    $ageCategory = 'senior';
                                } elseif (!empty($existingAge)) {
                                    $ageCategory = 'adult';
                                }
                            @endphp
                            
                            <select name="age_category"
                                    class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-purple-500 focus:ring focus:ring-purple-200 transition appearance-none cursor-pointer bg-white pr-10 text-gray-900"
                                    required>
                                <option value="" disabled {{ old('age_category', $ageCategory) ? '' : 'selected' }}>Select age category</option>
                                <option value="kitten" {{ old('age_category', $ageCategory) == 'kitten' ? 'selected' : '' }}>Kitten</option>
                                <option value="puppy" {{ old('age_category', $ageCategory) == 'puppy' ? 'selected' : '' }}>Puppy</option>
                                <option value="adult" {{ old('age_category', $ageCategory) == 'adult' ? 'selected' : '' }}>Adult</option>
                                <option value="senior" {{ old('age_category', $ageCategory) == 'senior' ? 'selected' : '' }}>Senior</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        @error('age_category')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            Gender <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select name="gender" 
                                    class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-purple-500 focus:ring focus:ring-purple-200 transition appearance-none cursor-pointer bg-white pr-10 text-gray-900" required>
                                <option value="" disabled>Select gender</option>
                                <option value="Male" {{ old('gender', $animal->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $animal->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Unknown" {{ old('gender', $animal->gender) == 'Unknown' ? 'selected' : '' }}>Unknown</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        @error('gender')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Health Details --}}
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Health Details <span class="text-red-600">*</span>
                    </label>
                    <textarea name="health_details" rows="4" 
                              class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-purple-500 focus:ring focus:ring-purple-200 transition text-gray-900 bg-white" 
                              placeholder="Describe the animal's health condition, any injuries, etc..." required>{{ old('health_details', $animal->health_details) }}</textarea>
                    @error('health_details')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                

                {{-- Current Images --}}
                @if($animal->images && count($animal->images) > 0)
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Current Images
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        @foreach($animal->images as $image)
                            <div class="relative group">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="Animal image" class="w-full h-32 object-cover rounded-lg shadow">
                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="mr-2">
                                        <span class="text-white text-sm">Delete</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Upload New Images --}}
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Upload New Images
                    </label>
                    <input 
                        type="file" 
                        name="images[]" 
                        multiple 
                        accept="image/*"
                        class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-purple-500 focus:ring focus:ring-purple-200 transition text-gray-900 bg-white"
                    >
                    <p class="text-sm text-gray-600 mt-2">You can upload multiple images (hold Ctrl/Cmd to select multiple files)</p>
                    @error('images')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditModal({{ $animal->id }})"
                            class="px-6 py-3 bg-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-400 transition duration-300">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                        <i class="fas fa-save mr-2"></i>Update Animal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(animalId) {
    const modal = document.getElementById('editAnimalModal-' + animalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeEditModal(animalId) {
    const modal = document.getElementById('editAnimalModal-' + animalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.id && event.target.id.startsWith('editAnimalModal-')) {
        closeEditModal(event.target.id.split('-')[1]);
    }
});
</script>