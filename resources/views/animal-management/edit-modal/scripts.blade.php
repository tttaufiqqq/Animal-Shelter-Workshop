{{-- JavaScript for Image Preview --}}
<script>
    function previewNewImages(event) {
        const files = event.target.files;
        const container = document.getElementById('newImagePreviewContainer');
        const grid = document.getElementById('newImagePreviewGrid');

        if (files.length === 0) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        grid.innerHTML = '';

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative group rounded-xl overflow-hidden shadow-lg border-2 border-purple-300 hover:border-purple-500 transition-all';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-36 object-cover transition-transform group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
                            <p class="text-white text-xs font-semibold truncate">
                                <i class="fas fa-file-image mr-1"></i>
                                ${file.name}
                            </p>
                        </div>
                        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg">
                            <i class="fas fa-check"></i> New
                        </div>
                    `;
                    grid.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function clearNewImages() {
        const input = document.getElementById('newImageInput');
        const container = document.getElementById('newImagePreviewContainer');
        const grid = document.getElementById('newImagePreviewGrid');

        input.value = '';
        grid.innerHTML = '';
        container.classList.add('hidden');
    }

    // Form submission loading state
    document.getElementById('editAnimalForm').addEventListener('submit', function(e) {
        const submitButton = document.getElementById('submitButton');
        const form = document.getElementById('editAnimalForm');

        // Disable submit button and show loading state
        submitButton.disabled = true;
        submitButton.classList.add('opacity-75', 'cursor-not-allowed');
        submitButton.classList.remove('hover:from-purple-700', 'hover:via-purple-800', 'hover:to-purple-900', 'hover:scale-105');

        submitButton.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Updating...</span>
        `;

        // Disable cancel button
        const cancelButtons = form.querySelectorAll('button[type="button"]');
        cancelButtons.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // Disable all form inputs
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => input.disabled = true);
    });
</script>

<script>
    function openEditModal(animalId) {
        const modal = document.getElementById('editAnimalModal-' + animalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEditModal(animalId) {
        const modal = document.getElementById('editAnimalModal-' + animalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal when clicking outside the white dialog
    document.addEventListener('click', function(event) {
        if (event.target.id && event.target.id.startsWith('editAnimalModal-')) {
            closeEditModal(event.target.id.split('-')[1]);
        }
    });
</script>
