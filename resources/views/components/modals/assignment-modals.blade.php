@props(['report'])

{{-- No Caretaker Selected Error Modal --}}
<div id="noCaretakerSelectedModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4" onclick="closeNoCaretakerSelectedModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-6 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h2 class="text-lg font-bold">Selection Required</h2>
                        <p class="text-red-100 text-sm">Report #{{ $report->id }}</p>
                    </div>
                </div>
                <button onclick="closeNoCaretakerSelectedModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <p class="text-gray-700 mb-4 text-sm font-medium">Please select a caretaker before submitting the assignment.</p>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <p class="text-red-800 text-sm">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    You must choose a caretaker from the dropdown menu to proceed with the assignment.
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end gap-3">
            <button type="button" onclick="closeNoCaretakerSelectedModal()" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                OK, I Understand
            </button>
        </div>
    </div>
</div>

{{-- Same Caretaker Error Modal --}}
<div id="sameCaretakerErrorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white p-6 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h2 class="text-lg font-bold">Already Assigned</h2>
                        <p class="text-yellow-100 text-sm">Report #{{ $report->id }}</p>
                    </div>
                </div>
                <button onclick="closeSameCaretakerErrorModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <p class="text-gray-700 mb-4 text-sm font-medium">This report is already assigned to <strong id="currentCaretakerNameError"></strong>.</p>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-yellow-900 mb-1">Cannot Reassign</p>
                        <p class="text-xs text-yellow-800 mt-2">Please select a different caretaker if you want to update the assignment.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 p-4 border-t border-gray-200 flex justify-end rounded-b-2xl">
            <button type="button"
                    onclick="closeSameCaretakerErrorModal()"
                    class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors text-sm">
                OK
            </button>
        </div>
    </div>
</div>

{{-- Assignment Confirmation Modal --}}
<div id="assignmentConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h2 class="text-lg font-bold">Confirm Assignment</h2>
                        <p class="text-purple-100 text-sm">Report #{{ $report->id }}</p>
                    </div>
                </div>
                <button onclick="closeAssignmentModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <p class="text-gray-700 mb-4 text-sm font-medium" id="assignmentConfirmMessage"></p>
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-purple-900 mb-1">Assignment Details</p>
                        <div class="space-y-2 mt-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Report ID:</span>
                                <span class="font-semibold text-gray-900">#{{ $report->id }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Location:</span>
                                <span class="font-semibold text-gray-900 text-right">{{ $report->city }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">New Caretaker:</span>
                                <span class="font-semibold text-gray-900" id="newCaretakerName"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 p-4 border-t border-gray-200 flex gap-3 rounded-b-2xl">
            <button type="button"
                    onclick="closeAssignmentModal()"
                    class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors text-sm">
                Cancel
            </button>

            <button type="button"
                    onclick="confirmAssignment()"
                    id="confirmAssignmentBtn"
                    class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors text-sm flex items-center gap-2 justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="confirmAssignmentBtnText">Confirm Assignment</span>
            </button>
        </div>
    </div>
</div>
