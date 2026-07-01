<script>
    // Close Report Detail Modal
    function closeReportDetailModal() {
        const modal = document.getElementById('reportDetailModal');
        const detailContent = document.getElementById('reportDetailContent');

        if (detailContent) detailContent.scrollTop = 0;

        modal.classList.add('hidden');

        if (detailMap) {
            detailMap.remove();
            detailMap = null;
        }
    }

    // Open My Reports Modal (Alpine.js handles body overflow)
    function openMyReportsModal() {
        window.dispatchEvent(new CustomEvent('open-my-reports-modal'));
    }

    // Close My Reports Modal (Alpine.js handles body overflow)
    function closeMyReportsModal() {
        window.dispatchEvent(new CustomEvent('close-my-reports-modal'));
    }

    // Image modal functions
    function openImageModal(imageSrc) {
        event.stopPropagation();
        const modal = document.getElementById('imageModal');
        document.getElementById('modalImage').src = imageSrc;
        modal.classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    // Close modals when clicking outside (reportDetailModal only)
    document.getElementById('reportDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeReportDetailModal();
    });

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const imageModal = document.getElementById('imageModal');
            const detailModal = document.getElementById('reportDetailModal');
            const reportsModal = document.getElementById('myReportsModal');

            if (!imageModal.classList.contains('hidden')) {
                closeImageModal();
            } else if (!detailModal.classList.contains('hidden')) {
                closeReportDetailModal();
            } else if (reportsModal && reportsModal.style.display !== 'none') {
                closeMyReportsModal();
            }
        }
    });

    // Re-open modal automatically if pagination triggers a reload with ?open_modal=1
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_modal') == 1) {
        window.addEventListener('load', () => {
            openMyReportsModal();
        });
    }
</script>
