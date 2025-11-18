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
                <button onclick="closeAdoptionFeeModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Animal Information -->
            <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-5">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-paw text-purple-600 mr-2"></i>
                    Animal: {{ $booking->animal->name }}
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Species:</span>
                        <span class="font-semibold text-gray-800 ml-2">{{ $booking->animal->species }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Age:</span>
                        <span class="font-semibold text-gray-800 ml-2">{{ $booking->animal->age }}</span>
                    </div>
                </div>
            </div>

            <!-- Fee Breakdown -->
            <div class="bg-white border-2 border-gray-200 rounded-xl p-6">
                <h3 class="font-bold text-gray-800 mb-4 text-lg flex items-center">
                    <i class="fas fa-calculator text-green-600 mr-2"></i>
                    Fee Calculation
                </h3>

                <div class="space-y-3">
                    <!-- Base Fee by Species -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-800">Base Adoption Fee</p>
                            <p class="text-sm text-gray-600">{{ $booking->animal->species }}</p>
                        </div>
                        <span class="text-lg font-bold text-gray-800">RM {{ number_format($feeBreakdown['base_fee'], 2) }}</span>
                    </div>

                    <!-- Medical Records Fee -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-800">Medical Records</p>
                            <p class="text-sm text-gray-600">{{ $feeBreakdown['medical_count'] }} record(s) × RM {{ number_format($feeBreakdown['medical_rate'], 2) }}</p>
                        </div>
                        <span class="text-lg font-bold text-gray-800">RM {{ number_format($feeBreakdown['medical_fee'], 2) }}</span>
                    </div>

                    <!-- Vaccination Records Fee -->
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
            </div>

            <!-- Detailed Medical Records -->
            @if($medicalRecords->count() > 0)
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-5">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-notes-medical text-blue-600 mr-2"></i>
                    Medical Records ({{ $medicalRecords->count() }})
                </h4>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($medicalRecords as $record)
                        <div class="bg-white rounded p-3 text-sm">
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-800">{{ $record->treatment ?? 'Medical Treatment' }}</span>
                                <span class="text-gray-600">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Detailed Vaccination Records -->
            @if($vaccinationRecords->count() > 0)
            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-5">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-syringe text-green-600 mr-2"></i>
                    Vaccination Records ({{ $vaccinationRecords->count() }})
                </h4>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($vaccinationRecords as $record)
                        <div class="bg-white rounded p-3 text-sm">
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-800">{{ $record->vaccine_name ?? 'Vaccination' }}</span>
                                <span class="text-gray-600">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- What's Included -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-5">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-check-circle text-yellow-600 mr-2"></i>
                    What's Included in the Fee
                </h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                        <span>Complete medical history and records</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                        <span>All vaccinations administered at our facility</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                        <span>Health certificate and documentation</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                        <span>Post-adoption support and guidance</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 p-6 border-t border-gray-200">
            <form action="{{ route('bookings.confirm', $booking->id) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <!-- Terms & Conditions -->
                <div class="mb-4 flex items-start">
                    <input type="checkbox" 
                        id="agree_terms" 
                        name="agree_terms" 
                        class="mt-1 mr-3 h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500" 
                        required>
                    <label for="agree_terms" class="text-sm text-gray-700">
                        I understand and agree to pay the adoption fee of <strong>RM {{ number_format($feeBreakdown['total_fee'], 2) }}</strong>. I acknowledge that this fee covers all medical care and vaccinations provided to the animal. <span class="text-red-600">*</span>
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
                        <i class="fas fa-check-circle mr-2"></i>Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function closeAdoptionFeeModal() {
        document.getElementById('adoptionFeeModal').remove();
    }

    // Close modal when clicking outside
    document.getElementById('adoptionFeeModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAdoptionFeeModal();
        }
    });

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('adoptionFeeModal');
            if (modal) {
                closeAdoptionFeeModal();
            }
        }
    });
    function openAdoptionFeeModal(bookingId) {
        const container = document.getElementById('adoptionFeeModalContainer');
        if (!container) {
            // Create container if it doesn't exist
            const newContainer = document.createElement('div');
            newContainer.id = 'adoptionFeeModalContainer';
            document.body.appendChild(newContainer);
        }
        
        const containerElement = document.getElementById('adoptionFeeModalContainer');
        containerElement.innerHTML = '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]"><div class="bg-white rounded-lg p-6"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</div></div>';
        
        fetch(`/bookings/${bookingId}/adoption-fee`, {
            method: 'GET',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            containerElement.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading adoption fee:', error);
            alert('Failed to load adoption fee details: ' + error.message);
            containerElement.innerHTML = '';
        });
    }
</script>