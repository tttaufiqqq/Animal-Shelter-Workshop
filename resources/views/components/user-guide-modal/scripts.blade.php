
<script>
function openGuideModal(specificSection = null) {
    const modal = document.getElementById('userGuideModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Reset scroll position to top
    const modalContent = modal.querySelector('.overflow-y-auto');
    if (modalContent) {
        modalContent.scrollTop = 0;
    }

    // Only scroll to specific section if explicitly requested
    if (specificSection) {
        setTimeout(() => {
            scrollToSection(specificSection);
        }, 300);
    }
}

function closeGuideModal() {
    const modal = document.getElementById('userGuideModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const modalContent = section.closest('.overflow-y-auto');
        if (modalContent) {
            const offset = section.offsetTop - 20;
            modalContent.scrollTo({ top: offset, behavior: 'smooth' });
        }
    }
}

// Close modal when clicking outside
document.getElementById('userGuideModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeGuideModal();
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeGuideModal();
});
</script>
