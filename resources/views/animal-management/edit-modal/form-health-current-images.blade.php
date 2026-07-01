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
                @if($animal->relationLoaded('images') && $animal->images->count() > 0)
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
