<!-- Status Update Card -->
@php
    $isFinal = in_array($rescue->status, ['Success', 'Failed']);
@endphp

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Update Status</h2>
    </div>
    <div class="p-6">
        {{-- Database Offline Warning --}}
        @if(isset($dbWarning) && !$canCompleteRescue && !$isFinal)
            <div class="flex items-start gap-3 p-4 mb-4 bg-amber-50 border-l-4 border-amber-500 rounded">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800">Database Offline</p>
                    <p class="text-sm text-amber-700 mt-1">{{ $dbWarning }}</p>
                </div>
            </div>
        @endif

        @if($isFinal)
            <div class="flex items-start gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="text-sm text-gray-600">This rescue has been finalized and cannot be updated.</p>
            </div>
        @else
            <form id="statusForm" action="{{ route('rescues.update-status', $rescue->id) }}" method="POST">
                @csrf
                @method('PATCH')

                {{-- Smart Progressive Workflow for Caretakers --}}
                @if($rescue->status === 'Scheduled')
                    {{-- Just Assigned: Start rescue or mark as failed if can't start --}}
                    <div class="grid grid-cols-1 gap-3">
                        <button type="button" onclick="updateStatus('In Progress')" id="statusProgressBtn"
                                class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-4 py-3 rounded-lg text-base font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span id="statusProgressText">Start Rescue</span>
                        </button>

                        <button type="button" onclick="updateStatus('Failed')" id="statusFailedBtn"
                                class="bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white px-4 py-3 rounded-lg text-sm font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span id="statusFailedText">Mark as Failed (Can't Start)</span>
                        </button>
                    </div>

                @elseif($rescue->status === 'In Progress')
                    {{-- Currently Working: Complete successfully or mark as failed --}}
                    <div class="grid grid-cols-1 gap-3">
                        <button type="button"
                                @if(!$canCompleteRescue)
                                    disabled
                                    title="Cannot complete rescue: Required databases are offline"
                                    class="bg-gray-400 text-gray-200 px-4 py-3 rounded-lg text-base font-bold cursor-not-allowed flex items-center justify-center gap-2 opacity-60"
                                @else
                                    onclick="updateStatus('Success')"
                                    class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-4 py-3 rounded-lg text-base font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                                @endif
                                id="statusSuccessBtn">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span id="statusSuccessText">Mark as Successful</span>
                            @if(!$canCompleteRescue)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            @endif
                        </button>

                        <button type="button" onclick="updateStatus('Failed')" id="statusFailedBtn"
                                class="bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white px-4 py-3 rounded-lg text-sm font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span id="statusFailedText">Mark as Failed</span>
                        </button>
                    </div>
                @endif
            </form>
        @endif
    </div>
</div>
