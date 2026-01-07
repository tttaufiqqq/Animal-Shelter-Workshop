<!-- Book Adoption Modal -->
<div id="bookAdoptionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🐾</span>
                    <div>
                        <h2 class="text-2xl font-bold">Book Adoption Appointment</h2>
                        <p class="text-purple-100 text-sm">Schedule a visit to meet your new companion</p>
                    </div>
                </div>
                <button onclick="closeBookAdoptionModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form id="bookAdoptionForm" method="POST" action="{{ route('adoption.book') }}" class="p-6 space-y-4">
            @csrf
            
            <!-- Hidden Animal ID -->
            <input type="hidden" id="book_animalID" name="animalID" value="">
            
            <!-- Animal Info Display -->
            <div class="bg-purple-50 border-l-4 border-purple-600 p-4 rounded-lg">
                <h3 class="font-bold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-paw text-purple-600 mr-2"></i>
                    Animal Information
                </h3>
                <div class="space-y-1 text-sm">
                    <p><span class="font-semibold text-gray-700">Name:</span> <span id="display_animal_name" class="text-gray-800"></span></p>
                    <p><span class="font-semibold text-gray-700">Species:</span> <span id="display_animal_species" class="text-gray-800"></span></p>
                </div>
            </div>

            <!-- Appointment Date & Time -->
            <div>
               <label for="appointment_date" class="block text-gray-800 font-semibold mb-2">
                  <i class="fas fa-calendar-alt mr-1"></i>Preferred Appointment Date & Time <span class="text-red-600">*</span>
               </label>
               <input type="datetime-local" 
                     id="appointment_date" 
                     name="appointment_date" 
                     min="{{ date('Y-m-d\TH:i') }}"
                     class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" 
                     required>
               
               <!-- Show booked slots info -->
               @if($bookedSlots && $bookedSlots->count() > 0)
                  <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs font-semibold text-yellow-800 mb-2">
                           <i class="fas fa-clock mr-1"></i>Already Booked Time Slots:
                        </p>
                        <div class="space-y-1">
                           @foreach($bookedSlots as $slot)
                              <div class="text-xs text-yellow-700 flex items-center">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    {{ \Carbon\Carbon::parse($slot['datetime'])->format('F d, Y - h:i A') }}
                              </div>
                           @endforeach
                        </div>
                        <p class="text-xs text-yellow-600 mt-2">
                           Please select a different date and time
                        </p>
                  </div>
               @endif
               
               <p class="text-xs text-gray-600 mt-2">
                  <i class="fas fa-info-circle mr-1"></i>
                  Select your preferred date and time for the adoption appointment
               </p>
               <div id="booking-warning" class="hidden mt-2 text-xs text-red-600 bg-red-50 p-2 rounded border border-red-200">
                  <i class="fas fa-exclamation-triangle mr-1"></i>
                  This time slot is already booked. Please select a different time.
               </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    Your Contact Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="user_name" class="block text-gray-700 font-medium mb-2 text-sm">Full Name</label>
                        <input type="text" 
                               id="user_name" 
                               name="user_name" 
                               value="{{ auth()->user()->name ?? '' }}"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm" 
                               readonly>
                    </div>
                    <div>
                        <label for="user_email" class="block text-gray-700 font-medium mb-2 text-sm">Email</label>
                        <input type="email" 
                               id="user_email" 
                               name="user_email" 
                               value="{{ auth()->user()->email ?? '' }}"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm" 
                               readonly>
                    </div>
                    <div>
                        <label for="user_email" class="block text-gray-700 font-medium mb-2 text-sm">Phone Number</label>
                        <input type="email" 
                               id="user_email" 
                               name="user_email" 
                               value="{{ auth()->user()->phoneNum ?? '' }}"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm" 
                               readonly>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    Before You Book
                </h4>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>Make sure you can commit to caring for a pet long-term</li>
                    <li>Consider the costs of pet ownership (food, vet care, etc.)</li>
                    <li>Prepare your home for a new pet</li>
                    <li>Bring a valid ID to your appointment</li>
                </ul>
            </div>

            <!-- Terms & Conditions -->
            <div class="flex items-start">
                <input type="checkbox" 
                       id="terms" 
                       name="terms" 
                       class="mt-1 mr-3 h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500" 
                       required>
                <label for="terms" class="text-sm text-gray-700">
                    I understand that this is a booking request and not a guarantee of adoption. I agree to attend the scheduled appointment and provide necessary documentation. <span class="text-red-600">*</span>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button"
                        onclick="closeBookAdoptionModal()"
                        id="bookAdoptionCancelBtn"
                        class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="submit"
                        id="bookAdoptionSubmitBtn"
                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg flex items-center gap-2">
                    <i class="fas fa-calendar-check" id="bookAdoptionSubmitIcon"></i>
                    <span id="bookAdoptionSubmitText">Book Appointment</span>
                    <svg class="animate-spin h-5 w-5 text-white hidden" id="bookAdoptionSubmitSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Book Adoption Form Loading State
    document.addEventListener('DOMContentLoaded', function() {
        const bookAdoptionForm = document.getElementById('bookAdoptionForm');
        if (bookAdoptionForm) {
            bookAdoptionForm.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('bookAdoptionSubmitBtn');
                const submitText = document.getElementById('bookAdoptionSubmitText');
                const submitIcon = document.getElementById('bookAdoptionSubmitIcon');
                const submitSpinner = document.getElementById('bookAdoptionSubmitSpinner');
                const cancelBtn = document.getElementById('bookAdoptionCancelBtn');

                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:from-purple-700', 'hover:to-purple-800');

                // Hide icon and text, show spinner
                submitIcon.classList.add('hidden');
                submitText.textContent = 'Booking...';
                submitSpinner.classList.remove('hidden');

                // Disable cancel button
                cancelBtn.disabled = true;
                cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');

                // Disable all form inputs
                const inputs = bookAdoptionForm.querySelectorAll('input, select, textarea, button[type="button"]');
                inputs.forEach(input => input.disabled = true);
            });
        }
    });
