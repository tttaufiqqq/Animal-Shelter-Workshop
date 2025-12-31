{{-- Cancel Confirmation Modal --}}
<div id="cancelConfirmModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full animate-fadeIn" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 rounded-t-2xl">
            <div class="flex items-center gap-3">
                <span class="text-3xl">⚠️</span>
                <div>
                    <h2 class="text-xl font-bold">Cancel Booking</h2>
                    <p class="text-red-100 text-sm">Booking #{{ $booking->id }}</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <p class="text-gray-700 mb-4 text-lg font-medium">Are you sure you want to cancel this booking?</p>

            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4 mb-4">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-yellow-800 mb-1">Warning: This action cannot be undone</p>
                        <ul class="text-sm text-yellow-700 space-y-1">
                            <li>• Your appointment will be cancelled immediately</li>
                            <li>• The time slot will be made available to others</li>
                            <li>• You will need to create a new booking to visit these animals</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-blue-700">
                        <strong>Alternative:</strong> If you need to reschedule, you can cancel this booking and create a new one with your preferred date and time.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 p-6 border-t border-gray-200 flex gap-3 rounded-b-2xl">
            <button type="button"
                    onclick="closeCancelModal({{ $booking->id }})"
                    id="cancelModalNoBtn-{{ $booking->id }}"
                    class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300 shadow-md">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    No, Keep Booking
                </span>
            </button>

            <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="flex-1" id="cancelForm-{{ $booking->id }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                        id="confirmCancelBtn-{{ $booking->id }}"
                        class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-300 shadow-md flex items-center gap-2 justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span id="confirmCancelBtnText-{{ $booking->id }}">Yes, Cancel Booking</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.2s ease-out;
    }
</style>
