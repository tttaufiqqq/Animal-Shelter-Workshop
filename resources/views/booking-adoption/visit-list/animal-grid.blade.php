                <form method="POST" action="{{ route('visit.list.confirm') }}" class="space-y-6" id="visitListForm" onsubmit="handleVisitSubmit(event)">
                    @csrf

                    <!-- Animals Counter -->
                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-center gap-3">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ $animalList->count() }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $animalList->count() }} {{ Str::plural('Animal', $animalList->count()) }} Selected
                            </p>
                            <p class="text-sm text-gray-600">Ready for your visit appointment</p>
                        </div>
                    </div>

                    <!-- Selected Animals Grid -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-paw text-purple-600"></i>
                            Animals You'll Visit
                        </h2>

                        <div class="grid grid-cols-1 gap-4">
                            @foreach($animalList as $index => $animal)
                                <div class="group bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 hover:border-purple-300 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg">
                                    <div class="flex gap-4">
                                        <!-- Animal Image -->
                                        <div class="flex-shrink-0">
                                            <div class="w-24 h-24 rounded-xl overflow-hidden bg-gray-200 ring-4 ring-purple-100 group-hover:ring-purple-300 transition-all duration-300">
                                                @if($animal->images && $animal->images->count() > 0)
                                                    <img src="{{ $animal->images->first()->url }}"
                                                         alt="{{ $animal->name }}"
                                                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="fas fa-paw text-gray-400 text-3xl"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Animal Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                                        {{ $animal->name }}
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            #{{ $index + 1 }}
                                                        </span>
                                                    </h3>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm">
                                                            <i class="fas fa-dog"></i>
                                                            {{ $animal->species }}
                                                        </span>
                                                        @if($animal->breed)
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm">
                                                                <i class="fas fa-dna"></i>
                                                                {{ $animal->breed }}
                                                            </span>
                                                        @endif
                                                        @if($animal->age)
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-50 text-orange-700 rounded-full text-sm">
                                                                <i class="fas fa-calendar"></i>
                                                                {{ $animal->age }}
                                                            </span>
                                                        @endif
                                                        @if($animal->gender)
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-50 text-pink-700 rounded-full text-sm">
                                                                <i class="fas fa-{{ $animal->gender == 'Male' ? 'mars' : 'venus' }}"></i>
                                                                {{ $animal->gender }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Remove Button (No Form) -->
                                                <button type="button"
                                                        onclick="openRemoveConfirmModal({{ $animal->id }}, '{{ $animal->name }}')"
                                                        class="text-red-500 hover:text-white hover:bg-red-500 p-2.5 rounded-lg transition-all duration-200 flex items-center gap-2 group/btn border border-red-200 hover:border-red-500">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <span class="text-sm font-medium hidden sm:inline">Remove</span>
                                                </button>
                                            </div>

                                            <!-- Remarks Input -->
                                            <div class="mt-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                    <i class="fas fa-comment-dots text-purple-600 mr-1"></i>
                                                    Why are you interested in {{ $animal->name }}?
                                                </label>
                                                <textarea name="remarks[{{ $animal->id }}]"
                                                          placeholder="Tell us what makes {{ $animal->name }} special to you..."
                                                          class="w-full border-2 border-gray-200 focus:border-purple-400 focus:ring-2 focus:ring-purple-200 text-gray-700 rounded-lg p-3 text-sm transition-all duration-200 resize-none"
                                                          rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden input for booking -->
                                    <input type="hidden" name="animal_ids[]" value="{{ $animal->id }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
