<script>
// Handle reassign slot form submission
function handleReassignSlotSubmit(event) {
    const submitBtn = document.getElementById('reassignSlotBtn');
    const btnText = document.getElementById('reassignSlotBtnText');
    const btnLoading = document.getElementById('reassignSlotBtnLoading');

    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    // Form will submit normally, button stays disabled
}

// Handle assign slot form submission
function handleAssignSlotSubmit(event) {
    const submitBtn = document.getElementById('assignSlotBtn');
    const btnText = document.getElementById('assignSlotBtnText');
    const btnLoading = document.getElementById('assignSlotBtnLoading');

    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    // Form will submit normally, button stays disabled
}

// Handle add to visit list form submission
function handleAddToVisitListSubmit(event) {
    const submitBtn = document.getElementById('addToVisitListBtn');
    const btnText = document.getElementById('addToVisitListBtnText');
    const btnLoading = document.getElementById('addToVisitListBtnLoading');

    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    // Form will submit normally, button stays disabled
}
</script>
