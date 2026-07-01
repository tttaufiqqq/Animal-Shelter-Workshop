<script>
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
