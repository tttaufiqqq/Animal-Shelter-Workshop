<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reportForm');
        const alertContainer = document.getElementById('reportModalAlert');

        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // ==================== VALIDATE CITY AND STATE ARE FILLED ====================
                const cityField = document.getElementById('cityInput');
                const stateField = document.getElementById('stateInput');
                const addressField = document.getElementById('addressInput');

                let validationErrors = [];

                if (!addressField.value) {
                    validationErrors.push('Address is required');
                    addressField.classList.add('border-red-500', 'border-2');
                }

                if (!cityField.value) {
                    validationErrors.push('City is required');
                    cityField.classList.add('border-red-500', 'border-2');
                }

                if (!stateField.value) {
                    validationErrors.push('State is required');
                    stateField.classList.add('border-red-500', 'border-2');
                }

                setTimeout(() => {
                    [addressField, cityField, stateField].forEach(field => {
                        field.classList.remove('border-red-500', 'border-2');
                    });
                }, 3000);

                if (validationErrors.length > 0) {
                    showToast('Please fill all location fields', 'error');
                    showReportModalAlert('error',
                        '<strong>Location information incomplete:</strong><br>' +
                        validationErrors.map(err => `• ${err}`).join('<br>')
                    );
                    return false;
                }

                stateField.disabled = false;

                const lat = document.getElementById('latitudeInput').value;
                const lng = document.getElementById('longitudeInput').value;

                if (!lat || !lng) {
                    document.getElementById('mapError').classList.remove('hidden');
                    showToast('Please select a location on the map', 'error');
                    showReportModalAlert('error', 'Please select a location on the map before submitting.');
                    stateField.disabled = true;
                    return false;
                }

                const submitBtn = document.getElementById('submitBtn');
                const originalBtnContent = submitBtn.innerHTML;
                const cancelBtn = document.getElementById('cancelBtn');

                alertContainer.classList.add('hidden');
                alertContainer.innerHTML = '';

                submitBtn.disabled = true;
                cancelBtn.disabled = true;

                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Submitting Report...</span>
                `;

                showToast('Submitting your report...', 'info');

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showReportModalAlert('success', data.message || 'Report submitted successfully!');
                        showToast('Report submitted successfully!', 'success');

                        form.reset();
                        document.getElementById('imagePreview').innerHTML = '';
                        if (marker) map.removeLayer(marker);
                        if (circle) map.removeLayer(circle);
                        document.getElementById('latitudeInput').value = '';
                        document.getElementById('longitudeInput').value = '';

                        setTimeout(() => {
                            alertContainer.classList.add('hidden');
                            closeReportModal();
                        }, 2000);
                    } else {
                        let errorMessage = data.message || 'An error occurred while submitting your report.';

                        if (data.errors) {
                            errorMessage = '<ul class="list-disc list-inside">';
                            for (const field in data.errors) {
                                data.errors[field].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            }
                            errorMessage += '</ul>';
                        }

                        showReportModalAlert('error', errorMessage);
                        showToast('Please fix the errors and try again', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showReportModalAlert('error', 'Network error. Please check your connection and try again.');
                    showToast('Network error. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    cancelBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                }
            });
        }
    });
</script>
