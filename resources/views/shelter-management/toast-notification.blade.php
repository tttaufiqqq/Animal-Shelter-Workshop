{{-- Toast Notification --}}
<div id="toastNotification" class="hidden fixed top-4 right-4 z-50 transform transition-all duration-300">
    <div class="bg-white rounded-lg shadow-2xl border-l-4 overflow-hidden max-w-md">
        <div class="p-4 flex items-start gap-3">
            <!-- Icon -->
            <div id="toastIcon" class="flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1">
                <h4 id="toastTitle" class="font-bold text-gray-900 mb-1">Notification</h4>
                <p id="toastMessage" class="text-sm text-gray-600"></p>
            </div>

            <!-- Close Button -->
            <button onclick="closeToast()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Progress Bar -->
        <div class="h-1 bg-gray-200">
            <div id="toastProgress" class="h-full transition-all duration-100 ease-linear"></div>
        </div>
    </div>
</div>

<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast-enter {
    animation: slideInRight 0.3s ease-out forwards;
}

.toast-exit {
    animation: slideOutRight 0.3s ease-in forwards;
}
</style>
