    {{-- Auto-Refresh Toggle --}}
    <div class="mb-4 flex items-center justify-between bg-white rounded-lg shadow-sm p-3 border border-gray-200">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Auto-refresh new reports</span>
        </div>
        <button wire:click="toggleAutoRefresh"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $autoRefresh ? 'bg-purple-600' : 'bg-gray-300' }}">
            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $autoRefresh ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    {{-- Manual Refresh Banner (only shows if auto-refresh is OFF) --}}
    @if($hasNewReports && !$autoRefresh)
        <div class="mb-4 flex items-center justify-between gap-2 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg animate-pulse">
            <div class="flex items-center gap-3">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-800">New Reports Available!</h3>
                    <p class="text-sm text-blue-700 mt-0.5">New stray animal reports have been submitted.</p>
                </div>
            </div>
            <button wire:click="refreshReports"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition duration-300 flex items-center gap-2 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Click to see new reports
            </button>
        </div>
    @endif
