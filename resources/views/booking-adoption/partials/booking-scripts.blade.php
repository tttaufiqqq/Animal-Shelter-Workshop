<script>
    function openBookingModal(bookingId) {
        document.getElementById('bookingModal-' + bookingId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openAdoptionModal(bookingId) {
        document.getElementById('adoptionModal-' + bookingId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAdoptionModal(bookingId) {
        document.getElementById('adoptionModal-' + bookingId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>
