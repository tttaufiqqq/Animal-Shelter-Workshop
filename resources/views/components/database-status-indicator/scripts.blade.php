
<script>
    function toggleDbStatus() {
        const panel = document.getElementById('dbStatusPanel');
        const badge = document.getElementById('dbStatusBadge');

        panel.classList.toggle('hidden');

        // Rotate arrow icon
        const arrow = badge.querySelector('svg:last-child');
        if (!panel.classList.contains('hidden')) {
            arrow.style.transform = 'rotate(180deg)';
        } else {
            arrow.style.transform = 'rotate(0deg)';
        }
    }

    function refreshDbStatus() {
        // Show loading state
        const refreshBtn = event.target.closest('button');
        const originalHtml = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Refreshing...';
        refreshBtn.disabled = true;

        // Reload the page to get fresh connection status
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    // Real-time Database Status Monitoring
    let dbStatusData = @json($dbConnectionStatus ?? []);
    let autoRefreshInterval = null;
    let lastStatusHash = JSON.stringify(dbStatusData);

    // Auto-refresh database status from API
    async function checkDatabaseStatus() {
        try {
            const response = await fetch('/api/database-status');
            const data = await response.json();

            // Check if status changed
            const newStatusHash = JSON.stringify(data.status);
            if (newStatusHash !== lastStatusHash) {
                console.log('[DB Monitor] Status changed, reloading page...');

                // Detect which databases changed
                Object.keys(data.status).forEach(conn => {
                    const oldStatus = dbStatusData[conn]?.connected;
                    const newStatus = data.status[conn]?.connected;

                    if (oldStatus !== newStatus) {
                        const connName = conn.charAt(0).toUpperCase() + conn.slice(1);
                        if (newStatus) {
                            showToast(`✅ ${connName} database is back online!`, 'success');
                        } else {
                            showToast(`⚠️ ${connName} database went offline!`, 'warning');
                        }
                    }
                });

                // Update local data and reload page after brief delay
                dbStatusData = data.status;
                lastStatusHash = newStatusHash;

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            console.error('[DB Monitor] Failed to check status:', error);
        }
    }

    // Toast notification system
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';

        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="font-medium">${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Slide in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);

        // Slide out and remove
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Start auto-refresh on page load
    window.addEventListener('DOMContentLoaded', function() {
        // Check every 20 seconds (balanced between real-time and server load)
        autoRefreshInterval = setInterval(checkDatabaseStatus, 20000);
        console.log('[DB Monitor] Auto-refresh started (every 20 seconds)');

        // Show initial notification
        const disconnectedCount = {{ count($dbDisconnected ?? []) }};
        if (disconnectedCount > 0) {
            console.log(`[DB Monitor] ${disconnectedCount} database(s) currently offline`);
        }
    });

    // Clean up interval on page unload
    window.addEventListener('beforeunload', function() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });

    // Close panel when clicking outside
    document.addEventListener('click', function(event) {
        const panel = document.getElementById('dbStatusPanel');
        const badge = document.getElementById('dbStatusBadge');

        if (!panel.contains(event.target) && !badge.contains(event.target)) {
            panel.classList.add('hidden');
            const arrow = badge.querySelector('svg:last-child');
            arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>
