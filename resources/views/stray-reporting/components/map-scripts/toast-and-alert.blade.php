<script>
    // Toast notification system
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const colors = {
            error: 'bg-red-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const toast = document.createElement('div');
        toast.className = `px-4 py-3 rounded-lg shadow-lg text-white font-medium ${colors[type]} transform transition-all duration-300 flex items-center gap-2`;

        // Add icon based on type
        const icons = {
            error: '❌',
            success: '✅',
            warning: '⚠️',
            info: 'ℹ️'
        };

        toast.innerHTML = `
        <span class="text-lg">${icons[type]}</span>
        <span>${message}</span>
    `;

        container.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        return toast;
    }

    // Modal alert system (for report submission feedback)
    function showReportModalAlert(type, message) {
        const alertContainer = document.getElementById('reportModalAlert');
        const isSuccess = type === 'success';

        alertContainer.innerHTML = `
            <div class="flex items-start gap-3 p-4 bg-${isSuccess ? 'green' : 'red'}-50 border border-${isSuccess ? 'green' : 'red'}-200 rounded-xl shadow-sm">
                <svg class="w-6 h-6 text-${isSuccess ? 'green' : 'red'}-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    ${isSuccess
            ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />'
            : '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />'
        }
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-${isSuccess ? 'green' : 'red'}-700">${message}</p>
                </div>
                <button onclick="document.getElementById('reportModalAlert').classList.add('hidden')" class="text-${isSuccess ? 'green' : 'red'}-600 hover:text-${isSuccess ? 'green' : 'red'}-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        alertContainer.classList.remove('hidden');

        // Scroll to alert
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
</script>
