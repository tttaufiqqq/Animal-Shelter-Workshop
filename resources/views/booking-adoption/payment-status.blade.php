<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - Adoption</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #9333ea;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #7e22ce;
    }
    /* Smooth line clamp */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #9333ea;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #7e22ce;
    }
</style>
<body class="bg-gray-100">
@include('navbar')

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        @if($status_id == 1)
            <!-- Success -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-center">
                    <div class="text-white">
                        <i class="fas fa-check-circle text-6xl mb-4"></i>
                        <h1 class="text-3xl font-bold mb-2">Payment Successful!</h1>
                        <p class="text-green-100">Your adoption has been confirmed</p>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <h3 class="font-bold text-gray-800 mb-2">
                            <i class="fas fa-paw text-green-600 mr-2"></i>
                            Congratulations on adopting {{ $animal_names ?? 'your new pet(s)' }}!
                        </h3>
                        <p class="text-gray-700 text-sm">
                            @if(isset($animal_count) && $animal_count > 1)
                                We're thrilled that you've decided to give loving homes to {{ $animal_count }} furry friends.
                            @else
                                We're thrilled that you've decided to give a loving home to our furry friend.
                            @endif
                        </p>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-semibold text-gray-800 mb-3">Payment Details</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Booking ID:</span>
                                <span class="font-semibold">#{{ $booking_id }}</span>
                            </div>
                            @if(isset($animal_count) && $animal_count > 1)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Animals Adopted:</span>
                                    <span class="font-semibold">{{ $animal_count }} animals</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Amount Paid:</span>
                                <span class="font-semibold text-green-600">RM {{ number_format($amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Bill Code:</span>
                                <span class="font-semibold">{{ $billcode }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Reference No:</span>
                                <span class="font-semibold">{{ $reference_no ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">Completed</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Next Steps
                        </h4>
                        <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                            <li>Check your email for adoption confirmation and receipt</li>
                            <li>Prepare for your scheduled pickup appointment</li>
                            <li>Bring a valid government-issued ID</li>
                            <li>Our staff will contact you with pickup instructions</li>
                            <li>Prepare supplies and a safe space for your new pet(s)</li>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('booking:main') }}" class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition shadow-md">
                            <i class="fas fa-list mr-2"></i>
                            View My Bookings
                        </a>
                        <a href="{{ route('welcome') }}" class="flex-1 text-center bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold transition shadow-md">
                            <i class="fas fa-home mr-2"></i>
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Failed or Pending -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-red-600 p-8 text-center">
                    <div class="text-white">
                        <i class="fas fa-exclamation-circle text-6xl mb-4"></i>
                        <h1 class="text-3xl font-bold mb-2">Payment {{ $status_id == 2 ? 'Pending' : 'Failed' }}</h1>
                        <p class="text-red-100">{{ $status_id == 2 ? 'Your payment is being processed' : 'There was an issue with your payment' }}</p>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                        <h3 class="font-bold text-gray-800 mb-2">
                            <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                            Booking Status: Confirmed (Awaiting Payment)
                        </h3>
                        <p class="text-gray-700 text-sm">
                            Your booking for {{ $animal_names ?? 'your selected animal(s)' }} is confirmed, but payment has not been completed yet.
                            @if($status_id == 2)
                                Please wait while we process your payment.
                            @else
                                You can retry the payment from your bookings page.
                            @endif
                        </p>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-semibold text-gray-800 mb-3">Payment Details</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Booking ID:</span>
                                <span class="font-semibold">#{{ $booking_id }}</span>
                            </div>
                            @if(isset($animal_count) && $animal_count > 1)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Animals Selected:</span>
                                    <span class="font-semibold">{{ $animal_count }} animals</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Amount Due:</span>
                                <span class="font-semibold text-red-600">RM {{ number_format($amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Bill Code:</span>
                                <span class="font-semibold">{{ $billcode }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Reference No:</span>
                                <span class="font-semibold">{{ $reference_no ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 {{ $status_id == 2 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700' }} rounded text-xs font-semibold">
                                        {{ $status_id == 2 ? 'Pending' : 'Failed' }}
                                    </span>
                            </div>
                        </div>
                    </div>

                    @if($status_id == 3)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <h4 class="font-semibold text-gray-800 mb-2">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                What Happened?
                            </h4>
                            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                                <li>Payment was cancelled or declined</li>
                                <li>Insufficient funds or payment method issue</li>
                                <li>Your booking remains confirmed - you can retry payment</li>
                                <li>Contact us if you need assistance</li>
                            </ul>
                        </div>
                    @endif

                    <div class="flex gap-3">
                        <a href="{{ route('booking:main') }}" class="flex-1 text-center bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-lg font-semibold transition shadow-md">
                            <i class="fas fa-redo mr-2"></i>
                            {{ $status_id == 2 ? 'View Booking' : 'Retry Payment' }}
                        </a>
                        <a href="{{ route('welcome') }}" class="flex-1 text-center bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold transition shadow-md">
                            <i class="fas fa-home mr-2"></i>
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
</body>
</html>
