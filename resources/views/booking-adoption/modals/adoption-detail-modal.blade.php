{{-- Adoption Detail Modal (Expanded View) --}}
<div id="adoptionDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center p-4" style="z-index: 100000 !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;">
    <div class="bg-white rounded-2xl shadow-2xl w-[800px] max-w-full max-h-[90vh] flex flex-col overflow-hidden">
        {{-- Modal Header (Fixed at top) --}}
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 flex-shrink-0 rounded-t-2xl z-20 relative">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">💚</span>
                    <div>
                        <h2 class="text-2xl font-bold" id="detailAdoptionTitle">Adoption Records</h2>
                        <p class="text-green-100 text-sm">Adoption information for this booking</p>
                    </div>
                </div>
                <button onclick="closeAdoptionDetailModal()" class="text-white hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Modal Body (Scrollable) --}}
        <div class="flex-1 overflow-y-auto p-6 relative z-10" id="adoptionDetailContent">
            {{-- Content will be populated dynamically --}}
        </div>
    </div>
</div>
