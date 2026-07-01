<script>
    function toggleInventoryEdit() {
        const form = document.getElementById('inventoryEditForm');
        const isHidden = form.classList.contains('hidden');

        if (isHidden) {
            form.classList.remove('hidden');
            document.getElementById('editInventoryId').value = currentInventoryData.id;
            document.getElementById('editItemName').value = currentInventoryData.item_name;
            document.getElementById('editCategoryID').value = currentInventoryData.categoryID;
            document.getElementById('editQuantity').value = currentInventoryData.quantity;
            document.getElementById('editWeight').value = currentInventoryData.weight || '';
            document.getElementById('editBrand').value = currentInventoryData.brand || '';
            document.getElementById('editStatus').value = currentInventoryData.status;
            document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-times mr-2"></i>Cancel Edit';
        } else {
            form.classList.add('hidden');
            document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
        }
    }

    function cancelInventoryEdit() {
        document.getElementById('inventoryEditForm').classList.add('hidden');
        document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
    }

    function deleteInventoryItem() {
        if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/shelter-management/inventory/${currentInventoryId}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.content;
                form.appendChild(csrfInput);
            }

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeInventoryDetailModal() {
        document.getElementById('inventoryDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentInventoryId = null;
        currentInventoryData = null;

        document.getElementById('inventoryEditForm').classList.add('hidden');
        document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
    }

    document.getElementById('inventoryDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeInventoryDetailModal();
        }
    });

    document.getElementById('updateInventoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('updateInventorySubmitBtn');
        const submitIcon = document.getElementById('updateInventoryIcon');
        const submitText = document.getElementById('updateInventoryText');
        const cancelBtn = document.getElementById('updateInventoryCancelBtn');

        submitBtn.disabled = true;
        cancelBtn.disabled = true;
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = 'Updating...';

        return true;
    });
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
