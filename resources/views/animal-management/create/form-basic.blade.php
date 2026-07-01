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
