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
