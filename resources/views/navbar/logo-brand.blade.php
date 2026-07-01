<!-- Alpine.js - Required for dropdown functionality -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<nav class="bg-gradient-to-r from-purple-700 to-purple-900 shadow-lg">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <div class="flex justify-between items-center h-16">
   <!-- Logo/Brand -->
   <div class="flex items-center space-x-2 relative" x-data="{ showTooltip: false }">
        <a href="{{ route('welcome') }}"
           class="flex items-center space-x-2 group"
           @mouseenter="showTooltip = true"
           @mouseleave="showTooltip = false">
            <span class="text-3xl group-hover:scale-110 transition-transform duration-300">🐾</span>
            <span class="text-white font-bold text-xl group-hover:text-purple-200 transition duration-300">
                Stray Animal Shelter
            </span>
        </a>

        <!-- Tooltip -->
        <div x-show="showTooltip"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="absolute left-0 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
             style="display: none;">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-purple-900">Click to return to homepage</span>
            </div>
            <!-- Arrow pointing up -->
            <div class="absolute left-6 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 transform rotate-45"></div>
        </div>
    </div>
