
    // Close adoption modal
    function closeAdoptionModal() {
        const modal = document.getElementById('adoptionDetailModal');
        const content = document.getElementById('adoptionModalContent');

        // Animate out
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 200);
    }

    // Close on backdrop click
    document.getElementById('adoptionDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAdoptionModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('adoptionDetailModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAdoptionModal();
            }
        }
    });

    // Close payment status modal
    function closePaymentModal() {
        const modal = document.getElementById('paymentStatusModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
</script>
</body>
</html>
