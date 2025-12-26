{{-- Edit Animal Modal --}}
<div id="editAnimalModal-{{ $animal->id }}"
     class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-md hidden z-50 flex items-center justify-center p-4"
     style="animation: fadeIn 0.3s ease-out;">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[92vh] overflow-hidden"
         style="animation: slideUp 0.4s ease-out;">

        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 text-white p-7 sticky top-0 z-10 shadow-xl">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-3 rounded-xl backdrop-blur">
                        <span class="text-3xl">🐾</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight">Edit Animal Profile</h2>
                        <p class="text-purple-100 text-sm mt-1">Update information for <span class="font-semibold">{{ $animal->name }}</span></p>
                    </div>
                </div>
                <button onclick="closeEditModal({{ $animal->id }})"
                        class="group bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all duration-300 hover:rotate-90">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px) scale(0.95); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .image-preview-container {
                position: relative;
                overflow: hidden;
            }
            .image-preview {
                transition: transform 0.3s ease;
            }
            .image-preview:hover {
                transform: scale(1.05);
            }
            .delete-badge {
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
        </style>

        {{-- Modal Body --}}
        <div class="p-8 overflow-y-auto max-h-[calc(92vh-120px)] bg-gradient-to-b from-white to-gray-50">
            <form action="{{ route('animal-management.update', $animal->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="editAnimalForm">
                @csrf
                @method('PUT')

                {{-- Name & Weight Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Name --}}
                    <div class="group">
                        <label class="block text-gray-700 font-bold mb-2 flex items-center gap-2">
                            <i class="fas fa-paw text-purple-600"></i>
                            Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="name" value="{{ old('name', $animal->name ?? '') }}"
                                   class="w-full border-2 border-gray-300 rounded-xl shadow-sm px-4 py-3.5 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all text-gray-900 bg-white font-medium hover:border-purple-300"
                                   placeholder="Enter animal's name" required>
                        </div>
                        @error('name')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Weight --}}
                    <div class="group">
                        <label class="block text-gray-700 font-bold mb-2 flex items-center gap-2">
                            <i class="fas fa-weight text-purple-600"></i>
                            Weight (kg) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="weight" min="0" step="0.1" value="{{ old('weight', $animal->weight ?? '') }}"
                                   class="w-full border-2 border-gray-300 rounded-xl shadow-sm px-4 py-3.5 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all text-gray-900 bg-white font-medium hover:border-purple-300"
                                   placeholder="0.0" required>
                        </div>
                        @error('weight')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                {{-- Species --}}
                <div class="group">
                    <label class="block text-gray-700 font-bold mb-2 flex items-center gap-2">
                        <i class="fas fa-cat text-purple-600"></i>
                        Species <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="species" required
                                class="w-full border-2 border-gray-300 rounded-xl shadow-sm px-4 py-3.5 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all text-gray-900 bg-white font-medium appearance-none cursor-pointer hover:border-purple-300">
                            <option value="" disabled>-- Select Species --</option>
                            <option value="Dog" {{ old('species', $animal->species) == 'Dog' ? 'selected' : '' }}>🐕 Dog</option>
                            <option value="Cat" {{ old('species', $animal->species) == 'Cat' ? 'selected' : '' }}>🐈 Cat</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-purple-600">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    @error('species')
                    <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Age & Gender Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Age Category --}}
                    <div class="group">
                        <label class="block text-gray-700 font-bold mb-2 flex items-center gap-2">
                            <i class="fas fa-birthday-cake text-purple-600"></i>
                            Age Category <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            @php
                                $existingAge = $animal->age ?? '';
                                $ageCategory = '';

                                if (str_contains(strtolower($existingAge), 'kitten')) $ageCategory = 'kitten';
                                elseif (str_contains(strtolower($existingAge), 'puppy')) $ageCategory = 'puppy';
                                elseif (str_contains(strtolower($existingAge), 'senior')) $ageCategory = 'senior';
                                elseif (!empty($existingAge)) $ageCategory = 'adult';
                            @endphp

                            <select name="age_category"
                                    class="w-full border-2 border-gray-300 rounded-xl shadow-sm px-4 py-3.5 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all appearance-none cursor-pointer bg-white text-gray-900 font-medium hover:border-purple-300"
                                    required>
                                <option value="" disabled {{ old('age_category', $ageCategory) ? '' : 'selected' }}>Select age category</option>
                                <option value="kitten" {{ old('age_category', $ageCategory) == 'kitten' ? 'selected' : '' }}>🐱 Kitten</option>
                                <option value="puppy" {{ old('age_category', $ageCategory) == 'puppy' ? 'selected' : '' }}>🐶 Puppy</option>
                                <option value="adult" {{ old('age_category', $ageCategory) == 'adult' ? 'selected' : '' }}>✨ Adult</option>
                                <option value="senior" {{ old('age_category', $ageCategory) == 'senior' ? 'selected' : '' }}>👴 Senior</option>
                            </select>

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-purple-600">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        @error('age_category')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div class="group">
                        <label class="block text-gray-700 font-bold mb-2 flex items-center gap-2">
                            <i class="fas fa-venus-mars text-purple-600"></i>
                            Gender <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <select name="gender"
                                    class="w-full border-2 border-gray-300 rounded-xl shadow-sm px-4 py-3.5 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all appearance-none cursor-pointer bg-white text-gray-900 font-medium hover:border-purple-300"
                                    required>
                                <option value="" disabled>Select gender</option>
                                <option value="Male" {{ old('gender', $animal->gender) == 'Male' ? 'selected' : '' }}>♂️ Male</option>
                                <option value="Female" {{ old('gender', $animal->gender) == 'Female' ? 'selected' : '' }}>♀️ Female</option>
                                <option value="Unknown" {{ old('gender', $animal->gender) == 'Unknown' ? 'selected' : '' }}>❓ Unknown</option>
                            </select>

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-purple-600">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        @error('gender')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                {{-- Health Details --}}
                <div class="group">
                    <label class="block text-gray-700 font-bold mb-2 flex items-center gap-2">
                        <i class="fas fa-heartbeat text-purple-600"></i>
                        Health Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="health_details" required
                                class="w-full border-2 border-gray-300 rounded-xl shadow-sm px-4 py-3.5 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all text-gray-900 bg-white font-medium appearance-none cursor-pointer hover:border-purple-300">
                            <option value="" disabled {{ old('health_details', $animal->health_details) ? '' : 'selected' }}>-- Select Health Status --</option>
                            <option value="Healthy" {{ old('health_details', $animal->health_details) == 'Healthy' ? 'selected' : '' }}>
                                ✅ Healthy
                            </option>
                            <option value="Sick" {{ old('health_details', $animal->health_details) == 'Sick' ? 'selected' : '' }}>
                                🤒 Sick
                            </option>
                            <option value="Need Observation" {{ old('health_details', $animal->health_details) == 'Need Observation' ? 'selected' : '' }}>
                                👁️ Need Observation
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-purple-600">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i>
                        Select the current health status
                    </p>
                    @error('health_details')
                    <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Current Images --}}
                @if($animal->images && count($animal->images) > 0)
                    <div class="space-y-4">
                        <label class="block text-gray-700 font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-images text-purple-600"></i>
                            Current Images
                            <span class="text-xs text-gray-500 font-normal">(Select images to delete)</span>
                        </label>

                        @php
                            $firstImage = $animal->images->first();
                            $remainingImages = $animal->images->slice(1);
                        @endphp

                        {{-- Primary Image --}}
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-300 rounded-2xl p-5 shadow-lg">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="bg-purple-600 p-1.5 rounded-lg">
                                    <i class="fas fa-star text-white text-sm"></i>
                                </div>
                                <h3 class="font-bold text-gray-800">Primary Image</h3>
                                <span class="text-xs bg-purple-200 text-purple-800 px-2 py-1 rounded-full font-semibold">Main</span>
                            </div>
                            <div class="relative image-preview-container rounded-xl overflow-hidden shadow-xl group">
                                <img src="{{ $firstImage->url }}"
                                     alt="Primary image for {{ $animal->name }}"
                                     class="w-full h-72 object-cover image-preview">

                                {{-- Delete Option --}}
                                <div class="absolute top-3 right-3">
                                    <label class="cursor-pointer flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-xl font-bold shadow-lg transition-all delete-badge hover:scale-105">
                                        <input type="checkbox" name="delete_images[]" value="{{ $firstImage->id }}"
                                               class="w-4 h-4 rounded border-2 border-white">
                                        <i class="fas fa-trash"></i>
                                        <span class="text-sm">Delete</span>
                                    </label>
                                </div>

                                {{-- Hover Overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                                    <p class="text-white text-sm font-semibold">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        This is the main display image
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Images Grid --}}
                        @if(count($remainingImages) > 0)
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="bg-gray-600 p-1.5 rounded-lg">
                                        <i class="fas fa-th text-white text-sm"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Additional Images</h3>
                                    <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-full font-semibold">{{ count($remainingImages) }} photos</span>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($remainingImages as $image)
                                        <div class="relative group image-preview-container rounded-xl overflow-hidden shadow-lg border-2 border-gray-200 hover:border-purple-400 transition-all">
                                            <img src="{{ $image->url }}"
                                                 alt="Animal image"
                                                 class="w-full h-36 object-cover image-preview">

                                            {{-- Delete Overlay --}}
                                            <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                                <label class="cursor-pointer flex flex-col items-center gap-2 text-white">
                                                    <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"
                                                           class="w-5 h-5 rounded border-2 border-white"
                                                           onchange="this.parentElement.classList.toggle('selected', this.checked)">
                                                    <div class="flex items-center gap-1 bg-red-600 px-3 py-1.5 rounded-lg font-bold text-sm shadow-lg">
                                                        <i class="fas fa-trash"></i>
                                                        <span>Delete</span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Upload New Images --}}
                <div class="group">
                    <label class="block text-gray-700 font-bold mb-3 flex items-center gap-2">
                        <i class="fas fa-cloud-upload-alt text-purple-600"></i>
                        Upload New Images
                    </label>

                    {{-- File Input with Custom Styling --}}
                    <div class="relative">
                        <input type="file" name="images[]" multiple accept="image/*" id="newImageInput"
                               class="hidden"
                               onchange="previewNewImages(event)">
                        <label for="newImageInput"
                               class="flex flex-col items-center justify-center w-full h-40 border-3 border-dashed border-purple-300 rounded-2xl cursor-pointer bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 transition-all hover:border-purple-500 hover:scale-[1.02] shadow-lg">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                                <div class="bg-purple-200 p-4 rounded-full mb-3">
                                    <i class="fas fa-images text-purple-600 text-3xl"></i>
                                </div>
                                <p class="mb-2 text-sm text-gray-700 font-bold">
                                    <span class="text-purple-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG (MAX. 5MB each)</p>
                                <p class="text-xs text-purple-600 font-semibold mt-1">
                                    <i class="fas fa-info-circle"></i>
                                    Hold Ctrl/Cmd to select multiple files
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Image Preview Container --}}
                    <div id="newImagePreviewContainer" class="hidden mt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-eye text-purple-600"></i>
                                New Images Preview
                            </p>
                            <button type="button" onclick="clearNewImages()"
                                    class="text-xs text-red-600 hover:text-red-800 font-semibold flex items-center gap-1 hover:scale-105 transition-transform">
                                <i class="fas fa-times-circle"></i>
                                Clear All
                            </button>
                        </div>
                        <div id="newImagePreviewGrid" class="grid grid-cols-2 md:grid-cols-4 gap-3"></div>
                    </div>

                    @error('images')
                    <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                    @error('images.*')
                    <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-6 border-t-2 border-gray-200 mt-6">
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-purple-600"></i>
                        Fields marked with <span class="text-red-500 font-bold">*</span> are required
                    </p>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <button type="button" onclick="closeEditModal({{ $animal->id }})"
                                class="flex-1 sm:flex-none group px-6 py-3.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-times group-hover:rotate-90 transition-transform"></i>
                            <span>Cancel</span>
                        </button>

                        <button type="submit" id="submitButton"
                                class="flex-1 sm:flex-none group px-8 py-3.5 bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 hover:from-purple-700 hover:via-purple-800 hover:to-purple-900 text-white font-bold rounded-xl transition-all duration-300 shadow-xl hover:shadow-2xl hover:scale-105 flex items-center justify-center gap-2">
                            <i class="fas fa-save group-hover:scale-110 transition-transform"></i>
                            <span>Update Animal</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- JavaScript for Image Preview --}}
