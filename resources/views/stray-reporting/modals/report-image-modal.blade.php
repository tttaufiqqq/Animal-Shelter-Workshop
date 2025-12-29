<!-- Image Modal (Full Size Preview) -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-90 backdrop-blur-sm z-[70] flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-6xl max-h-full">
        <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-screen rounded-xl shadow-2xl">
    </div>
</div>
