<script>
// Dynamic Age Categories
document.addEventListener('DOMContentLoaded', function () {
    const speciesSelect = document.getElementById('species');
    const ageSelect = document.getElementById('age_category');

    function updateAgeCategories() {
        const species = speciesSelect.value;
        ageSelect.innerHTML = '<option value="" disabled selected>Select age category</option>';

        let ageOptions = [];
        if (species === 'Cat') {
            ageOptions = ["kitten", "adult", "senior"];
        } else if (species === 'Dog') {
            ageOptions = ["puppy", "adult", "senior"];
        }

        ageOptions.forEach(age => {
            let option = document.createElement("option");
            option.value = age;
            option.textContent = age.charAt(0).toUpperCase() + age.slice(1);
            ageSelect.appendChild(option);
        });
    }

    speciesSelect.addEventListener('change', updateAgeCategories);
    updateAgeCategories();

    // Image Preview
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function() {
        imagePreview.innerHTML = '';
        const files = Array.from(this.files);

        if (files.length > 0) {
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview relative group';
                    div.innerHTML = `
                        <img src="${e.target.result}"
                             class="w-full h-24 object-cover rounded-xl border-2 border-purple-200 shadow-md"
                             alt="Preview ${index + 1}">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition rounded-xl flex items-center justify-center">
                            <span class="text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition">
                                Image ${index + 1}
                            </span>
                        </div>
                    `;
                    imagePreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // Form Submission
    const form = document.getElementById('animalForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    form.addEventListener('submit', function(e) {
        // Validation
        const requiredFields = form.querySelectorAll('[required]');
        let allValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                allValid = false;
                field.classList.add('border-red-500', 'border-2');
                field.classList.remove('border-gray-200');
            } else {
                field.classList.remove('border-red-500');
                field.classList.add('border-gray-200');
            }
        });

        if (!allValid) {
            e.preventDefault();

            // Scroll to first error
            const firstError = form.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }

            // Show alert
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-xl shadow-2xl z-50 fade-in';
            alertDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Please fill in all required fields';
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);

            return false;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Adding Animal...';
        submitSpinner.classList.remove('hidden');
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    });

    // Remove error styling on input
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-200');
            }
        });
    });
});
</script>

</body>
</html>
