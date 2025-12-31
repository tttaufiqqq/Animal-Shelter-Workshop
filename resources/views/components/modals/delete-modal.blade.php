@props(['report'])

{{-- Delete Confirmation Modal --}}
<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-6 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h2 class="text-lg font-bold">Delete Report</h2>
                        <p class="text-red-100 text-sm">Report #{{ $report->id }}</p>
                    </div>
                </div>
                <button onclick="closeDeleteModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <p class="text-gray-700 mb-4 text-sm font-medium">Are you sure you want to delete this report?</p>
            <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
                <p class="text-xs font-semibold text-red-800 mb-1">Warning: This action cannot be undone!</p>
                <p class="text-xs text-red-700">All information will be permanently deleted:</p>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside space-y-1">
                    <li>Report details and location</li>
                    <li>All uploaded images</li>
                    <li>Assignment history</li>
                </ul>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 p-4 border-t border-gray-200 flex gap-3 rounded-b-2xl">
            <button type="button"
                    onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors text-sm">
                Cancel
            </button>

            <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="flex-1" id="deleteReportForm">
                @csrf
                @method('DELETE')
                <button type="submit"
                        id="confirmDeleteBtn"
                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors text-sm flex items-center gap-2 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span id="confirmDeleteBtnText">Delete</span>
                </button>
            </form>
        </div>
    </div>
</div>
