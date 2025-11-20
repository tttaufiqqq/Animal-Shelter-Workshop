<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Animal - Stray Animals Shelter</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-white min-h-screen flex flex-col">
   
    <!-- Include Navbar -->
    @include('navbar')

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center p-4 py-8">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-8">
                <div class="flex items-center mb-2">
                    <span class="text-4xl mr-4">🐾</span>
                    <h2 class="text-3xl font-bold">Add New Animal</h2>
                </div>
                <p class="text-purple-100 text-lg">
                    Register a new animal to the shelter system from rescue: {{$rescue_id}}
                </p>
            </div>

            <!-- Form Section -->
            <div class="p-8 md:p-12">
                @if (session('success'))
                    <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-6">
                        <p class="font-semibold">{{ session('success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-lg mb-6">
                        <p class="font-semibold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('animal-management.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                            class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" 
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
                        <input type="number" name="weight" min="0" step="0.1" value="{{ old('weight') }}" 
                            class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" 
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
                            class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition">
                            
                            <option value="" disabled selected>-- Select Species --</option>
                            <option value="Dog" {{ old('species') == 'Dog' ? 'selected' : '' }}>Dog</option>
                            <option value="Cat" {{ old('species') == 'Cat' ? 'selected' : '' }}>Cat</option>
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
                                <select name="age_category"
                                        class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition appearance-none cursor-pointer bg-white pr-10"
                                        required>
                                    <option value="" disabled selected>Select age category</option>
                                    <option value="kitten" {{ old('age_category') == 'kitten' ? 'selected' : '' }}>Kitten</option>
                                    <option value="puppy" {{ old('age_category') == 'puppy' ? 'selected' : '' }}>Puppy</option>
                                    <option value="adult" {{ old('age_category') == 'adult' ? 'selected' : '' }}>Adult</option>
                                    <option value="senior" {{ old('age_category') == 'senior' ? 'selected' : '' }}>Senior</option>
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
                                        class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition appearance-none cursor-pointer bg-white pr-10" required>
                                    <option value="" disabled selected>Select gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Unknown" {{ old('gender') == 'Unknown' ? 'selected' : '' }}>Unknown</option>
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
                                  class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" 
                                  placeholder="Describe the animal's health condition, any injuries, etc..." required>{{ old('health_details') }}</textarea>
                        @error('health_details')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rescue ID (Hidden) --}}
                    <input type="hidden" name="rescueID" value="{{ $rescue_id }}">

                    {{-- Slot --}}
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            Slot
                        </label>
                        <div class="relative">
                            <select name="slotID"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition appearance-none cursor-pointer bg-white pr-10">
                                <option value="" selected>No slot selected</option>
                                @forelse($slots as $slot)
                                    <option value="{{ $slot->id }}" {{ old('slotID') == $slot->id ? 'selected' : '' }}>
                                        Slot {{ $slot->name ?? $slot->id }} - {{ $slot->section ?? 'N/A' }}
                                    </option>
                                @empty
                                    <option value="" disabled>No available slots</option>
                                @endforelse
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        @error('slotID')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-600 mt-2">
                            <i class="fas fa-info-circle"></i> Only available slots are shown
                        </p>
                    </div>

                    {{-- Animal Images --}}
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            Upload Images <span class="text-red-600">*</span>
                        </label>
                        <input 
                            type="file" 
                            name="images[]" 
                            multiple 
                            accept="image/*"
                            class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition"
                            required
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
                    <div class="flex justify-end pt-4">
                        <button type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                            <i class="fas fa-plus mr-2"></i>Add Animal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('[required]');

            // Remove red border on input
            requiredFields.forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-gray-300');
                    }
                });
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let allValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        allValid = false;
                        field.classList.add('border-red-500');
                        field.classList.remove('border-gray-300');
                    } else {
                        field.classList.remove('border-red-500');
                        field.classList.add('border-gray-300');
                    }
                });

                if (!allValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>