<div id="adopterModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    @php
        // Helper function to safely get a value from the profile data or old input after failure
    $getProfileValue = fn ($key) => optional($adopterProfile)->{$key} ?? old($key);
    @endphp

    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">

        <div class="sticky top-0 bg-purple-600 text-white p-6 rounded-t-2xl flex justify-between items-center">
            <h2 class="text-2xl font-bold flex items-center">
                <i class="fas fa-user-circle mr-3"></i>
                Adopter Profile
            </h2>
            <button type="button" onclick="closeAdopterModal()" class="hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Alert Container (Hidden by default) -->
        <div id="adopterModalAlert" class="hidden mx-6 mt-6"></div>

        <form id="adopterProfileForm" action="{{ route('adopter.profile.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- Housing Type -->
            <div>
                <label for="housing_type" class="block text-sm font-semibold text-gray-700 mb-2">Housing Type</label>
                <select id="housing_type" name="housing_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select housing type</option>
                    <option value="condo" @selected($getProfileValue('housing_type') == 'condo')>Condo</option>
                    <option value="landed" @selected($getProfileValue('housing_type') == 'landed')>Landed Property</option>
                    <option value="apartment" @selected($getProfileValue('housing_type') == 'apartment')>Apartment</option>
                    <option value="hdb" @selected($getProfileValue('housing_type') == 'hdb')>HDB</option>
                </select>
            </div>

            <!-- Has Children (Uses strict comparison for 1/0 boolean types) -->
            <div>
                <label for="has_children" class="block text-sm font-semibold text-gray-700 mb-2">Do you have children?</label>
                <select id="has_children" name="has_children" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select option</option>
                    {{-- Check against both integer 1 and string '1' for robustness --}}
                    <option value="1" @selected($getProfileValue('has_children') == 1)>Yes</option>
                    <option value="0" @selected($getProfileValue('has_children') == 0 && $getProfileValue('has_children') !== null)>No</option>
                </select>
            </div>

            <!-- Has Other Pets -->
            <div>
                <label for="has_other_pets" class="block text-sm font-semibold text-gray-700 mb-2">Do you have other pets?</label>
                <select id="has_other_pets" name="has_other_pets" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select option</option>
                    <option value="1" @selected($getProfileValue('has_other_pets') == 1)>Yes</option>
                    <option value="0" @selected($getProfileValue('has_other_pets') == 0 && $getProfileValue('has_other_pets') !== null)>No</option>
                </select>
            </div>

            <!-- Activity Level -->
            <div>
                <label for="activity_level" class="block text-sm font-semibold text-gray-700 mb-2">Activity Level</label>
                <select id="activity_level" name="activity_level" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select activity level</option>
                    <option value="low" @selected($getProfileValue('activity_level') == 'low')>Low - Prefer quiet, relaxed activities</option>
                    <option value="medium" @selected($getProfileValue('activity_level') == 'medium')>Medium - Moderate exercise and play</option>
                    <option value="high" @selected($getProfileValue('activity_level') == 'high')>High - Very active, lots of outdoor time</option>
                </select>
            </div>

            <!-- Experience -->
            <div>
                <label for="experience" class="block text-sm font-semibold text-gray-700 mb-2">Pet Experience</label>
                <select id="experience" name="experience" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select experience level</option>
                    <option value="beginner" @selected($getProfileValue('experience') == 'beginner')>Beginner</option>
                    <option value="intermediate" @selected($getProfileValue('experience') == 'intermediate')>Intermediate</option>
                    <option value="expert" @selected($getProfileValue('experience') == 'expert')>Expert</option>
                </select>
            </div>

            <!-- Preferred Species -->
            <div>
                <label for="preferred_species" class="block text-sm font-semibold text-gray-700 mb-2">Preferred Species</label>
                <select id="preferred_species" name="preferred_species" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select species</option>
                    <option value="cat" @selected($getProfileValue('preferred_species') == 'cat')>Cat</option>
                    <option value="dog" @selected($getProfileValue('preferred_species') == 'dog')>Dog</option>
                    <option value="both" @selected($getProfileValue('preferred_species') == 'both')>No preference</option>
                </select>
            </div>

            <!-- Preferred Size -->
            <div>
                <label for="preferred_size" class="block text-sm font-semibold text-gray-700 mb-2">Preferred Size</label>
                <select id="preferred_size" name="preferred_size" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select size</option>
                    <option value="small" @selected($getProfileValue('preferred_size') == 'small')>Small</option>
                    <option value="medium" @selected($getProfileValue('preferred_size') == 'medium')>Medium</option>
                    <option value="large" @selected($getProfileValue('preferred_size') == 'large')>Large</option>
                    <option value="any" @selected($getProfileValue('preferred_size') == 'any')>No preference</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeAdopterModal()" id="cancelAdopterBtn" class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" id="saveAdopterBtn" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg flex items-center gap-2 justify-center">
                    <span id="saveAdopterBtnText">Save Profile</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Loading spinner animation */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>

