<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Adopter Modal Functions
function openAdopterModal() {
    const modal = document.getElementById('adopterModal');
    modal?.classList.remove('hidden');
    modal?.classList.add('flex');
}

function closeAdopterModal() {
    const modal = document.getElementById('adopterModal');
    modal?.classList.add('hidden');
    modal?.classList.remove('flex');
}

// Note: openResultModal() and closeResultModal() are defined in result.blade.php
// because they include additional logic for loading matches

// Caretaker Modal Functions
function openCaretakerModal() {
    const modal = document.getElementById('caretakerModal');
    modal?.classList.remove('hidden');
    modal?.classList.add('flex');
}

function closeCaretakerModal() {
    const modal = document.getElementById('caretakerModal');
    modal?.classList.add('hidden');
    modal?.classList.remove('flex');
}

// Close modals when clicking outside
document.getElementById('adopterModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAdopterModal();
});

document.getElementById('caretakerModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCaretakerModal();
});

// Auto-open report modal if there are validation errors
@if ($errors->any() || session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        openReportModal();
    });
@endif

// Auto-open caretaker modal if there are validation errors for caretaker form
@if (session('caretaker_error') || $errors->caretaker->any())
    document.addEventListener('DOMContentLoaded', function() {
        openCaretakerModal();
    });
@endif

// Auto-close and show success if caretaker was created successfully
@if (session('caretaker_success'))
    document.addEventListener('DOMContentLoaded', function() {
        openCaretakerModal();
        setTimeout(() => {
            closeCaretakerModal();
        }, 3000); // Close after 3 seconds
    });
@endif
</script>
