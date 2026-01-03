<div id="animalModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    @php
        // Helper function to safely get a value from the profile data or old input after failure
        $getProfileValue = fn ($key) => optional($animalProfile)->{$key} ?? old($key);
    @endphp

    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">

        <div class="sticky top-0 bg-purple-500 text-white p-6 rounded-t-2xl flex justify-between items-center">
            <h2 class="text-2xl font-bold flex items-center">
                <i class="fas fa-paw mr-3"></i> 
                Animal Profile
            </h2>
            <button type="button" onclick="closeAnimalModal()" class="hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('animal.profile.store', ['animal' => $animal->id]) }}" method="POST" class="p-6 space-y-5">
            @csrf
            
            {{-- Optionally include a hidden field for the Animal Profile ID if updating --}}
            @if ($animalProfile)
                <input type="hidden" name="profile_id" value="{{ $animalProfile->id }}">
            @endif

            <!-- Size -->
            <div>
                <label for="size" class="block text-sm font-semibold text-gray-700 mb-2">Size</label>
                <select id="size" name="size" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select size</option>
                    <option value="small" @selected($getProfileValue('size') == 'small')>Small (Under 10kg)</option>
                    <option value="medium" @selected($getProfileValue('size') == 'medium')>Medium (10-25kg)</option>
                    <option value="large" @selected($getProfileValue('size') == 'large')>Large (Over 25kg)</option>
                </select>
            </div>

            <!-- Energy Level -->
            <div>
                <label for="energy_level" class="block text-sm font-semibold text-gray-700 mb-2">Energy Level</label>
                <select id="energy_level" name="energy_level" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select energy level</option>
                    <option value="low" @selected($getProfileValue('energy_level') == 'low')>Low - Calm and relaxed</option>
                    <option value="medium" @selected($getProfileValue('energy_level') == 'medium')>Medium - Moderately active</option>
                    <option value="high" @selected($getProfileValue('energy_level') == 'high')>High - Very energetic and playful</option>
                </select>
            </div>

            <!-- Good with Kids -->
            <div>
                <label for="good_with_kids" class="block text-sm font-semibold text-gray-700 mb-2">Good with Kids?</label>
                <select id="good_with_kids" name="good_with_kids" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select option</option>
                    <option value="1" @selected($getProfileValue('good_with_kids') == 1)>Yes</option>
                    <option value="0" @selected($getProfileValue('good_with_kids') == 0 && $getProfileValue('good_with_kids') !== null)>No</option>
                </select>
            </div>

            <!-- Good with Other Pets -->
            <div>
                <label for="good_with_pets" class="block text-sm font-semibold text-gray-700 mb-2">Good with Other Pets?</label>
                <select id="good_with_pets" name="good_with_pets" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select option</option>
                    <option value="1" @selected($getProfileValue('good_with_pets') == 1)>Yes</option>
                    <option value="0" @selected($getProfileValue('good_with_pets') == 0 && $getProfileValue('good_with_pets') !== null)>No</option>
                </select>
            </div>

            <!-- Temperament -->
            <div>
                <label for="temperament" class="block text-sm font-semibold text-gray-700 mb-2">Temperament</label>
                <select id="temperament" name="temperament" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select temperament</option>
                    <option value="calm" @selected($getProfileValue('temperament') == 'calm')>Calm</option>
                    <option value="active" @selected($getProfileValue('temperament') == 'active')>Active</option>
                    <option value="shy" @selected($getProfileValue('temperament') == 'shy')>Shy</option>
                    <option value="friendly" @selected($getProfileValue('temperament') == 'friendly')>Friendly</option>
                    <option value="independent" @selected($getProfileValue('temperament') == 'independent')>Independent</option>
                </select>
            </div>

            <!-- Medical Needs -->
            <div>
                <label for="medical_needs" class="block text-sm font-semibold text-gray-700 mb-2">Medical Needs</label>
                <select id="medical_needs" name="medical_needs" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white">
                    <option value="">Select medical needs</option>
                    <option value="none" @selected($getProfileValue('medical_needs') == 'none')>None</option>
                    <option value="minor" @selected($getProfileValue('medical_needs') == 'minor')>Minor</option>
                    <option value="moderate" @selected($getProfileValue('medical_needs') == 'moderate')>Moderate</option>
                    <option value="special" @selected($getProfileValue('medical_needs') == 'special')>Special</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeAnimalModal()" id="animalProfileCancelBtn" class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" id="animalProfileSubmitBtn" class="flex-1 px-6 py-3 bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600 transition shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-save" id="animalProfileSubmitIcon"></i>
                    <span id="animalProfileSubmitText">Save Profile</span>
                    <svg class="animate-spin h-5 w-5 text-white hidden" id="animalProfileSubmitSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to open the modal
    function openAnimalModal() {
        document.getElementById('animalModal').classList.remove('hidden');
        document.getElementById('animalModal').classList.add('flex');
    }

    // Function to close the modal
    function closeAnimalModal() {
        document.getElementById('animalModal').classList.add('hidden');
        document.getElementById('animalModal').classList.remove('flex');
    }

    // Animal Profile Form Loading State
    document.addEventListener('DOMContentLoaded', function() {
        const animalProfileForm = document.querySelector('#animalModal form');
        if (animalProfileForm) {
            animalProfileForm.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('animalProfileSubmitBtn');
                const submitText = document.getElementById('animalProfileSubmitText');
                const submitIcon = document.getElementById('animalProfileSubmitIcon');
                const submitSpinner = document.getElementById('animalProfileSubmitSpinner');
                const cancelBtn = document.getElementById('animalProfileCancelBtn');

                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-purple-600');

                // Hide icon and text, show spinner
                submitIcon.classList.add('hidden');
                submitText.textContent = 'Saving...';
                submitSpinner.classList.remove('hidden');

                // Disable cancel button
                cancelBtn.disabled = true;
                cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');

                // Disable all form inputs
                const inputs = animalProfileForm.querySelectorAll('input, select, textarea');
                inputs.forEach(input => input.disabled = true);
            });
        }
    });
</script>