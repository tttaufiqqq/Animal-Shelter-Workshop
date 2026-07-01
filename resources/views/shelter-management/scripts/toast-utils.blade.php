<script>
    // ==================== TOAST NOTIFICATION UTILITIES ====================
    let toastTimeout = null;
    let toastProgressInterval = null;

    function showToast(message, type = 'info', title = '', duration = 4000) {
        const toast = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        const toastProgress = document.getElementById('toastProgress');

        if (toastTimeout) clearTimeout(toastTimeout);
        if (toastProgressInterval) clearInterval(toastProgressInterval);

        const configs = {
            error: {
                borderColor: 'border-red-500',
                iconColor: 'text-red-500',
                progressColor: 'bg-red-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                title: title || 'Error'
            },
            success: {
                borderColor: 'border-green-500',
                iconColor: 'text-green-500',
                progressColor: 'bg-green-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                title: title || 'Success'
            },
            warning: {
                borderColor: 'border-yellow-500',
                iconColor: 'text-yellow-500',
                progressColor: 'bg-yellow-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
                title: title || 'Warning'
            },
            info: {
                borderColor: 'border-blue-500',
                iconColor: 'text-blue-500',
                progressColor: 'bg-blue-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                title: title || 'Info'
            }
        };

        const config = configs[type] || configs.info;

        toast.classList.remove('border-red-500', 'border-green-500', 'border-yellow-500', 'border-blue-500');
        toast.classList.add(config.borderColor);

        toastIcon.classList.remove('text-red-500', 'text-green-500', 'text-yellow-500', 'text-blue-500');
        toastIcon.classList.add(config.iconColor);
        toastIcon.querySelector('svg').innerHTML = config.icon;

        toastProgress.classList.remove('bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-blue-500');
        toastProgress.classList.add(config.progressColor);

        toastTitle.textContent = config.title;
        toastMessage.textContent = message;

        toast.classList.remove('hidden', 'toast-exit');
        toast.classList.add('toast-enter');

        toastProgress.style.width = '100%';
        let progress = 100;
        const step = 100 / (duration / 100);

        toastProgressInterval = setInterval(() => {
            progress -= step;
            toastProgress.style.width = progress + '%';
            if (progress <= 0) {
                clearInterval(toastProgressInterval);
            }
        }, 100);

        toastTimeout = setTimeout(() => {
            closeToast();
        }, duration);
    }

    function closeToast() {
        const toast = document.getElementById('toastNotification');
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-exit');

        setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('toast-exit');
        }, 300);

        if (toastTimeout) clearTimeout(toastTimeout);
        if (toastProgressInterval) clearInterval(toastProgressInterval);
    }
</script>
