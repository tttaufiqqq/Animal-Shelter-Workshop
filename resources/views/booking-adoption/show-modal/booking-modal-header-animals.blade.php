    <!-- Booking Details Modal -->
<div id="bookingModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-[1400px] w-full max-h-[95vh] overflow-y-auto" onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <h2 class="text-2xl font-bold">Booking Details</h2>
                        <p class="text-purple-100 text-sm">Booking #{{ $booking->id }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('bookingModal-{{ $booking->id }}')" class="text-white hover:text-gray-200 transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">

            <!-- Status Badge -->
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Booking Status</h3>
                @php
                    $statusBadgeColors = [
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'confirmed' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                    $statusKey = strtolower($booking->status);
                @endphp
                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusBadgeColors[$statusKey] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $booking->status }}
                </span>
            </div>

            <!-- Animals Section with Checkboxes -->
            @if($animals->isNotEmpty())
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-300 rounded-xl p-6 shadow-md">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center text-xl">
                        <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Animals in this Booking
                        @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                            <span class="ml-2 text-sm font-normal text-purple-600">(Select animals to adopt)</span>
                        @endif
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($animals as $index => $animal)
                            <label class="cursor-pointer group">
                                <div class="bg-white rounded-xl p-4 shadow-md border-2 border-transparent group-hover:border-purple-500 transition relative
                                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed'])) has-[:checked]:border-purple-500 has-[:checked]:ring-2 has-[:checked]:ring-purple-300 @endif">

                                    {{-- Checkbox for selection (only for pending/confirmed) --}}
                                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                                        <input type="checkbox"
                                               id="selectAnimal-{{ $booking->id }}-{{ $animal->id }}"
                                               class="animal-select-{{ $booking->id }} absolute top-3 right-3 w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                               data-animal-id="{{ $animal->id }}"
                                               data-animal-name="{{ $animal->name }}"
                                               data-animal-species="{{ $animal->species }}"
                                               data-fee="{{ $allFeeBreakdowns[$animal->id]['total_fee'] }}"
                                               data-base-fee="{{ $allFeeBreakdowns[$animal->id]['base_fee'] }}"
                                               data-medical-fee="{{ $allFeeBreakdowns[$animal->id]['medical_fee'] }}"
                                               data-vaccination-fee="{{ $allFeeBreakdowns[$animal->id]['vaccination_fee'] }}"
                                               data-medical-count="{{ $allFeeBreakdowns[$animal->id]['medical_count'] }}"
                                               data-vaccination-count="{{ $allFeeBreakdowns[$animal->id]['vaccination_count'] }}"
                                            {{ $index === 0 ? 'checked' : '' }}>
                                    @endif

                                    @if($animal->images && $animal->images->count() > 0)
                                        <img src="{{ $animal->images->first()->url }}"
                                             alt="{{ $animal->name }}"
                                             class="w-full h-40 object-cover rounded-lg mb-3">
                                    @else
                                        <div class="w-full h-40 bg-gradient-to-br from-purple-300 to-purple-400 rounded-lg flex items-center justify-center mb-3">
                                            <span class="text-5xl">
                                                @if(strtolower($animal->species) == 'dog') 🐕
                                                @elseif(strtolower($animal->species) == 'cat') 🐈
                                                @else 🐾
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    <div class="text-gray-800 font-semibold">{{ $animal->name }}</div>
                                    <div class="text-gray-600 text-sm">{{ $animal->species }} • {{ $animal->age }} • {{ $animal->gender }}</div>

                                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                                        <div class="mt-2 text-sm font-semibold text-purple-700">
                                            Fee: RM {{ number_format($allFeeBreakdowns[$animal->id]['total_fee'], 2) }}
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Selection Summary --}}
                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                        <div class="mt-4 p-3 bg-white rounded-lg border border-purple-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">
                                    Selected: <span id="selectedCount-{{ $booking->id }}" class="font-bold text-purple-700">1</span> animal(s)
                                </span>
                                <span class="text-gray-700">
                                    Estimated Fee: <span id="estimatedFee-{{ $booking->id }}" class="font-bold text-green-600">RM {{ number_format($allFeeBreakdowns[$animals->first()->id]['total_fee'] ?? 0, 2) }}</span>
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gray-100 rounded-xl p-6 text-center">
                    <p class="text-gray-500">No animals associated with this booking.</p>
                </div>
            @endif
