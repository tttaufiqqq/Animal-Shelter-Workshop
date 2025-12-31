{{-- Step 2: Select Animals to Adopt --}}
<div class="space-y-5">

    @if($animals->isNotEmpty())
        {{-- Instructions --}}
        <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
            <p class="text-sm text-gray-700">
                Select the animals you'd like to adopt. Fees include medical care and vaccinations.
            </p>
        </div>

        {{-- Animal Selection Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($animals as $index => $animal)
                @php
                    $breakdown = $allFeeBreakdowns[$animal->id] ?? null;
                @endphp

                <label class="cursor-pointer group block">
                    <div class="bg-white rounded-xl p-4 border border-gray-200 group-hover:border-purple-300 transition-all relative
                        has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">

                        {{-- Checkbox --}}
                        <input type="checkbox"
                               id="selectAnimal-{{ $booking->id }}-{{ $animal->id }}"
                               class="animal-select-{{ $booking->id }} absolute top-4 right-4 w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500 cursor-pointer"
                               data-animal-id="{{ $animal->id }}"
                               data-animal-name="{{ $animal->name }}"
                               data-animal-species="{{ $animal->species }}"
                               data-fee="{{ $breakdown['total_fee'] ?? 0 }}"
                               data-base-fee="{{ $breakdown['base_fee'] ?? 0 }}"
                               data-medical-fee="{{ $breakdown['medical_fee'] ?? 0 }}"
                               data-vaccination-fee="{{ $breakdown['vaccination_fee'] ?? 0 }}"
                               data-medical-count="{{ $breakdown['medical_count'] ?? 0 }}"
                               data-vaccination-count="{{ $breakdown['vaccination_count'] ?? 0 }}"
                               {{ $index === 0 ? 'checked' : '' }}>

                        {{-- Selected Badge --}}
                        <div class="selected-badge hidden absolute top-3 right-3 bg-purple-600 text-white rounded-full p-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>

                        {{-- Animal Image --}}
                        @if($animal->images && $animal->images->count() > 0)
                            <img src="{{ $animal->images->first()->url }}"
                                 alt="{{ $animal->name }}"
                                 class="w-full h-40 object-cover rounded-lg mb-3">
                        @else
                            <div class="w-full h-40 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                                <span class="text-5xl">
                                    @if(strtolower($animal->species) == 'dog') 🐕
                                    @elseif(strtolower($animal->species) == 'cat') 🐈
                                    @else 🐾
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- Animal Info --}}
                        <div class="mb-3">
                            <h4 class="text-lg font-semibold text-gray-900 mb-1">{{ $animal->name }}</h4>
                            <p class="text-sm text-gray-600 mb-2">{{ $animal->species }} • {{ $animal->age }} • {{ $animal->gender }}</p>

                            {{-- Health Records Summary --}}
                            @if($breakdown && ($breakdown['medical_count'] > 0 || $breakdown['vaccination_count'] > 0))
                            <div class="flex gap-1.5 flex-wrap">
                                @if($breakdown['medical_count'] > 0)
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-medium">
                                        {{ $breakdown['medical_count'] }} Medical
                                    </span>
                                @endif
                                @if($breakdown['vaccination_count'] > 0)
                                    <span class="px-2 py-0.5 bg-green-50 text-green-700 rounded text-xs font-medium">
                                        {{ $breakdown['vaccination_count'] }} Vaccine{{ $breakdown['vaccination_count'] > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Fee Breakdown --}}
                        @if($breakdown)
                            <div class="pt-3 border-t border-gray-100 space-y-1.5">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">Base</span>
                                    <span class="font-medium text-gray-900">RM {{ number_format($breakdown['base_fee'], 2) }}</span>
                                </div>
                                @if($breakdown['medical_fee'] > 0)
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Medical</span>
                                        <span class="font-medium text-gray-900">RM {{ number_format($breakdown['medical_fee'], 2) }}</span>
                                    </div>
                                @endif
                                @if($breakdown['vaccination_fee'] > 0)
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Vaccines</span>
                                        <span class="font-medium text-gray-900">RM {{ number_format($breakdown['vaccination_fee'], 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-sm font-semibold pt-1.5 border-t border-gray-100">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-purple-600">RM {{ number_format($breakdown['total_fee'], 2) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Selection Summary --}}
        <div class="bg-purple-600 rounded-xl p-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-purple-200 mb-1">Selected</p>
                    <p class="text-2xl font-bold text-white">
                        <span id="selectedCount-{{ $booking->id }}">1</span>
                        <span class="text-base text-purple-200 font-normal">/ {{ $animals->count() }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-purple-200 mb-1">Total Fee</p>
                    <p class="text-2xl font-bold text-white" id="estimatedFee-{{ $booking->id }}">
                        RM {{ number_format($allFeeBreakdowns[$animals->first()->id]['total_fee'] ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

    @else
        <div class="bg-gray-100 rounded-xl p-8 text-center">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-500 text-lg font-medium">No animals associated with this booking.</p>
        </div>
    @endif

</div>

<style>
    /* Show selected badge when checkbox is checked */
    .animal-select-{{ $booking->id }}:checked ~ .selected-badge {
        display: block !important;
    }

    /* Hide checkbox when selected */
    .animal-select-{{ $booking->id }}:checked {
        display: none;
    }
</style>
