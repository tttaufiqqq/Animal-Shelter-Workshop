<script>
    // ==================== FORM SUBMISSION LOADING STATES ====================

    document.getElementById('sectionForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('sectionSubmitBtn');
        const submitIcon = document.getElementById('sectionSubmitIcon');
        const submitText = document.getElementById('sectionSubmitButtonText');
        const cancelBtn = document.getElementById('sectionCancelBtn');

        const isUpdate = document.getElementById('sectionFormMethod').value === 'PUT';
        showLoading(isUpdate ? 'Updating section...' : 'Creating section...');

        submitBtn.disabled = true;
        cancelBtn.disabled = true;
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = isUpdate ? 'Updating...' : 'Adding...';

        return true;
    });

    document.getElementById('slotForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('slotSubmitBtn');
        const submitIcon = document.getElementById('slotSubmitIcon');
        const submitText = document.getElementById('slotSubmitButtonText');
        const cancelBtn = document.getElementById('slotCancelBtn');

        const isUpdate = document.getElementById('slotFormMethod').value === 'PUT';
        showLoading(isUpdate ? 'Updating slot...' : 'Creating slot...');

        submitBtn.disabled = true;
        cancelBtn.disabled = true;
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = isUpdate ? 'Updating...' : 'Adding...';

        return true;
    });

    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('categorySubmitBtn');
        const submitIcon = document.getElementById('categorySubmitIcon');
        const submitText = document.getElementById('categorySubmitButtonText');
        const cancelBtn = document.getElementById('categoryCancelBtn');

        const isUpdate = document.getElementById('categoryFormMethod').value === 'PUT';
        showLoading(isUpdate ? 'Updating category...' : 'Creating category...');

        submitBtn.disabled = true;
        cancelBtn.disabled = true;
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = isUpdate ? 'Updating...' : 'Adding...';

        return true;
    });

    document.getElementById('inventoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('inventorySubmitBtn');

        showLoading('Adding inventory item...');
        setButtonLoading(submitBtn, true);

        return true;
    });

    const updateInventoryForm = document.getElementById('updateInventoryForm');
    if (updateInventoryForm) {
        updateInventoryForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('updateInventorySubmitBtn');

            showLoading('Updating inventory item...');
            setButtonLoading(submitBtn, true);

            return true;
        });
    }
</script>

<style>
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
