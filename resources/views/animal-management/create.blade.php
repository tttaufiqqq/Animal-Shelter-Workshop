<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Animal - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Simple fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }

        /* Image preview styles */
        .image-preview {
            position: relative;
            transition: transform 0.2s;
        }

        .image-preview:hover {
            transform: scale(1.05);
        }

        /* Progress bar */
        .progress-step {
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-50 min-h-screen">

@include('navbar')

<div class="container mx-auto px-4 py-8 max-w-4xl">

    <!-- Back Button -->
    <div class="mb-6 fade-in">
        <a href="{{ route('animal-management.index') }}"
           class="inline-flex items-center gap-2 text-purple-700 hover:text-purple-900 font-semibold transition">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Animals</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden fade-in">

        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 text-white p-8">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-white bg-opacity-20 p-3 rounded-2xl backdrop-blur-sm">
                    <i class="fas fa-paw text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Add New Animal</h1>
                    <p class="text-purple-100 text-sm mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Register rescued animal to the shelter
                    </p>
                </div>
            </div>

            @if($rescue_id)
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4 mt-4">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="fas fa-ambulance text-xl"></i>
                        <div>
                            <span class="font-semibold">Rescue ID:</span>
                            <span class="font-bold text-lg ml-2">#{{ $rescue_id }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Messages -->
        @if (session('success'))
            <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm mx-6 mt-6">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="font-semibold text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="m-6 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl shadow-md fade-in">
                <div class="flex items-start gap-3">
                    <div class="bg-red-500 text-white p-2 rounded-full">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <p class="font-bold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('animal-management.store') }}"
              method="POST"
              enctype="multipart/form-data"
              id="animalForm"
              class="p-8 space-y-8">
            @csrf

            <!-- Hidden Rescue ID -->
            <input type="hidden" name="rescueID" value="{{ $rescue_id }}">

            <!-- Section 1: Basic Information -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b-2 border-purple-200">
                    <div class="bg-purple-100 text-purple-700 w-10 h-10 rounded-full flex items-center justify-center font-bold">
                        1
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Basic Information</h2>
                </div>

                <!-- Name -->
                <div>
                    <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                        <i class="fas fa-signature text-purple-600"></i>
                        Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="w-full border-2 border-gray-200 rounded-xl shadow-sm px-4 py-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition"
                           placeholder="e.g., Buddy, Whiskers, Max..."
                           required>
                    @error('name')
                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Weight & Species Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Weight -->
                    <div>
                        <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                            <i class="fas fa-weight text-purple-600"></i>
                            Weight (kg) <span class="text-red-600">*</span>
                        </label>
                        <input type="number"
                               name="weight"
                               min="0"
                               step="0.1"
                               value="{{ old('weight') }}"
                               class="w-full border-2 border-gray-200 rounded-xl shadow-sm px-4 py-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition"
                               placeholder="0.0"
                               required>
                        @error('weight')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Species -->
                    <div>
                        <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                            <i class="fas fa-paw text-purple-600"></i>
                            Species <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select name="species"
                                    id="species"
                                    class="w-full border-2 border-gray-200 rounded-xl shadow-sm px-4 py-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition appearance-none cursor-pointer bg-white"
                                    required>
                                <option value="" disabled {{ !old('species') ? 'selected' : '' }}>Select Species</option>
                                <option value="Dog" {{ old('species') == 'Dog' ? 'selected' : '' }}>🐕 Dog</option>
                                <option value="Cat" {{ old('species') == 'Cat' ? 'selected' : '' }}>🐈 Cat</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
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
                </div>

                <!-- Age & Gender Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Age Category -->
                    <div>
                        <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                            <i class="fas fa-birthday-cake text-purple-600"></i>
                            Age Category <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select name="age_category"
                                    id="age_category"
                                    class="w-full border-2 border-gray-200 rounded-xl shadow-sm px-4 py-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition appearance-none cursor-pointer bg-white"
                                    required>
                                <option value="" disabled selected>Select age category</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle"></i>
                            Options change based on species
                        </p>
                        @error('age_category')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                            <i class="fas fa-venus-mars text-purple-600"></i>
                            Gender <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select name="gender"
                                    class="w-full border-2 border-gray-200 rounded-xl shadow-sm px-4 py-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition appearance-none cursor-pointer bg-white"
                                    required>
                                <option value="" disabled selected>Select gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>♂️ Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>♀️ Female</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
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
            </div>

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

<script>
// Dynamic Age Categories
document.addEventListener('DOMContentLoaded', function () {
    const speciesSelect = document.getElementById('species');
    const ageSelect = document.getElementById('age_category');

    function updateAgeCategories() {
        const species = speciesSelect.value;
        ageSelect.innerHTML = '<option value="" disabled selected>Select age category</option>';

        let ageOptions = [];
        if (species === 'Cat') {
            ageOptions = ["kitten", "adult", "senior"];
        } else if (species === 'Dog') {
            ageOptions = ["puppy", "adult", "senior"];
        }

        ageOptions.forEach(age => {
            let option = document.createElement("option");
            option.value = age;
            option.textContent = age.charAt(0).toUpperCase() + age.slice(1);
            ageSelect.appendChild(option);
        });
    }

    speciesSelect.addEventListener('change', updateAgeCategories);
    updateAgeCategories();

    // Image Preview
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function() {
        imagePreview.innerHTML = '';
        const files = Array.from(this.files);

        if (files.length > 0) {
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview relative group';
                    div.innerHTML = `
                        <img src="${e.target.result}"
                             class="w-full h-24 object-cover rounded-xl border-2 border-purple-200 shadow-md"
                             alt="Preview ${index + 1}">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition rounded-xl flex items-center justify-center">
                            <span class="text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition">
                                Image ${index + 1}
                            </span>
                        </div>
                    `;
                    imagePreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // Form Submission
    const form = document.getElementById('animalForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    form.addEventListener('submit', function(e) {
        // Validation
        const requiredFields = form.querySelectorAll('[required]');
        let allValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                allValid = false;
                field.classList.add('border-red-500', 'border-2');
                field.classList.remove('border-gray-200');
            } else {
                field.classList.remove('border-red-500');
                field.classList.add('border-gray-200');
            }
        });

        if (!allValid) {
            e.preventDefault();

            // Scroll to first error
            const firstError = form.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }

            // Show alert
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-xl shadow-2xl z-50 fade-in';
            alertDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Please fill in all required fields';
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);

            return false;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Adding Animal...';
        submitSpinner.classList.remove('hidden');
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    });

    // Remove error styling on input
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-200');
            }
        });
    });
});
</script>

</body>
</html>
