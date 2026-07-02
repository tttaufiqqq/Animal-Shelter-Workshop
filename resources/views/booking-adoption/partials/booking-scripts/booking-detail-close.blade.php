<script>
    function closeBookingDetailModal() {
        const modal = document.getElementById('bookingDetailModal');
        const detailContent = document.getElementById('bookingDetailContent');

        // Reset scroll position when closing
        if (detailContent) {
            detailContent.scrollTop = 0;
        }

        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
