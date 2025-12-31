{{-- Image Modal --}}
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md hidden z-[70] flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="max-w-6xl max-h-full relative" onclick="event.stopPropagation()">
        <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img id="modalImage" src="" alt="Enlarged view" class="max-w-full max-h-screen rounded-2xl shadow-2xl">
    </div>
</div>
