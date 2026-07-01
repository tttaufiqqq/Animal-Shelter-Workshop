<!-- Global Loading Overlay -->
@include('booking-adoption.partials.loading-overlay')

<!-- Adoption Detail Modal -->
<div id="adoptionDetailModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-[60]">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="adoptionModalContent">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold" id="adoptionModalTitle">Adoption Details</h2>
                        <p class="text-green-100 text-sm" id="adoptionModalSubtitle">Booking #000</p>
                    </div>
                </div>
                <button onclick="closeAdoptionModal()" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-xl flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6" id="adoptionModalBody">
            <!-- Loading State -->
            <div id="adoptionModalLoading" class="flex flex-col items-center justify-center py-12">
                <div class="w-16 h-16 border-4 border-green-200 border-t-green-600 rounded-full animate-spin mb-4"></div>
                <p class="text-gray-600 font-medium">Loading adoption details...</p>
            </div>
            <!-- Content will be populated dynamically -->
            <div id="adoptionModalContent-inner" class="hidden"></div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex-shrink-0 border-t border-gray-100">
            <button onclick="closeAdoptionModal()" class="w-full px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-times"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- Booking Modal JavaScript -->
<script src="{{ asset('js/booking-modal.js') }}"></script>
