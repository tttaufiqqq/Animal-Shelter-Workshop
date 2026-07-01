{{-- Map Modal --}}
<div id="mapModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeMapModal()">
    <div class="bg-white rounded shadow-lg max-w-4xl w-full" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center p-4 border-b">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Rescue Location Map</h3>
                <p id="mapModalAddress" class="text-sm text-gray-600 mt-1"></p>
            </div>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="modalMap" class="w-full" style="height: 400px;"></div>
    </div>
</div>