</script>

<script>
    // Open Book Adoption Modal
    function openBookAdoptionModal(animalId, animalName, animalSpecies) {
        // Set animal information
        document.getElementById('book_animalID').value = animalId;
        document.getElementById('display_animal_name').textContent = animalName;
        document.getElementById('display_animal_species').textContent = animalSpecies;
        
        // Show modal
        document.getElementById('bookAdoptionModal').classList.remove('hidden');
    }

    // Close Book Adoption Modal
    function closeBookAdoptionModal() {
        document.getElementById('bookAdoptionModal').classList.add('hidden');
        document.getElementById('bookAdoptionForm').reset();
    }

    // Close modal when clicking outside
    document.getElementById('bookAdoptionModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeBookAdoptionModal();
        }
    });

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBookAdoptionModal();
        }
    });
               // Store booked slots
               const bookedSlots = @json($bookedSlots ?? []);
               
               // Function to check if a datetime is booked
               function isSlotBooked(selectedDateTime) {
                  return bookedSlots.some(slot => slot.datetime === selectedDateTime);
               }
               
               // Validate appointment time on form submission
               document.getElementById('bookAdoptionForm')?.addEventListener('submit', function(e) {
                  const appointmentInput = document.getElementById('appointment_date');
                  const selectedDateTime = appointmentInput.value;
                  
                  if (isSlotBooked(selectedDateTime)) {
                        e.preventDefault();
                        alert('This time slot is already booked. Please select a different date and time.');
                        appointmentInput.focus();
                        return false;
                  }
               });
               
               // Add real-time validation
               document.addEventListener('DOMContentLoaded', function() {
                  const appointmentInput = document.getElementById('appointment_date');
                  const warningDiv = document.getElementById('booking-warning');
                  const submitButton = document.querySelector('#bookAdoptionForm button[type="submit"]');
                  
                  if (appointmentInput) {
                        appointmentInput.addEventListener('change', function() {
                           const selectedDateTime = this.value;
                           
                           if (isSlotBooked(selectedDateTime)) {
                              // Show warning
                              warningDiv.classList.remove('hidden');
                              // Disable submit button
                              if (submitButton) {
                                    submitButton.disabled = true;
                                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                              }
                              // Highlight input as error
                              this.classList.add('border-red-500', 'bg-red-50');
                           } else {
                              // Hide warning
                              warningDiv.classList.add('hidden');
                              // Enable submit button
                              if (submitButton) {
                                    submitButton.disabled = false;
                                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                              }
                              // Remove error styling
                              this.classList.remove('border-red-500', 'bg-red-50');
                           }
                        });
                  }
               });
</script>