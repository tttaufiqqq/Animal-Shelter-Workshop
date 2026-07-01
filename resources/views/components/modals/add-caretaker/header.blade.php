<!-- Add Caretaker Modal -->
<div id="caretakerModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-gradient-to-r from-teal-600 to-teal-700 text-white p-6 rounded-t-2xl z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-plus text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Add New Caretaker</h2>
                        <p class="text-teal-100 text-sm">Create a new caretaker account</p>
                    </div>
                </div>
                <button onclick="closeCaretakerModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            {{-- Success Alert --}}
            @if (session('caretaker_success'))
                <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-semibold text-green-700">{{ session('caretaker_success') }}</p>
                </div>
            @endif

            {{-- Error Alert --}}
            @if (session('caretaker_error'))
                <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="font-semibold text-red-700">{{ session('caretaker_error') }}</p>
                </div>
            @endif
