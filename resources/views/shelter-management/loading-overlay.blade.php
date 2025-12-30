{{-- Global Loading Overlay --}}
<div id="globalLoadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-[100]">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="flex flex-col items-center">
            {{-- Animated Spinner --}}
            <div class="relative">
                <div class="w-20 h-20 border-8 border-purple-200 rounded-full"></div>
                <div class="w-20 h-20 border-8 border-purple-600 rounded-full border-t-transparent absolute top-0 left-0 animate-spin"></div>
            </div>

            {{-- Loading Message --}}
            <div class="mt-6 text-center">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Please Wait</h3>
                <p class="text-gray-600" id="globalLoadingMessage">Processing your request...</p>
            </div>

            {{-- Progress Indicator --}}
            <div class="mt-4 w-full">
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full animate-pulse" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
