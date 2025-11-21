<!-- Adoption Fee Modal -->
<div id="adoptionFeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">💰</span>
                    <div>
                        <h2 class="text-2xl font-bold">Adoption Fee Breakdown</h2>
                        <p class="text-green-100 text-sm">Booking #{{ $booking->id }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeAdoptionFeeModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">

            <!-- Select Animal -->
            <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-5">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-paw text-purple-600 mr-2"></i>
                    Select an Animal to Adopt
                </h3>
                <div class="space-y-2">
                    @foreach($booking->animals as $animal)
                        <label class="flex items-center cursor-pointer bg-white rounded p-3 shadow-sm hover:shadow-md transition">
                            <input type="radio"
                                   name="selected_animal"
                                   value="{{ $animal->id }}"
                                   class="mr-3 animal-radio"
                                   @if($loop->first) checked @endif>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $animal->name }} ({{ $animal->species }}, {{ $animal->age }} yrs)</p>
                                @if($animal->images && $animal->images->count() > 0)
                                    <img src="{{ asset('storage/' . $animal->images->first()->image_path) }}"
                                         alt="{{ $animal->name }}"
                                         class="w-24 h-24 object-cover rounded-lg mt-1">
                                @else
                                    <div class="w-24 h-24 bg-gray-200 flex items-center justify-center rounded-lg mt-1">
                                        <span class="text-2xl">@if(strtolower($animal->species)=='dog') 🐕 @elseif(strtolower($animal->species)=='cat') 🐈 @else 🐾 @endif</span>
                                    </div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Fee Breakdown Partial -->
            <div class="space-y-3">
                <!-- Base Fee -->
                <div class="flex justify-between items-center py-3 border-b border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-800">Base Adoption Fee</p>
                        <p class="text-sm text-gray-600">{{ $animal->species }}</p>
                    </div>
                    <span class="text-lg font-bold text-gray-800">RM {{ number_format($feeBreakdown['base_fee'], 2) }}</span>
                </div>

                <!-- Medical Fee -->
                <div class="flex justify-between items-center py-3 border-b border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-800">Medical Records</p>
                        <p class="text-sm text-gray-600">{{ $feeBreakdown['medical_count'] }} record(s) × RM {{ number_format($feeBreakdown['medical_rate'], 2) }}</p>
                    </div>
                    <span class="text-lg font-bold text-gray-800">RM {{ number_format($feeBreakdown['medical_fee'], 2) }}</span>
                </div>

                <!-- Vaccination Fee -->
                <div class="flex justify-between items-center py-3 border-b border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-800">Vaccination Records</p>
                        <p class="text-sm text-gray-600">{{ $feeBreakdown['vaccination_count'] }} record(s) × RM {{ number_format($feeBreakdown['vaccination_rate'], 2) }}</p>
                    </div>
                    <span class="text-lg font-bold text-gray-800">RM {{ number_format($feeBreakdown['vaccination_fee'], 2) }}</span>
                </div>

                <!-- Total -->
                <div class="flex justify-between items-center py-4 bg-green-50 rounded-lg px-4 mt-4">
                    <div>
                        <p class="text-xl font-bold text-gray-800">Total Adoption Fee</p>
                        <p class="text-sm text-gray-600">All medical care included</p>
                    </div>
                    <span class="text-3xl font-bold text-green-600">RM {{ number_format($feeBreakdown['total_fee'], 2) }}</span>
                </div>
            </div>


            <!-- What's Included -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-5 mt-4">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-check-circle text-yellow-600 mr-2"></i>
                    What's Included in the Fee
                </h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-2 mt-1"></i>Complete medical history and records</li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-2 mt-1"></i>All vaccinations administered at our facility</li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-2 mt-1"></i>Health certificate and documentation</li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-2 mt-1"></i>Post-adoption support and guidance</li>
                </ul>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 p-6 border-t border-gray-200">
            <form id="confirmAdoptionForm" action="{{ route('bookings.confirm', $booking->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="animal_id" id="selectedAnimalId">

                <!-- Terms & Conditions -->
                <div class="mb-4 flex items-start">
                    <input type="checkbox"
                           id="agree_terms"
                           name="agree_terms"
                           class="mt-1 mr-3 h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                           required>
                    <label for="agree_terms" class="text-sm text-gray-700">
                        I understand and agree to pay the adoption fee for the selected animal. <span class="text-red-600">*</span>
                    </label>
                </div>

                <div class="flex flex-wrap justify-end gap-3">
                    <button type="button"
                            onclick="closeAdoptionFeeModal()"
                            class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-lg hover:from-green-700 hover:to-green-800 transition duration-300 shadow-lg">
                        <i class="fas fa-check-circle mr-2"></i>Complete Adoption
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="adoptionFeeModalContainer"></div>

<script>
    // Close modal
    function closeAdoptionFeeModal() {
        document.getElementById('adoptionFeeModal')?.remove();
    }

    // Close modal when clicking outside
    document.getElementById('adoptionFeeModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeAdoptionFeeModal();
    });

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAdoptionFeeModal();
    });

    // Load adoption fee for selected animal via AJAX
    function loadAdoptionFee(animalId) {
        const container = document.getElementById('feeBreakdownContainer');
        container.innerHTML = `
        <div class="text-center py-6">
            <i class="fas fa-spinner fa-spin text-green-600 text-3xl mb-3"></i>
            <p class="text-gray-600">Loading adoption fee...</p>
        </div>`;

        fetch("{{ route('bookings.adoption-fee', $booking->id) }}?animal_id=" + animalId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.text();
            })
            .then(html => {
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading adoption fee:', error);
                container.innerHTML = '<p class="text-red-600 text-center py-6">Failed to load adoption fee.</p>';
            });
    }



    // Handle animal selection
    document.querySelectorAll('.animal-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const animalId = this.value;
            document.getElementById('selectedAnimalId').value = animalId;
            loadAdoptionFee(animalId);
        });
    });

    // Initialize first animal after modal loads
    function initializeFeeOnModalLoad() {
        const firstRadio = document.querySelector('.animal-radio:checked');
        if (firstRadio) {
            document.getElementById('selectedAnimalId').value = firstRadio.value;
            loadAdoptionFee(firstRadio.value);
        }
    }

    // Call initialize function after DOM
    initializeFeeOnModalLoad();
</script>
