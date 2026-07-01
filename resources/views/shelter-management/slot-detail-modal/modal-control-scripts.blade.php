<script>
    function closeSlotDetailModal() {
        document.getElementById('slotDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentSlotId = null;
    }

    function editSlotFromDetail() {
        if (currentSlotId) {
            closeSlotDetailModal();
            if (typeof editSlot === 'function') {
                editSlot(currentSlotId);
            }
        }
    }

    function openInventoryModalForSlot() {
        if (!currentSlotId) return;

        const addInventoryBtn = document.getElementById('addInventoryBtn');
        const addInventoryIcon = document.getElementById('addInventoryIcon');
        const addInventoryText = document.getElementById('addInventoryText');

        if (addInventoryBtn) {
            addInventoryBtn.disabled = true;
            addInventoryIcon.className = 'fas fa-spinner fa-spin';
            addInventoryText.textContent = 'Loading...';
        }

        const slotName = document.getElementById('detailSlotName').textContent;
        if (typeof openInventoryModal === 'function') {
            openInventoryModal(currentSlotId, slotName);
        }

        setTimeout(() => {
            if (addInventoryBtn) {
                addInventoryBtn.disabled = false;
                addInventoryIcon.className = 'fas fa-plus';
                addInventoryText.textContent = 'Add Inventory';
            }
        }, 500);
    }

    function viewInventoryDetails(inventoryId) {
        if (typeof openInventoryDetailModal === 'function') {
            openInventoryDetailModal(inventoryId);
        } else {
            console.log('View inventory:', inventoryId);
        }
    }

    document.getElementById('slotDetailModal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeSlotDetailModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('slotDetailModal')?.classList.contains('hidden')) {
            closeSlotDetailModal();
        }
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
