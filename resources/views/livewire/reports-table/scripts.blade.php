    {{-- Livewire Event Listeners & Animations --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            // Listen for new reports loaded event
            Livewire.on('new-reports-loaded', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const count = data.count;
                const reportIds = data.reportIds;

                // Show toast notification
                showNewReportsToast(count, reportIds);

                // Scroll to top to show new reports
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // Listen for clear highlights event
            Livewire.on('clear-highlights-after-delay', () => {
                setTimeout(() => {
                    @this.call('clearNewReportHighlights');
                }, 5000); // Clear after 5 seconds
            });
        });

        // Show toast notification for new reports
        function showNewReportsToast(count, reportIds) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-lg shadow-2xl z-[100] animate-slide-in-right max-w-md';
            toast.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm">${count} New Report${count > 1 ? 's' : ''} Received!</h4>
                        <p class="text-xs mt-1 opacity-90">
                            Report${count > 1 ? 's' : ''} #${reportIds.join(', #')} ${count > 1 ? 'have' : 'has'} been automatically loaded
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
    </script>

    {{-- Animation Styles --}}
    <style>
        @keyframes slide-in-right {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .animate-slide-in-right {
            animation: slide-in-right 0.3s ease-out;
        }

        @keyframes highlight-fade {
            0% { background-color: rgb(220, 252, 231); }
            100% { background-color: rgb(240, 253, 244); }
        }

        .animate-highlight-fade {
            animation: highlight-fade 2s ease-in-out;
        }
    </style>
    @endpush
