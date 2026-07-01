<script>
    // Set base URL for remove action (using route helper)
    const removeBaseUrl = "{{ url('visit-list/remove') }}/";

    // Store animal info for removal
    let pendingRemoveAnimalId = null;
    let pendingRemoveAnimalName = '';

    // Open remove confirmation modal
    window.openRemoveConfirmModal = function(animalId, animalName) {
        pendingRemoveAnimalId = animalId;
        pendingRemoveAnimalName = animalName;

        const modal = document.getElementById('removeConfirmModal');
        const content = document.getElementById('removeConfirmContent');
        const animalNameElement = document.getElementById('removeAnimalName');

        // Reset modal state (in case it was left in loading state)
        const confirmBtn = document.getElementById('removeModalConfirmBtn');
        const cancelBtn = document.getElementById('removeModalCancelBtn');
        const removeIcon = document.getElementById('removeIcon');
        const removeText = document.getElementById('removeText');
        const removeSpinner = document.getElementById('removeSpinner');

        confirmBtn.disabled = false;
        cancelBtn.disabled = false;
        confirmBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        confirmBtn.classList.add('hover:from-red-600', 'hover:to-red-700', 'hover:shadow-xl');
        cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        removeIcon.classList.remove('hidden');
        removeText.textContent = 'Remove';
        removeSpinner.classList.add('hidden');

        animalNameElement.textContent = animalName;

        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    // Close remove confirmation modal
    window.closeRemoveConfirmModal = function() {
        const modal = document.getElementById('removeConfirmModal');
        const content = document.getElementById('removeConfirmContent');

        content.classList.add('opacity-0', 'scale-95');
        content.classList.remove('opacity-100', 'scale-100');

        setTimeout(() => {
            modal.classList.add('hidden');
            pendingRemoveAnimalId = null;
            pendingRemoveAnimalName = '';
        }, 300);
    }

    // Confirm and submit removal
    window.confirmRemoveAnimal = function() {
        if (pendingRemoveAnimalId) {
            // Show loading state
            const confirmBtn = document.getElementById('removeModalConfirmBtn');
            const cancelBtn = document.getElementById('removeModalCancelBtn');
            const removeIcon = document.getElementById('removeIcon');
            const removeText = document.getElementById('removeText');
            const removeSpinner = document.getElementById('removeSpinner');

            // Disable buttons
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            confirmBtn.classList.add('opacity-75', 'cursor-not-allowed');
            confirmBtn.classList.remove('hover:from-red-600', 'hover:to-red-700', 'hover:shadow-xl');
            cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // Show loading state
            removeIcon.classList.add('hidden');
            removeText.textContent = 'Removing...';
            removeSpinner.classList.remove('hidden');

            // Submit form
            const form = document.getElementById('removeAnimalForm');
            form.action = removeBaseUrl + pendingRemoveAnimalId;
            form.submit();
        }
    }

    // Open / Close Modal
    window.openVisitModal = function() {
        const modal = document.getElementById('visitModal');
        const content = document.getElementById('visitModalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
        // Re-evaluate button state when modal opens
        // Call multiple times to ensure it works in all contexts
        setTimeout(() => window.updateConfirmButton(), 50);
        setTimeout(() => window.updateConfirmButton(), 150);
        setTimeout(() => window.updateConfirmButton(), 300);
    }

    window.closeVisitModal = function() {
        const modal = document.getElementById('visitModal');
        const content = document.getElementById('visitModalContent');
        content.classList.add('opacity-0', 'scale-95');
        content.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
