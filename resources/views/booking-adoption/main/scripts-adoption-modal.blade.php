<script>
    // Store bookings data for modal access
    const bookingsData = @json($bookingsDataArray);

    // Open adoption modal
    function openAdoptionModal(bookingId) {
        const modal = document.getElementById('adoptionDetailModal');
        const content = document.getElementById('adoptionModalContent');
        const loading = document.getElementById('adoptionModalLoading');
        const innerContent = document.getElementById('adoptionModalContent-inner');

        // Show modal with animation
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Reset state
        loading.classList.remove('hidden');
        innerContent.classList.add('hidden');

        // Animate in
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);

        // Find booking data
        const booking = bookingsData.find(b => b.id === bookingId);

        if (!booking) {
            innerContent.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Booking Not Found</h3>
                    <p class="text-gray-600">Unable to load adoption details for this booking.</p>
                </div>
            `;
            loading.classList.add('hidden');
            innerContent.classList.remove('hidden');
            return;
        }

        // Update title
        document.getElementById('adoptionModalTitle').textContent = 'Adoption Details';
        document.getElementById('adoptionModalSubtitle').textContent = `Booking #${booking.id}`;

        // Simulate loading for better UX
        setTimeout(() => {
            // Build adoption cards
            let adoptionsHtml = '';

            if (booking.adoptions && booking.adoptions.length > 0) {
                adoptionsHtml = booking.adoptions.map((adoption, index) => `
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-5 ${index > 0 ? 'mt-4' : ''}">
                        <!-- Adoption Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">Adoption #${adoption.id}</h3>
                                    <p class="text-sm text-gray-600">${adoption.created_at}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full">
                                Completed
                            </span>
                        </div>

                        <!-- Animal Info -->
                        ${adoption.animal ? `
                            <div class="bg-white rounded-lg p-4 mb-4 border border-gray-100">
                                <div class="flex items-center gap-4">
                                    ${adoption.animal.image_url ? `
                                        <img src="${adoption.animal.image_url}" alt="${adoption.animal.name}" class="w-16 h-16 rounded-lg object-cover">
                                    ` : `
                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-paw text-gray-400 text-xl"></i>
                                        </div>
                                    `}
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900">${adoption.animal.name}</h4>
                                        <p class="text-sm text-gray-600">${adoption.animal.species} ${adoption.animal.breed ? '• ' + adoption.animal.breed : ''}</p>
                                        <p class="text-xs text-gray-500">${adoption.animal.gender}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Adoption Fee</p>
                                        <p class="text-xl font-bold text-green-600">RM ${parseFloat(adoption.fee).toFixed(2)}</p>
                                    </div>
                                </div>
                            </div>
                        ` : ''}

                        <!-- Remarks -->
                        ${adoption.remarks ? `
                            <div class="bg-white rounded-lg p-4 mb-4 border border-gray-100">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-comment-alt text-gray-400 mt-1"></i>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Remarks</p>
                                        <p class="text-gray-700">${adoption.remarks}</p>
                                    </div>
                                </div>
                            </div>
                        ` : ''}

                        <!-- Transaction Info -->
                        ${adoption.transaction ? `
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <div class="flex items-center gap-2 mb-3">
                                    <i class="fas fa-credit-card text-blue-600"></i>
                                    <h4 class="font-semibold text-gray-900">Payment Information</h4>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p class="text-gray-500">Amount Paid</p>
                                        <p class="font-semibold text-gray-900">RM ${parseFloat(adoption.transaction.amount).toFixed(2)}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Status</p>
                                        <p class="font-semibold text-green-600 flex items-center gap-1">
                                            <i class="fas fa-check-circle text-xs"></i>
                                            ${adoption.transaction.status}
                                        </p>
                                    </div>
                                    ${adoption.transaction.bill_code ? `
                                        <div>
                                            <p class="text-gray-500">Bill Code</p>
                                            <p class="font-mono font-semibold text-gray-900">${adoption.transaction.bill_code}</p>
                                        </div>
                                    ` : ''}
                                    ${adoption.transaction.reference_no ? `
                                        <div>
                                            <p class="text-gray-500">Reference No</p>
                                            <p class="font-mono font-semibold text-gray-900">${adoption.transaction.reference_no}</p>
                                        </div>
                                    ` : ''}
                                    <div class="col-span-2">
                                        <p class="text-gray-500">Transaction Date</p>
                                        <p class="font-semibold text-gray-900">${adoption.transaction.created_at}</p>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `).join('');

                // Add summary
                const totalFee = booking.adoptions.reduce((sum, a) => sum + parseFloat(a.fee), 0);
                adoptionsHtml += `
                    <div class="mt-6 bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl p-5 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-receipt text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-purple-200 text-sm">Total Adoption Fee</p>
                                    <p class="text-2xl font-bold">RM ${totalFee.toFixed(2)}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-purple-200 text-sm">Animals Adopted</p>
                                <p class="text-3xl font-bold">${booking.adoptions.length}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div class="mt-4 bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-heart text-white text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-green-800">Thank you for adopting!</h4>
                                <p class="text-sm text-green-700 mt-1">Your adoption has been successfully completed. You can pick up your new family member at the shelter during operating hours.</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                adoptionsHtml = `
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Adoptions Yet</h3>
                        <p class="text-gray-600">This booking doesn't have any adoption records.</p>
                    </div>
                `;
            }

            innerContent.innerHTML = adoptionsHtml;
            loading.classList.add('hidden');
            innerContent.classList.remove('hidden');
        }, 300);
    }
