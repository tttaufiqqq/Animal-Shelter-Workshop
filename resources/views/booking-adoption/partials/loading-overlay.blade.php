{{-- Global Loading Overlay --}}
<div id="loadingOverlay" class="loading-overlay hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[100]">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center transform scale-95 opacity-0 transition-all duration-300" id="loadingContent">
        {{-- Animated Spinner --}}
        <div class="relative w-24 h-24 mx-auto mb-6">
            {{-- Outer Ring --}}
            <div class="absolute inset-0 border-8 border-purple-200 rounded-full"></div>

            {{-- Spinning Ring --}}
            <div class="absolute inset-0 border-8 border-transparent border-t-purple-600 border-r-purple-600 rounded-full animate-spin"></div>

            {{-- Inner Pulsing Circle --}}
            <div class="absolute inset-3 bg-purple-100 rounded-full animate-pulse flex items-center justify-center">
                <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        {{-- Loading Text --}}
        <h3 class="text-2xl font-bold text-gray-800 mb-2" id="loadingTitle">Processing...</h3>
        <p class="text-gray-600" id="loadingMessage">Please wait while we process your request</p>

        {{-- Progress Dots --}}
        <div class="flex justify-center gap-2 mt-6">
            <span class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0s"></span>
            <span class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
            <span class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
        </div>
    </div>
</div>

<style>
    .loading-overlay.show #loadingContent {
        transform: scale(1);
        opacity: 1;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    .animate-pulse {
        animation: pulse 2s ease-in-out infinite;
    }

    .animate-bounce {
        animation: bounce 1.4s ease-in-out infinite;
    }
</style>