<script>
    // Function to open the modal
    function openAdopterModal() {
        document.getElementById('adopterModal').classList.remove('hidden');
        document.getElementById('adopterModal').classList.add('flex');
    }

    // Function to close the modal
    function closeAdopterModal() {
        document.getElementById('adopterModal').classList.add('hidden');
        document.getElementById('adopterModal').classList.remove('flex');
    }

    // Handle form submission with AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const adopterForm = document.getElementById('adopterProfileForm');
        const alertContainer = document.getElementById('adopterModalAlert');

        if (adopterForm) {
            adopterForm.addEventListener('submit', async function(e) {
                e.preventDefault(); // Prevent default form submission

                const saveBtn = document.getElementById('saveAdopterBtn');
                const cancelBtn = document.getElementById('cancelAdopterBtn');
                const originalBtnText = saveBtn.innerHTML;

                // Hide any existing alerts
                alertContainer.classList.add('hidden');
                alertContainer.innerHTML = '';

                // Disable both buttons
                saveBtn.disabled = true;
                cancelBtn.disabled = true;

                // Show loading spinner
                saveBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Saving Profile...</span>
                `;

                try {
                    const formData = new FormData(adopterForm);
                    const response = await fetch(adopterForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Show success message
                        showModalAlert('success', data.message || 'Profile saved successfully!');

                        // Auto-hide success message after 3 seconds
                        setTimeout(() => {
                            alertContainer.classList.add('hidden');
                        }, 3000);
                    } else {
                        // Show error message
                        let errorMessage = data.message || 'An error occurred while saving your profile.';

                        // Handle validation errors
                        if (data.errors) {
                            errorMessage = '<ul class="list-disc list-inside">';
                            for (const field in data.errors) {
                                data.errors[field].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            }
                            errorMessage += '</ul>';
                        }

                        showModalAlert('error', errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showModalAlert('error', 'Network error. Please check your connection and try again.');
                } finally {
                    // Re-enable buttons and restore original text
                    saveBtn.disabled = false;
                    cancelBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnText;
                }
            });
        }
    });

    function showModalAlert(type, message) {
        const alertContainer = document.getElementById('adopterModalAlert');
        const isSuccess = type === 'success';

        alertContainer.innerHTML = `
            <div class="flex items-start gap-3 p-4 bg-${isSuccess ? 'green' : 'red'}-50 border border-${isSuccess ? 'green' : 'red'}-200 rounded-xl shadow-sm">
                <svg class="w-6 h-6 text-${isSuccess ? 'green' : 'red'}-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    ${isSuccess
                        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />'
                        : '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />'
                    }
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-${isSuccess ? 'green' : 'red'}-700">${message}</p>
                </div>
                <button onclick="document.getElementById('adopterModalAlert').classList.add('hidden')" class="text-${isSuccess ? 'green' : 'red'}-600 hover:text-${isSuccess ? 'green' : 'red'}-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        alertContainer.classList.remove('hidden');

        // Scroll to alert
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
</script>