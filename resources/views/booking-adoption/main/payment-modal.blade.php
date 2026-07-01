<!-- Payment Status Modal -->
@if(session('show_payment_modal') && session('payment_status'))
    @php
        $payment = session('payment_status');
        $isSuccess = $payment['status_id'] == 1;
    @endphp
    <div id="paymentStatusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center modal-backdrop">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r {{ $isSuccess ? 'from-green-600 to-green-700' : 'from-red-600 to-red-700' }} text-white p-8 rounded-t-2xl">
                <div class="flex items-center justify-center mb-4">
                    @if($isSuccess)
                        <div class="bg-white rounded-full p-4">
                            <i class="fas fa-check-circle text-green-600" style="font-size: 4rem;"></i>
                        </div>
                    @else
                        <div class="bg-white rounded-full p-4">
                            <i class="fas fa-times-circle text-red-600" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
                <h2 class="text-3xl font-bold text-center">
                    {{ $isSuccess ? 'Payment Successful!' : 'Payment Failed' }}
                </h2>
                <p class="text-center text-white text-opacity-90 mt-2">
                    {{ $isSuccess ? 'Your adoption has been confirmed' : 'Payment could not be processed' }}
                </p>
            </div>

            <!-- Body -->
            <div class="p-8">
                @if($isSuccess)
                    <!-- Success Message -->
                    <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 text-xl mt-1"></i>
                            <div class="ml-3">
                                <h3 class="text-lg font-semibold text-green-800">Congratulations!</h3>
                                <p class="text-green-700 mt-1">
                                    You have successfully adopted <strong>{{ $payment['animal_names'] }}</strong>.
                                    Your booking has been completed and the animal(s) are now marked as adopted.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Failure Message -->
                    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-times-circle text-red-500 text-xl mt-1"></i>
                            <div class="ml-3">
                                <h3 class="text-lg font-semibold text-red-800">Payment Failed</h3>
                                <p class="text-red-700 mt-1">
                                    The payment for <strong>{{ $payment['animal_names'] }}</strong> could not be processed.
                                    Your booking remains confirmed. Please try again or contact support.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment Details -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Payment Details</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Booking ID</p>
                            <p class="font-semibold text-gray-900">#{{ $payment['booking_id'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Amount</p>
                            <p class="font-semibold text-gray-900">RM {{ number_format($payment['amount'], 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Bill Code</p>
                            <p class="font-semibold text-gray-900">{{ $payment['billcode'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Reference Number</p>
                            <p class="font-semibold text-gray-900">{{ $payment['reference_no'] }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Animal(s)</p>
                            <p class="font-semibold text-gray-900">{{ $payment['animal_names'] }} ({{ $payment['animal_count'] }} animal{{ $payment['animal_count'] > 1 ? 's' : '' }})</p>
                        </div>
                    </div>
                </div>

                @if($isSuccess)
                    <!-- Next Steps -->
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h4 class="font-semibold text-blue-900 mb-3 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            What's Next?
                        </h4>
                        <ul class="space-y-2 text-sm text-blue-900">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-600"></i>
                                <span>A confirmation email has been sent to your email address</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-600"></i>
                                <span>You can now pick up your adopted animal(s) from the shelter</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-600"></i>
                                <span>Contact us if you have any questions or need assistance</span>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-6 rounded-b-2xl flex justify-center gap-4">
                <button onclick="closePaymentModal()"
                        class="px-8 py-3 bg-gradient-to-r {{ $isSuccess ? 'from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'from-red-600 to-red-700 hover:from-red-700 hover:to-red-800' }} text-white rounded-lg font-semibold transition duration-300 shadow-lg">
                    {{ $isSuccess ? 'Close & View Bookings' : 'Close' }}
                </button>
            </div>
        </div>
    </div>
@endif
