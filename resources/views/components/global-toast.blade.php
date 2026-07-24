<!-- Global Toast (site-wide error/status display, independent of any modal) -->
<div id="globalToastContainer" class="fixed top-24 right-4 z-[200] w-full max-w-sm space-y-2 pointer-events-none"></div>
<script>
    if (typeof window.showGlobalToast !== 'function') {
        window.showGlobalToast = function (message, type, duration) {
            type = type || 'info';
            duration = duration || 6000;

            const container = document.getElementById('globalToastContainer');
            if (!container) return;

            const colors = { error: 'bg-red-600', success: 'bg-green-600', warning: 'bg-yellow-500', info: 'bg-blue-600' };
            const icons = { error: '❌', success: '✅', warning: '⚠️', info: 'ℹ️' };

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto flex items-start gap-2 px-4 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 ' + (colors[type] || colors.info);

            const icon = document.createElement('span');
            icon.className = 'text-lg leading-none';
            icon.textContent = icons[type] || icons.info;

            const text = document.createElement('span');
            text.className = 'flex-1 text-sm';
            text.textContent = message;

            toast.append(icon, text);
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };
    }
</script>
