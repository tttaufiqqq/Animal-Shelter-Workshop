<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - Adoption</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
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
                                Congratulations on adopting {{ $animal_name }}!
                            </h3>
                            <p class="text-gray-700 text-sm">We're thrilled that you've decided to give a loving home to our furry friend.</p>
                        </div>
                        
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h4 class="font-semibold text-gray-800 mb-3">Payment Details</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Booking ID:</span>
                                    <span class="font-semibold">#{{ $booking_id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount Paid:</span>
                                    <span class="font-semibold">RM {{ number_format($amount, 2) }}</span>
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
                                <li>Check your email for adoption confirmation</li>
                                <li>Prepare for your scheduled pickup appointment</li>
                                <li>Bring a valid ID and any required documents</li>
                                <li>We'll contact you with further instructions</li>
                            </ul>
                        </div>
                        
                        <div class="flex gap-3">
                            <a href="{{ route('booking:main') }}" class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition">
                                View My Bookings
                            </a>
                            <a href="{{ route('welcome') }}" class="flex-1 text-center bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold transition">
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
                            <p class="text-gray-700 text-sm">Your booking is confirmed, but payment has not been completed. You can try again.</p>
                        </div>
                        
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h4 class="font-semibold text-gray-800 mb-3">Payment Details</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Booking ID:</span>
                                    <span class="font-semibold">#{{ $booking_id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount:</span>
                                    <span class="font-semibold">RM {{ number_format($amount, 2) }}</span>
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
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-semibold">{{ $status_id == 2 ? 'Pending' : 'Failed' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <a href="{{ route('booking:main') }}" class="flex-1 text-center bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-semibold transition">
                                Try Again
                            </a>
                            <a href="{{ route('welcome') }}" class="flex-1 text-center bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold transition">
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