<script>
    function previewNewImages(event) {
        const files = event.target.files;
        const container = document.getElementById('newImagePreviewContainer');
        const grid = document.getElementById('newImagePreviewGrid');

        if (files.length === 0) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        grid.innerHTML = '';

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative group rounded-xl overflow-hidden shadow-lg border-2 border-purple-300 hover:border-purple-500 transition-all';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-36 object-cover transition-transform group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
                            <p class="text-white text-xs font-semibold truncate">
                                <i class="fas fa-file-image mr-1"></i>
                                ${file.name}
                            </p>
                        </div>
                        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg">
                            <i class="fas fa-check"></i> New
                        </div>
                    `;
                    grid.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function clearNewImages() {
        const input = document.getElementById('newImageInput');
        const container = document.getElementById('newImagePreviewContainer');
        const grid = document.getElementById('newImagePreviewGrid');

        input.value = '';
        grid.innerHTML = '';
        container.classList.add('hidden');
    }

    // Form submission loading state
    document.getElementById('editAnimalForm').addEventListener('submit', function(e) {
        const submitButton = document.getElementById('submitButton');
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Updating...</span>
        `;
    });
</script>

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

    // Close modal when clicking outside the white dialog
    document.addEventListener('click', function(event) {
        if (event.target.id && event.target.id.startsWith('editAnimalModal-')) {
            closeEditModal(event.target.id.split('-')[1]);
        }
    });
</script>
