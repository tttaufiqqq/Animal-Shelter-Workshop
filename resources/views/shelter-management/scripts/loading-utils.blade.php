<script>
    // ==================== ROUTE CONFIGURATION ====================
    const routePrefix = '{{ $routePrefix ?? "shelter-management" }}';
    const routeBaseUrl = routePrefix === 'admin.shelter-management' ? '/admin/shelter-management' : '/shelter-management';

    // ==================== LOADING INDICATOR UTILITIES ====================
    function showLoading(message = 'Processing...') {
        const loadingOverlay = document.getElementById('globalLoadingOverlay');
        const loadingMessage = document.getElementById('globalLoadingMessage');
        if (loadingOverlay && loadingMessage) {
            loadingMessage.textContent = message;
            loadingOverlay.classList.remove('hidden');
        }
    }

    function hideLoading() {
        const loadingOverlay = document.getElementById('globalLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
    }

    function setButtonLoading(button, isLoading, originalText = '') {
        if (!button) return;

        if (isLoading) {
            button.disabled = true;
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            button.classList.add('opacity-75', 'cursor-not-allowed');
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml || originalText;
            button.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }
</script>
