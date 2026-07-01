<script>
    // ==================== CONFIRMATION MODAL UTILITIES ====================
    let confirmationCallback = null;

    function showConfirmation(title, message, onConfirm) {
        const modal = document.getElementById('confirmationModal');
        const titleEl = document.getElementById('confirmationModalTitle');
        const messageEl = document.getElementById('confirmationModalMessage');

        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmationCallback = onConfirm;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmationModal() {
        const modal = document.getElementById('confirmationModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        confirmationCallback = null;
    }

    function confirmAction() {
        if (confirmationCallback && typeof confirmationCallback === 'function') {
            confirmationCallback();
        }
        closeConfirmationModal();
    }
</script>
