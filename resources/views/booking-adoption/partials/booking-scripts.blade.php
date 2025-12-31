<script>
    // ==================== DATA RETRIEVAL ====================
    // Use booking data from window.bookingsData
    function getBookingsData() {
        return window.bookingsData || [];
    }

    // ==================== BOOKING DETAIL MODAL FUNCTIONS ====================
    function openBookingDetailModal(bookingId) {
        const bookingsData = getBookingsData();
        const booking = bookingsData.find(b => b.id === bookingId);
        if (!booking) {
            console.error('Booking not found:', bookingId);
            return;
        }

        // Update title
        document.getElementById('detailBookingTitle').textContent = `Booking #${booking.id}`;

        // Build detail content
        const statusClass =
            booking.status === 'Pending' ? 'bg-yellow-200 text-yellow-900 border-yellow-400' :
            booking.status === 'Confirmed' ? 'bg-blue-200 text-blue-900 border-blue-400' :
            booking.status === 'Completed' ? 'bg-green-200 text-green-900 border-green-500' :
            booking.status === 'Cancelled' ? 'bg-red-200 text-red-900 border-red-400' :
            'bg-gray-100 text-gray-800 border-gray-300';

        const statusBadgeClass =
            booking.status === 'Pending' ? 'bg-yellow-600' :
            booking.status === 'Confirmed' ? 'bg-blue-600' :
            booking.status === 'Completed' ? 'bg-green-600' :
            booking.status === 'Cancelled' ? 'bg-red-600' :
            'bg-gray-600';

        // Determine status-based colors for sections
        const sectionBgClass =
            booking.status === 'Pending' ? 'bg-yellow-50 border-yellow-300' :
            booking.status === 'Confirmed' ? 'bg-blue-50 border-blue-300' :
            booking.status === 'Completed' ? 'bg-green-50 border-green-300' :
            booking.status === 'Cancelled' ? 'bg-red-50 border-red-300' :
            'bg-purple-50 border-purple-300';

        const iconColorClass =
            booking.status === 'Pending' ? 'text-yellow-600' :
            booking.status === 'Confirmed' ? 'text-blue-600' :
            booking.status === 'Completed' ? 'text-green-600' :
            booking.status === 'Cancelled' ? 'text-red-600' :
            'text-purple-600';

        let animalsHtml = '';
        if (booking.animals && booking.animals.length > 0) {
            animalsHtml = `
                <div class="${sectionBgClass} border-2 rounded-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4 text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 ${iconColorClass}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                        </svg>
                        Animals in Booking (${booking.animals.length})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${booking.animals.map(animal => `
                            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                                ${animal.image_url ? `
                                    <img src="${animal.image_url}"
                                         alt="${animal.name}"
                                         class="w-full h-32 object-cover rounded-lg mb-3">
                                ` : ''}
                                <h4 class="font-bold text-gray-800 mb-2">${animal.name}</h4>
                                <p class="text-sm text-gray-600">${animal.species} • ${animal.age} • ${animal.gender}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        let adoptionButtonHtml = '';
        if (booking.adoptions && booking.adoptions.length > 0) {
            adoptionButtonHtml = `
                <button onclick="openAdoptionDetailModal(${booking.id})"
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition duration-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                    View Adoption Records
                </button>
            `;
        }

        const content = `
            <div class="space-y-6">
                <!-- Status and Date -->
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border ${statusClass}">
                        ${booking.status}
                    </span>
                    <span class="text-sm text-gray-500">
                        Booked on ${new Date(booking.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                    </span>
                </div>

                <!-- User Information -->
                ${booking.user ? `
                    <div class="${sectionBgClass} border-2 rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-4 text-lg flex items-center gap-2">
                            <svg class="w-5 h-5 ${iconColorClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            User Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Name</p>
                                <p class="text-gray-800 font-medium">${booking.user.name}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Email</p>
                                <p class="text-gray-800 font-medium">${booking.user.email}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Phone</p>
                                <p class="text-gray-800 font-medium">${booking.user.phoneNum}</p>
                            </div>
                        </div>
                    </div>
                ` : ''}

                <!-- Appointment Details -->
                <div class="${sectionBgClass} border-2 rounded-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4 text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 ${iconColorClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Appointment Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date</p>
                            <p class="text-gray-800 font-bold text-lg">${new Date(booking.appointment_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Time</p>
                            <p class="text-gray-800 font-bold text-lg">${new Date('2000-01-01 ' + booking.appointment_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</p>
                        </div>
                    </div>
                </div>

                ${animalsHtml}

                ${adoptionButtonHtml}
            </div>
        `;

        document.getElementById('bookingDetailContent').innerHTML = content;
        document.getElementById('bookingDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Reset scroll position to top
        setTimeout(() => {
            const detailContent = document.getElementById('bookingDetailContent');
            if (detailContent) {
                detailContent.scrollTop = 0;
            }
        }, 0);
    }

    function closeBookingDetailModal() {
        const modal = document.getElementById('bookingDetailModal');
        const detailContent = document.getElementById('bookingDetailContent');

        // Reset scroll position when closing
        if (detailContent) {
            detailContent.scrollTop = 0;
        }

        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ==================== ADOPTION DETAIL MODAL FUNCTIONS ====================
    function openAdoptionDetailModal(bookingId) {
        const bookingsData = getBookingsData();
        const booking = bookingsData.find(b => b.id === bookingId);
        if (!booking) {
            console.error('Booking not found:', bookingId);
            return;
        }

        // Update title
        document.getElementById('detailAdoptionTitle').textContent = `Adoption Records - Booking #${booking.id}`;

        // Build adoption content
        let adoptionsHtml = '';
        if (booking.adoptions && booking.adoptions.length > 0) {
            adoptionsHtml = booking.adoptions.map(adoption => `
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Adoption #${adoption.id}</h3>
                        <span class="px-3 py-1 bg-green-600 text-white rounded-full text-sm font-semibold">Completed</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Adoption Fee</p>
                            <p class="text-2xl font-bold text-green-600">RM ${parseFloat(adoption.fee).toFixed(2)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Date</p>
                            <p class="text-lg font-semibold text-gray-800">${adoption.created_at}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Remarks</p>
                        <p class="text-gray-800">${adoption.remarks}</p>
                    </div>

                    ${adoption.transaction ? `
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">Payment Information</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-600">Amount:</span>
                                    <span class="font-medium text-gray-800">RM ${parseFloat(adoption.transaction.amount).toFixed(2)}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Status:</span>
                                    <span class="font-medium text-green-600">${adoption.transaction.status}</span>
                                </div>
                                ${adoption.transaction.bill_code ? `
                                    <div>
                                        <span class="text-gray-600">Bill Code:</span>
                                        <span class="font-medium text-gray-800">${adoption.transaction.bill_code}</span>
                                    </div>
                                ` : ''}
                                ${adoption.transaction.reference_no ? `
                                    <div>
                                        <span class="text-gray-600">Reference:</span>
                                        <span class="font-medium text-gray-800">${adoption.transaction.reference_no}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        const content = `
            <div class="space-y-6">
                ${adoptionsHtml}
                <button onclick="closeAdoptionDetailModal()"
                        class="w-full bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold transition duration-200">
                    Close
                </button>
            </div>
        `;

        document.getElementById('adoptionDetailContent').innerHTML = content;
        document.getElementById('adoptionDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Reset scroll position to top
        setTimeout(() => {
            const detailContent = document.getElementById('adoptionDetailContent');
            if (detailContent) {
                detailContent.scrollTop = 0;
            }
        }, 0);
    }

    function closeAdoptionDetailModal() {
        const modal = document.getElementById('adoptionDetailModal');
        const detailContent = document.getElementById('adoptionDetailContent');

        // Reset scroll position when closing
        if (detailContent) {
            detailContent.scrollTop = 0;
        }

        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ==================== BACKWARD COMPATIBILITY ====================
    // Keep old function names for backward compatibility
    function openAdoptionModal(bookingId) {
        openAdoptionDetailModal(bookingId);
    }

    // ==================== MODAL EVENT LISTENERS ====================
    // Close modals when clicking outside
    document.getElementById('bookingDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeBookingDetailModal();
        }
    });

    document.getElementById('adoptionDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAdoptionDetailModal();
        }
    });

    // ==================== ESCAPE KEY HANDLER ====================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const bookingModal = document.getElementById('bookingDetailModal');
            const adoptionModal = document.getElementById('adoptionDetailModal');

            if (bookingModal && !bookingModal.classList.contains('hidden')) {
                closeBookingDetailModal();
            } else if (adoptionModal && !adoptionModal.classList.contains('hidden')) {
                closeAdoptionDetailModal();
            }
        }
    });
</script>
