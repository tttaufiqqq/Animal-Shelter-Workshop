<!-- IMPROVED VISIT LIST MODAL -->
<div id="visitModal"
     class="fixed inset-0 hidden bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 transition-opacity duration-300">

    <div id="visitModalContent"
         class="bg-white max-w-4xl w-full rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col
                opacity-0 scale-95 transform transition-all duration-300">

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
            <div class="relative flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold flex items-center gap-2">
                        <i class="fas fa-heart"></i>
                        Your Visit List
                    </h1>
                    <p class="text-purple-100 text-sm mt-1">Schedule a visit with your favorite animals</p>
                </div>
                <button onclick="closeVisitModal()"
                        class="text-white/80 hover:text-white text-3xl w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/20 transition-all duration-200">
                    &times;
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 p-6">
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6 animate-slideIn">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
                        <div class="flex-1">
                            <p class="font-semibold mb-2">Please fix the following errors:</p>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if ($animalList->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-purple-100 rounded-full mb-6">
                        <i class="fas fa-heart-broken text-purple-400 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Your visit list is empty</h3>
                    <p class="text-gray-600 mb-8">Start adding animals you'd like to meet!</p>
                    <button onclick="closeVisitModal()"
                            class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-all duration-200 transform hover:scale-105">
                        Browse Animals
                    </button>
                </div>
            @else
                <form method="POST" action="{{ route('visit.list.confirm') }}" class="space-y-6" id="visitListForm">
                    @csrf

                    <!-- Animals Counter -->
                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-center gap-3">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ $animalList->count() }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $animalList->count() }} {{ Str::plural('Animal', $animalList->count()) }} Selected
                            </p>
                            <p class="text-sm text-gray-600">Ready for your visit appointment</p>
                        </div>
                    </div>

                    <!-- Selected Animals Grid -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-paw text-purple-600"></i>
                            Animals You'll Visit
                        </h2>

                        <div class="grid grid-cols-1 gap-4">
                            @foreach($animalList as $index => $animal)
                                <div class="group bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 hover:border-purple-300 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg">
                                    <div class="flex gap-4">
                                        <!-- Animal Image -->
                                        <div class="flex-shrink-0">
                                            <div class="w-24 h-24 rounded-xl overflow-hidden bg-gray-200 ring-4 ring-purple-100 group-hover:ring-purple-300 transition-all duration-300">
                                                @if($animal->images && $animal->images->count() > 0)
                                                    <img src="{{ $animal->images->first()->url }}"
                                                         alt="{{ $animal->name }}"
                                                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="fas fa-paw text-gray-400 text-3xl"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Animal Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                                        {{ $animal->name }}
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            #{{ $index + 1 }}
                                                        </span>
                                                    </h3>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm">
                                                            <i class="fas fa-dog"></i>
                                                            {{ $animal->species }}
                                                        </span>
                                                        @if($animal->breed)
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm">
                                                                <i class="fas fa-dna"></i>
                                                                {{ $animal->breed }}
                                                            </span>
                                                        @endif
                                                        @if($animal->age)
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-50 text-orange-700 rounded-full text-sm">
                                                                <i class="fas fa-calendar"></i>
                                                                {{ $animal->age }}
                                                            </span>
                                                        @endif
                                                        @if($animal->gender)
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-50 text-pink-700 rounded-full text-sm">
                                                                <i class="fas fa-{{ $animal->gender == 'Male' ? 'mars' : 'venus' }}"></i>
                                                                {{ $animal->gender }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Remove Button (No Form) -->
                                                <button type="button"
                                                        onclick="openRemoveConfirmModal({{ $animal->id }}, '{{ $animal->name }}')"
                                                        class="text-red-500 hover:text-white hover:bg-red-500 p-2.5 rounded-lg transition-all duration-200 flex items-center gap-2 group/btn border border-red-200 hover:border-red-500">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <span class="text-sm font-medium hidden sm:inline">Remove</span>
                                                </button>
                                            </div>

                                            <!-- Remarks Input -->
                                            <div class="mt-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                    <i class="fas fa-comment-dots text-purple-600 mr-1"></i>
                                                    Why are you interested in {{ $animal->name }}?
                                                </label>
                                                <textarea name="remarks[{{ $animal->id }}]"
                                                          placeholder="Tell us what makes {{ $animal->name }} special to you..."
                                                          class="w-full border-2 border-gray-200 focus:border-purple-400 focus:ring-2 focus:ring-purple-200 text-gray-700 rounded-lg p-3 text-sm transition-all duration-200 resize-none"
                                                          rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden input for booking -->
                                    <input type="hidden" name="animal_ids[]" value="{{ $animal->id }}">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Appointment Section -->
                    <div class="bg-gradient-to-br from-purple-50 to-blue-50 border-2 border-purple-200 rounded-2xl p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-calendar-check text-purple-600"></i>
                            Schedule Your Visit
                            <span class="text-red-500 text-sm">*</span>
                        </h2>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Date Input -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-calendar text-purple-600 mr-1"></i>
                                        Preferred Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date"
                                           id="appointmentDate"
                                           name="appointment_date"
                                           required
                                           min="{{ date('Y-m-d') }}"
                                           class="w-full border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-gray-700 rounded-xl p-3.5 text-base transition-all duration-200">
                                </div>

                                <!-- Time Select Dropdown -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-clock text-purple-600 mr-1"></i>
                                        Preferred Time <span class="text-red-500">*</span>
                                    </label>
                                    <select id="appointmentTime"
                                            name="appointment_time"
                                            required
                                            class="w-full border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-gray-700 rounded-xl p-3.5 text-base transition-all duration-200">
                                        <option value="">Select a time</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="09:30">9:30 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="10:30">10:30 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="11:30">11:30 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="12:30">12:30 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="13:30">1:30 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="14:30">2:30 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="15:30">3:30 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="16:30">4:30 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 mt-2">
                                <i class="fas fa-info-circle"></i>
                                Our adoption center is open Monday-Saturday, 9 AM - 5 PM
                            </p>
                            <p id="appointmentError" class="text-xs text-red-600 mt-2 hidden">
                                <i class="fas fa-exclamation-circle"></i>
                                Please select a date and time for your visit
                            </p>
                        </div>

                        <!-- Terms Checkbox -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-purple-200 mt-4">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox"
                                       name="terms"
                                       required
                                       class="mt-1 w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                <span class="text-sm text-gray-700 flex-1">
                                        <span class="font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">I understand and agree</span>
                                        <br>
                                        This is a visit appointment request pending staff approval. You will be notified once confirmed.
                                    </span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button"
                                onclick="closeVisitModal()"
                                id="visitCancelBtn"
                                class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 border-2 border-gray-200">
                            <i class="fas fa-arrow-left"></i>
                            Continue Browsing
                        </button>
                        <button type="submit"
                                id="confirmBookingBtn"
                                disabled
                                class="flex-1 px-6 py-4 bg-gray-300 text-gray-500 font-bold rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2 cursor-not-allowed">
                            <i class="fas fa-check-circle" id="visitSubmitIcon"></i>
                            <span id="visitSubmitText">Confirm Visit Booking</span>
                            <svg class="animate-spin h-5 w-5 text-white hidden" id="visitSubmitSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Hidden form for removing animals -->
<form id="removeAnimalForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Remove Confirmation Modal -->
<div id="removeConfirmModal" class="fixed inset-0 hidden bg-black/50 backdrop-blur-sm z-[10000] flex items-center justify-center p-4 transition-opacity duration-300">
    <div id="removeConfirmContent" class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 opacity-0 scale-95">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 rounded-t-2xl">
            <div class="flex items-center gap-3 text-white">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Remove Animal</h3>
                    <p class="text-red-100 text-sm">Are you sure about this?</p>
                </div>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base mb-2">
                You are about to remove <strong id="removeAnimalName" class="text-gray-900"></strong> from your visit list.
            </p>
            <p class="text-gray-600 text-sm">
                This action cannot be undone. You can always add the animal back to your list later.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex gap-3">
            <button type="button"
                    id="removeModalCancelBtn"
                    onclick="closeRemoveConfirmModal()"
                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200 border border-gray-200">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button type="button"
                    id="removeModalConfirmBtn"
                    onclick="confirmRemoveAnimal()"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-trash-alt mr-2" id="removeIcon"></i>
                <span id="removeText">Remove</span>
                <svg class="animate-spin h-5 w-5 text-white hidden inline-block ml-2" id="removeSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    // Set base URL for remove action (using route helper)
    const removeBaseUrl = "{{ url('visit-list/remove') }}/";

    // Store animal info for removal
    let pendingRemoveAnimalId = null;
    let pendingRemoveAnimalName = '';

    // Open remove confirmation modal
    function openRemoveConfirmModal(animalId, animalName) {
        pendingRemoveAnimalId = animalId;
        pendingRemoveAnimalName = animalName;

        const modal = document.getElementById('removeConfirmModal');
        const content = document.getElementById('removeConfirmContent');
        const animalNameElement = document.getElementById('removeAnimalName');

        // Reset modal state (in case it was left in loading state)
        const confirmBtn = document.getElementById('removeModalConfirmBtn');
        const cancelBtn = document.getElementById('removeModalCancelBtn');
        const removeIcon = document.getElementById('removeIcon');
        const removeText = document.getElementById('removeText');
        const removeSpinner = document.getElementById('removeSpinner');

        confirmBtn.disabled = false;
        cancelBtn.disabled = false;
        confirmBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        confirmBtn.classList.add('hover:from-red-600', 'hover:to-red-700', 'hover:shadow-xl');
        cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        removeIcon.classList.remove('hidden');
        removeText.textContent = 'Remove';
        removeSpinner.classList.add('hidden');

        animalNameElement.textContent = animalName;

        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    // Close remove confirmation modal
    function closeRemoveConfirmModal() {
        const modal = document.getElementById('removeConfirmModal');
        const content = document.getElementById('removeConfirmContent');

        content.classList.add('opacity-0', 'scale-95');
        content.classList.remove('opacity-100', 'scale-100');

        setTimeout(() => {
            modal.classList.add('hidden');
            pendingRemoveAnimalId = null;
            pendingRemoveAnimalName = '';
        }, 300);
    }

    // Confirm and submit removal
    function confirmRemoveAnimal() {
        if (pendingRemoveAnimalId) {
            // Show loading state
            const confirmBtn = document.getElementById('removeModalConfirmBtn');
            const cancelBtn = document.getElementById('removeModalCancelBtn');
            const removeIcon = document.getElementById('removeIcon');
            const removeText = document.getElementById('removeText');
            const removeSpinner = document.getElementById('removeSpinner');

            // Disable buttons
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            confirmBtn.classList.add('opacity-75', 'cursor-not-allowed');
            confirmBtn.classList.remove('hover:from-red-600', 'hover:to-red-700', 'hover:shadow-xl');
            cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // Show loading state
            removeIcon.classList.add('hidden');
            removeText.textContent = 'Removing...';
            removeSpinner.classList.remove('hidden');

            // Submit form
            const form = document.getElementById('removeAnimalForm');
            form.action = removeBaseUrl + pendingRemoveAnimalId;
            form.submit();
        }
    }

    // Open / Close Modal
    function openVisitModal() {
        const modal = document.getElementById('visitModal');
        const content = document.getElementById('visitModalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeVisitModal() {
        const modal = document.getElementById('visitModal');
        const content = document.getElementById('visitModalContent');
        content.classList.add('opacity-0', 'scale-95');
        content.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Enable / Disable Confirm Button
    function updateConfirmButton() {
        const appointmentDate = document.getElementById('appointmentDate');
        const appointmentTime = document.getElementById('appointmentTime');
        const termsCheckbox = document.querySelector('input[name="terms"]');
        const confirmBtn = document.getElementById('confirmBookingBtn');

        if (!appointmentDate || !appointmentTime || !termsCheckbox) return;

        const isValid = appointmentDate.value.trim() !== '' &&
            appointmentTime.value.trim() !== '' &&
            termsCheckbox.checked;

        confirmBtn.disabled = !isValid;
        if (isValid) {
            confirmBtn.classList.remove('bg-gray-300','text-gray-500','cursor-not-allowed');
            confirmBtn.classList.add('bg-gradient-to-r','from-purple-600','to-purple-700','hover:from-purple-700','hover:to-purple-800','text-white','hover:shadow-xl','transform','hover:scale-105','cursor-pointer');
        } else {
            confirmBtn.classList.add('bg-gray-300','text-gray-500','cursor-not-allowed');
            confirmBtn.classList.remove('bg-gradient-to-r','from-purple-600','to-purple-700','hover:from-purple-700','hover:to-purple-800','text-white','hover:shadow-xl','transform','hover:scale-105','cursor-pointer');
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('visitListForm');
        const appointmentDate = document.getElementById('appointmentDate');
        const appointmentTime = document.getElementById('appointmentTime');
        const termsCheckbox = document.querySelector('input[name="terms"]');

        if(appointmentDate){
            appointmentDate.addEventListener('input', updateConfirmButton);
            appointmentDate.addEventListener('change', updateConfirmButton);
        }
        if(appointmentTime){
            appointmentTime.addEventListener('change', updateConfirmButton);
        }
        if(termsCheckbox){
            termsCheckbox.addEventListener('change', updateConfirmButton);
        }

        if(form) {
            form.addEventListener('submit', function(e){
                if(!appointmentDate.value || !appointmentTime.value || !termsCheckbox.checked){
                    e.preventDefault();
                    alert('Please select a date, time and accept terms.');
                    if(!appointmentDate.value) {
                        appointmentDate.focus();
                    } else if(!appointmentTime.value) {
                        appointmentTime.focus();
                    }
                } else {
                    // Show loading state
                    const submitBtn = document.getElementById('confirmBookingBtn');
                    const submitText = document.getElementById('visitSubmitText');
                    const submitIcon = document.getElementById('visitSubmitIcon');
                    const submitSpinner = document.getElementById('visitSubmitSpinner');
                    const cancelBtn = document.getElementById('visitCancelBtn');

                    // Disable button and show loading state
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75');
                    submitBtn.classList.remove('hover:from-purple-700', 'hover:to-purple-800', 'hover:shadow-xl', 'hover:scale-105');

                    // Hide icon and text, show spinner
                    submitIcon.classList.add('hidden');
                    submitText.textContent = 'Processing...';
                    submitSpinner.classList.remove('hidden');

                    // Disable cancel button
                    cancelBtn.disabled = true;
                    cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');

                    // Disable all form inputs
                    const inputs = form.querySelectorAll('input, select, textarea, button[type="button"]');
                    inputs.forEach(input => input.disabled = true);
                }
            });
        }

        // Initial check
        updateConfirmButton();
    });

    // Close modal on click outside
    document.addEventListener('click', function(e){
        const modal = document.getElementById('visitModal');
        if(!modal.classList.contains('hidden') && e.target === modal){
            closeVisitModal();
        }

        // Also handle remove confirmation modal
        const removeModal = document.getElementById('removeConfirmModal');
        if(!removeModal.classList.contains('hidden') && e.target === removeModal){
            closeRemoveConfirmModal();
        }
    });

    // Close modal on Escape
    document.addEventListener('keydown', function(e){
        if(e.key === "Escape"){
            const removeModal = document.getElementById('removeConfirmModal');
            if(!removeModal.classList.contains('hidden')){
                closeRemoveConfirmModal();
            } else {
                closeVisitModal();
            }
        }
    });

    // Auto-open modal if session flag
    @if (session('open_visit_modal'))
    document.addEventListener("DOMContentLoaded", function () {
        openVisitModal();
    });
    @endif
</script>
