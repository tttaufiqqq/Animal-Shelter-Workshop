        <!-- Status Change Notification Banner -->
        @if($hasStatusChanges)
            <div class="bg-gradient-to-r from-green-50 to-green-100 border-b-2 border-green-300 p-4 animate-pulse">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-green-900">Report Status Updated!</h3>
                            <div class="text-xs text-green-700 mt-1 space-y-1">
                                @foreach($statusChanges as $change)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">Report #{{ $change['report_id'] }}:</span>
                                        <span class="px-2 py-0.5 bg-white rounded text-gray-700">{{ $change['old_status'] }}</span>
                                        <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="px-2 py-0.5 bg-green-600 text-white rounded font-semibold">{{ $change['new_status'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <button wire:click="acknowledgeChanges"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition duration-300 shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Got it!
                    </button>
                </div>
            </div>
        @endif
