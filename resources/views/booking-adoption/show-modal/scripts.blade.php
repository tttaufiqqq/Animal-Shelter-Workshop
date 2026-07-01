<style>
    /* Loading spinner animation */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>

<script>
    // Open cancel confirmation modal
    function openCancelModal(bookingId) {
        document.getElementById('cancelConfirmModal-' + bookingId).classList.remove('hidden');
        document.getElementById('cancelConfirmModal-' + bookingId).classList.add('flex');
    }

    // Close cancel confirmation modal
    function closeCancelModal(bookingId) {
        document.getElementById('cancelConfirmModal-' + bookingId).classList.add('hidden');
        document.getElementById('cancelConfirmModal-' + bookingId).classList.remove('flex');
    }

    // Handle cancel booking form submission
    document.addEventListener('DOMContentLoaded', function() {
        // Handle all cancel forms
        document.querySelectorAll('[id^="cancelForm-"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                const bookingId = this.id.replace('cancelForm-', '');
                const submitBtn = document.getElementById('confirmCancelBtn-' + bookingId);
                const noBtn = document.getElementById('cancelModalNoBtn-' + bookingId);

                // Disable buttons
                submitBtn.disabled = true;
                noBtn.disabled = true;

                // Show loading spinner
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Cancelling...</span>
                `;

                // Allow form to submit
                return true;
            });
        });

        // Handle all adoption fee forms (Complete Adoptions button)
        document.querySelectorAll('[id^="adoptionFeeModal-"]').forEach(modal => {
            const form = modal.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const bookingId = modal.id.replace('adoptionFeeModal-', '');
                    const submitBtn = document.getElementById('submitBtn-' + bookingId);

                    // Disable button
                    submitBtn.disabled = true;

                    // Show loading spinner
                    submitBtn.innerHTML = `
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Processing...</span>
                    `;

                    // Allow form to submit
                    return true;
                });
            }
        });
    });
</script>
