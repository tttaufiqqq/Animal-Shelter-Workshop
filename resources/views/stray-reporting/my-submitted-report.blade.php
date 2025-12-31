{{--
    My Submitted Reports Component (Livewire-Powered)

    This file includes modular components for displaying user's submitted reports:
    - My Reports List Modal (Livewire component with real-time status tracking)
    - Report Detail Modal (detailed view with map and status tracker)
    - Image Modal (full-size image preview)
    - JavaScript functions for all modal interactions

    Features:
    - Real-time status updates (polls every 15 seconds)
    - Automatic notification when report status changes
    - No page refresh needed
--}}

{{-- Livewire Component for Real-time Reports Tracking --}}
@livewire('user-reports-tracker')

{{-- Report Detail Modal (static, populated by JavaScript) --}}
@include('stray-reporting.modals.report-detail-modal')

{{-- Image Modal --}}
@include('stray-reporting.modals.report-image-modal')

{{-- Include JavaScript Functionality --}}
@include('stray-reporting.partials.my-reports-scripts')

{{-- Livewire Event Listeners --}}
@push('scripts')
<script>
    // Listen for status change events from Livewire
    document.addEventListener('livewire:init', () => {
        Livewire.on('status-changed', (event) => {
            const changes = event.changes;

            // Play notification sound (optional)
            // const audio = new Audio('/sounds/notification.mp3');
            // audio.play().catch(() => {});

            // Show toast notification for each change
            changes.forEach((change, index) => {
                setTimeout(() => {
                    showStatusChangeToast(change);
                }, index * 200); // Stagger notifications
            });
        });

        Livewire.on('changes-acknowledged', () => {
            // Show brief confirmation
            showSuccessToast('Status updates acknowledged!');
        });

        Livewire.on('reports-refreshed', () => {
            showSuccessToast('Reports refreshed!');
        });
    });

    // Show status change toast notification
    function showStatusChangeToast(change) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-lg shadow-2xl z-[100] animate-slide-in-right max-w-md';
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-sm">Report #${change.report_id} Updated!</h4>
                    <p class="text-xs mt-1 opacity-90">
                        Status changed to: <span class="font-bold">${change.new_status}</span>
                    </p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Show success toast
    function showSuccessToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-[100]';
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="font-semibold">${message}</span>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s ease-out';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>

{{-- Animation Styles --}}
<style>
    @keyframes slide-in-right {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-slide-in-right {
        animation: slide-in-right 0.3s ease-out;
    }
</style>
@endpush
