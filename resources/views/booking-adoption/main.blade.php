<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
@include('navbar')

<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-5xl font-bold mb-4">My Bookings</h1>
                <p class="text-xl text-purple-100">View and manage your appointment bookings for adoptions</p>
            </div>
            @unless($bookings->isEmpty())
                <div class="mt-6 md:mt-0">
                    <a href="{{ route('animal:main') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300 shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>New Booking</span>
                    </a>
                </div>
            @endunless
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-8 shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-lg mb-8 shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-5xl mb-4">📅</div>
            <p class="text-4xl font-bold text-purple-700 mb-2">{{ $bookings->total() }}</p>
            <p class="text-gray-600">Total Bookings</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-5xl mb-4">⏳</div>
            <p class="text-4xl font-bold text-yellow-600 mb-2">{{ $statusCounts['Pending'] ?? 0 }}</p>
            <p class="text-gray-600">Pending</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-5xl mb-4">✅</div>
            <p class="text-4xl font-bold text-blue-600 mb-2">{{ $statusCounts['Confirmed'] ?? 0 }}</p>
            <p class="text-gray-600">Confirmed</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-5xl mb-4">🎉</div>
            <p class="text-4xl font-bold text-green-600 mb-2">{{ $statusCounts['Completed'] ?? 0 }}</p>
            <p class="text-gray-600">Completed</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-5xl mb-4">❌</div>
            <p class="text-4xl font-bold text-red-600 mb-2">{{ $statusCounts['Cancelled'] ?? 0 }}</p>
            <p class="text-gray-600">Cancelled</p>
        </div>
    </div>

    @if($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <div class="mb-6">
                <svg class="w-32 h-32 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-700 mb-3">No Bookings Yet</h3>
            <p class="text-gray-500 text-lg mb-8">You haven't made any bookings. Start by creating your first appointment!</p>
            <a href="{{ route('animal:main') }}" class="inline-flex items-center gap-2 bg-purple-700 hover:bg-purple-800 text-white px-8 py-3 rounded-lg font-semibold transition duration-300 shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create First Booking</span>
            </a>
        </div>
    @else
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Your Appointments</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($bookings as $booking)
                @php
                    $statusKey = strtolower($booking->status);
                    $statusColors = [
                        'pending' => 'from-yellow-300 to-yellow-400',
                        'confirmed' => 'from-blue-300 to-blue-400',
                        'completed' => 'from-green-300 to-green-400',
                        'cancelled' => 'from-red-300 to-red-400',
                    ];
                    $statusEmojis = [
                        'pending' => '⏳',
                        'confirmed' => '✅',
                        'completed' => '🎉',
                        'cancelled' => '❌',
                    ];
                    $badgeColors = [
                        'pending' => 'bg-yellow-500',
                        'confirmed' => 'bg-blue-500',
                        'completed' => 'bg-green-500',
                        'cancelled' => 'bg-red-500',
                    ];
                @endphp
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                    <div class="relative">
                        <div class="h-32 bg-gradient-to-br {{ $statusColors[$statusKey] ?? 'from-gray-300 to-gray-400' }} flex items-center justify-center">
                            <span class="text-7xl">{{ $statusEmojis[$statusKey] ?? '📅' }}</span>
                        </div>
                        <div class="absolute top-4 right-4 {{ $badgeColors[$statusKey] ?? 'bg-gray-500' }} text-white px-3 py-1 rounded-full text-sm font-semibold">
                            {{ ucfirst($booking->status) }}
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-800">Booking #{{ $booking->id }}</h3>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div class="flex items-start">
                                <div class="bg-purple-100 rounded-full p-2 mr-3 flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Appointment Date</p>
                                    <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->appointment_date)->format('F d, Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="bg-purple-100 rounded-full p-2 mr-3 flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Time</p>
                                    <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}</p>
                                </div>
                            </div>

                            @if($booking->animals->isNotEmpty())
                                <div class="flex items-start">
                                    <div class="bg-purple-100 rounded-full p-2 mr-3 flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium">Animals</p>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($booking->animals as $animal)
                                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                                        {{ $animal->name }}
                                                    </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($booking->adoptions->isNotEmpty())
                                <div class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm font-semibold text-green-800">
                                                Adoption Confirmed ({{ $booking->adoptions->count() }})
                                            </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2 mt-6">
                            <button type="button"
                                    onclick="openModal('bookingModal-{{ $booking->id }}')"
                                    class="flex-1 text-center bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Include the modal for each booking (hidden by default) -->
                @include('booking-adoption.show-modal', ['booking' => $booking])
            @endforeach
        </div>
    @endif

    @if($bookings->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $bookings->links() }}
        </div>
    @endif

    <div class="mt-16 bg-gradient-to-r from-purple-700 to-purple-900 rounded-lg p-12 text-center text-white">
        <h2 class="text-3xl font-bold mb-4">Need Help with Your Booking?</h2>
        <p class="text-xl mb-6">Contact us if you have any questions about your appointments or need to make changes.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('contact') }}" class="bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300 inline-block">
                Contact Support
            </a>
        </div>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
            });
            document.body.style.overflow = 'auto';
        }
    });

    // Back to booking details from adoption fee modal
    function backToBookingDetails(bookingId) {
        closeModal('adoptionFeeModal-' + bookingId);
        openModal('bookingModal-' + bookingId);
    }

    // Update selection count and estimated fee in booking details modal
    function updateSelectionSummary(bookingId) {
        const checkboxes = document.querySelectorAll('.animal-select-' + bookingId + ':checked');
        let total = 0;
        let count = 0;

        checkboxes.forEach(function(cb) {
            total += parseFloat(cb.dataset.fee) || 0;
            count++;
        });

        const countEl = document.getElementById('selectedCount-' + bookingId);
        const feeEl = document.getElementById('estimatedFee-' + bookingId);

        if (countEl) countEl.innerText = count;
        if (feeEl) feeEl.innerText = 'RM ' + total.toFixed(2);
    }

    // Open adoption fee modal and populate with selected animals
    // Does NOT close the booking details modal
    function openAdoptionFeeModal(bookingId) {
        const checkboxes = document.querySelectorAll('.animal-select-' + bookingId + ':checked');

        // Check if at least one animal is selected
        if (checkboxes.length === 0) {
            alert('Please select at least one animal to adopt.');
            return;
        }

        // Get containers
        const listContainer = document.getElementById('selectedAnimalsList-' + bookingId);
        const hiddenInputsContainer = document.getElementById('hiddenAnimalInputs-' + bookingId);
        const grandTotalEl = document.getElementById('grandTotal-' + bookingId);
        const noAnimalsMsg = document.getElementById('noAnimalsSelected-' + bookingId);
        const submitBtn = document.getElementById('submitBtn-' + bookingId);

        // Clear previous content
        listContainer.innerHTML = '';
        hiddenInputsContainer.innerHTML = '';

        let grandTotal = 0;

        checkboxes.forEach(function(cb) {
            const animalId = cb.dataset.animalId;
            const animalName = cb.dataset.animalName;
            const animalSpecies = cb.dataset.animalSpecies;
            const baseFee = parseFloat(cb.dataset.baseFee) || 0;
            const medicalFee = parseFloat(cb.dataset.medicalFee) || 0;
            const vaccinationFee = parseFloat(cb.dataset.vaccinationFee) || 0;
            const totalFee = parseFloat(cb.dataset.fee) || 0;

            grandTotal += totalFee;

            // Create animal card (read-only)
            const animalCard = document.createElement('div');
            animalCard.className = 'flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200';
            animalCard.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">${animalName} (${animalSpecies})</p>
                            <p class="text-sm text-gray-600">
                                Base: RM ${baseFee.toFixed(2)},
                                Medical: RM ${medicalFee.toFixed(2)},
                                Vaccination: RM ${vaccinationFee.toFixed(2)}
                            </p>
                        </div>
                    </div>
                    <span class="font-bold text-gray-800">RM ${totalFee.toFixed(2)}</span>
                `;
            listContainer.appendChild(animalCard);

            // Create hidden input for form submission
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'animal_ids[]';
            hiddenInput.value = animalId;
            hiddenInputsContainer.appendChild(hiddenInput);
        });

        // Update grand total
        grandTotalEl.innerText = 'RM ' + grandTotal.toFixed(2);

        // Show/hide no animals message and enable/disable submit
        if (checkboxes.length === 0) {
            noAnimalsMsg.classList.remove('hidden');
            submitBtn.disabled = true;
        } else {
            noAnimalsMsg.classList.add('hidden');
            submitBtn.disabled = false;
        }

        // Open adoption fee modal WITHOUT closing booking modal
        openModal('adoptionFeeModal-' + bookingId);
    }

    // Add event listeners for animal selection checkboxes
    document.addEventListener('DOMContentLoaded', function() {
        // Find all animal selection checkboxes and add change listeners
        document.querySelectorAll('[class*="animal-select-"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                // Extract booking ID from class name
                const classes = this.className.split(' ');
                const selectClass = classes.find(c => c.startsWith('animal-select-'));
                if (selectClass) {
                    const bookingId = selectClass.replace('animal-select-', '');
                    updateSelectionSummary(bookingId);
                }
            });
        });
    });
</script>
</body>
</html>
