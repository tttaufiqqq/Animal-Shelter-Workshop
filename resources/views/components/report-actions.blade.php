@props(['report', 'caretakers'])

{{-- Actions Card --}}
<div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">Admin Actions</h2>
        </div>
    </div>
    <div class="p-6 space-y-6">
        <!-- Current Assignment Status -->
        @if($report->rescue && $report->rescue->caretaker)
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-blue-900">Currently Assigned</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $report->rescue->caretaker->name }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-900">Not Yet Assigned</p>
                        <p class="text-xs text-yellow-700 mt-1">Please assign a caretaker to handle this report</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Assign to Caretaker Form -->
        <form action="{{ route('reports.assign-caretaker', $report->id) }}" method="POST" class="space-y-3" id="assignCaretakerForm">
            @csrf
            @method('PATCH')

            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ $report->rescue && $report->rescue->caretaker ? 'Reassign Caretaker' : 'Assign Caretaker' }}
            </label>

            <select name="caretaker_id" required id="caretakerSelect"
                    data-current-caretaker="{{ $report->rescue && $report->rescue->caretaker ? $report->rescue->caretakerID : '' }}"
                    class="w-full px-3 py-2 text-sm border @error('caretaker_id') border-red-500 @else border-gray-300 @enderror rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                <option value="">Select caretaker...</option>
                @foreach($caretakers as $caretaker)
                    <option value="{{ $caretaker->id }}"
                            data-name="{{ $caretaker->name }}"
                            {{ old('caretaker_id') == $caretaker->id || ($report->rescue && $report->rescue->caretakerID == $caretaker->id) ? 'selected' : '' }}>
                        {{ $caretaker->name }}
                    </option>
                @endforeach
            </select>

            @error('caretaker_id')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <button type="button" id="assignBtn" onclick="showAssignmentConfirmation()"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm font-medium shadow flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="assignBtnText">{{ $report->rescue && $report->rescue->caretaker ? 'Update Assignment' : 'Assign Caretaker' }}</span>
            </button>
        </form>

        <!-- Danger Zone -->
        <div class="pt-4 border-t border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Danger Zone</p>
            <button type="button" onclick="openDeleteModal()"
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium shadow flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Delete Report</span>
            </button>
        </div>
    </div>
</div>
