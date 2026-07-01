{{-- Loading Overlay --}}
<div id="loadingOverlay" class="fixed inset-0 z-50 hidden">
    {{-- Blurred Background --}}
    <div class="absolute inset-0 bg-white/60 backdrop-blur-sm"></div>

    {{-- Centered Loading Content --}}
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center gap-4 border border-purple-100">
            {{-- Spinning Loader --}}
            <div class="relative">
                <div class="w-16 h-16 border-4 border-purple-200 rounded-full animate-spin border-t-purple-600"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-paw text-purple-600 text-xl animate-pulse"></i>
                </div>
            </div>

            {{-- Loading Text --}}
            <div class="text-center">
                <p class="text-lg font-bold text-gray-800">Loading Profile</p>
                <p class="text-sm text-gray-500">Please wait...</p>
            </div>

            {{-- Progress Dots --}}
            <div class="flex gap-1">
                <span class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                <span class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                <span class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay Script --}}
<script>
    function showLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
        }
    }

    // Hide loading overlay when page is loaded from cache (back button)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        }
    });
</script>
