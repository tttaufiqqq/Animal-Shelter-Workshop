<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
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
</head>
<body class="bg-gray-50">
@include('navbar')

<!-- Limited Connectivity Warning Banner -->
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
            <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. You may experience limited functionality or missing data.</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($dbDisconnected as $connection => $info)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ $info['module'] }}
                </span>
                @endforeach
            </div>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto flex-shrink-0 text-yellow-400 hover:text-yellow-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
</div>
@endif

<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-5xl font-bold mb-4">My Bookings</h1>
                <p class="text-xl text-purple-100">View and manage your appointment bookings for adoptions</p>
            </div>
            @unless($bookings->isEmpty())
                <div class="mt-6 md:mt-0">
                    <a href="{{ route('animal:main') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300 shadow-lg">
                        <i class="fas fa-plus"></i>
                        <span>New Booking</span>
                    </a>
                </div>
            @endunless
        </div>
    </div>
</div>

<div class="px-4 sm:px-6 lg:px-8 py-12">

    @if (session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
            <i class="fas fa-check-circle text-green-600 text-xl flex-shrink-0"></i>
            <p class="font-semibold text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
            <i class="fas fa-times-circle text-red-600 text-xl flex-shrink-0"></i>
            <p class="font-semibold text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Stats Cards as Filter Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
        <!-- Total Bookings Card -->
        <a href="{{ route('bookings.index') }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ !request('status') ? 'ring-4 ring-purple-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-calendar-alt text-purple-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-purple-700 mb-2">{{ $totalBookings }}</p>
            <p class="text-gray-600">Total Bookings</p>
            @if(!request('status'))
                <div class="mt-2 text-xs text-purple-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Pending Card -->
        <a href="{{ route('bookings.index', ['status' => 'Pending']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Pending' ? 'ring-4 ring-yellow-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-clock text-yellow-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-yellow-600 mb-2">{{ $statusCounts['Pending'] ?? 0 }}</p>
            <p class="text-gray-600">Pending</p>
            @if(request('status') == 'Pending')
                <div class="mt-2 text-xs text-yellow-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Confirmed Card -->
        <a href="{{ route('bookings.index', ['status' => 'Confirmed']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Confirmed' ? 'ring-4 ring-blue-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-check-circle text-blue-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-blue-600 mb-2">{{ $statusCounts['Confirmed'] ?? 0 }}</p>
            <p class="text-gray-600">Confirmed</p>
            @if(request('status') == 'Confirmed')
                <div class="mt-2 text-xs text-blue-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Completed Card -->
        <a href="{{ route('bookings.index', ['status' => 'Completed']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Completed' ? 'ring-4 ring-green-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-check-circle text-green-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-green-600 mb-2">{{ $statusCounts['Completed'] ?? 0 }}</p>
            <p class="text-gray-600">Completed</p>
            @if(request('status') == 'Completed')
                <div class="mt-2 text-xs text-green-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Cancelled Card -->
        <a href="{{ route('bookings.index', ['status' => 'Cancelled']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Cancelled' ? 'ring-4 ring-red-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-times-circle text-red-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-red-600 mb-2">{{ $statusCounts['Cancelled'] ?? 0 }}</p>
            <p class="text-gray-600">Cancelled</p>
            @if(request('status') == 'Cancelled')
                <div class="mt-2 text-xs text-red-600 font-semibold">● Active</div>
            @endif
        </a>
    </div>

    <!-- Search and Filter Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" action="{{ route('bookings.index') }}" class="space-y-4">
            <!-- Keep current status filter -->
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-search text-purple-600"></i>
                <h3 class="text-lg font-semibold text-gray-900">Search & Filter Bookings</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- General Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search
                    </label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Booking ID, Date, Remarks..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        From Date
                    </label>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        To Date
                    </label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition duration-300 flex items-center gap-2">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <a href="{{ route('bookings.index') }}"
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition duration-300 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>

            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                <div class="mt-3 text-sm text-purple-600 font-medium">
                    <span class="inline-flex items-center gap-1">
                        <i class="fas fa-info-circle"></i>
                        Active filters applied
                    </span>
                </div>
            @endif
        </form>
    </div>

    @if($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <div class="mb-6">
                <i class="fas fa-calendar-alt text-gray-300" style="font-size: 8rem;"></i>
            </div>
            <h3 class="text-3xl font-bold text-gray-700 mb-3">No Bookings Yet</h3>
            <p class="text-gray-500 text-lg mb-8">You haven't made any bookings. Start by creating your first appointment!</p>
            <a href="{{ route('animal:main') }}" class="inline-flex items-center gap-2 bg-purple-700 hover:bg-purple-800 text-white px-8 py-3 rounded-lg font-semibold transition duration-300 shadow-lg">
                <i class="fas fa-plus"></i>
                <span>Create First Booking</span>
            </a>
        </div>
    @else
        <!-- Table View -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Booking ID
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Appointment Date
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Time
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Animals
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Adoption
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($bookings as $booking)
                        @php
                            $statusKey = strtolower($booking->status);
                            $badgeColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                'confirmed' => 'bg-blue-100 text-blue-800 border-blue-300',
                                'completed' => 'bg-green-100 text-green-800 border-green-300',
                                'cancelled' => 'bg-red-100 text-red-800 border-red-300',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-purple-600"></i>
                                    <span class="text-sm font-bold text-purple-700">#{{ $booking->id }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeColors[$statusKey] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium">
                                    {{ \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium">
                                    {{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if($booking->animals->isNotEmpty())
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold">
                                            {{ $booking->animals->count() }}
                                        </span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($booking->animals->take(2) as $animal)
                                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                                    {{ $animal->name }}
                                                </span>
                                            @endforeach
                                            @if($booking->animals->count() > 2)
                                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs font-medium">
                                                    +{{ $booking->animals->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">No animals</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($booking->adoptions->isNotEmpty())
                                    <button type="button"
                                            onclick="openAdoptionModal({{ $booking->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-xs font-semibold hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                                        <i class="fas fa-heart"></i>
                                        <span>View Details</span>
                                        <span class="bg-white/20 px-1.5 py-0.5 rounded text-[10px]">{{ $booking->adoptions->count() }}</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-medium">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <button type="button"
                                        onclick="openBookingModal({{ $booking->id }})"
                                        class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-200 shadow-sm hover:shadow-md">
                                    <i class="fas fa-eye"></i>
                                    View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modals for all bookings (outside table structure) -->
        @foreach($bookings as $booking)
            @include('booking-adoption.partials.booking-modal-steps', ['booking' => $booking])
        @endforeach
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

<!-- Global Loading Overlay -->
@include('booking-adoption.partials.loading-overlay')

<!-- Adoption Detail Modal -->
<div id="adoptionDetailModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-[60]">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="adoptionModalContent">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold" id="adoptionModalTitle">Adoption Details</h2>
                        <p class="text-green-100 text-sm" id="adoptionModalSubtitle">Booking #000</p>
                    </div>
                </div>
                <button onclick="closeAdoptionModal()" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-xl flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6" id="adoptionModalBody">
            <!-- Loading State -->
            <div id="adoptionModalLoading" class="flex flex-col items-center justify-center py-12">
                <div class="w-16 h-16 border-4 border-green-200 border-t-green-600 rounded-full animate-spin mb-4"></div>
                <p class="text-gray-600 font-medium">Loading adoption details...</p>
            </div>
            <!-- Content will be populated dynamically -->
            <div id="adoptionModalContent-inner" class="hidden"></div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex-shrink-0 border-t border-gray-100">
            <button onclick="closeAdoptionModal()" class="w-full px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-times"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- Booking Modal JavaScript -->
<script src="{{ asset('js/booking-modal.js') }}"></script>

@php
    $bookingsDataArray = $bookings->map(function($booking) {
        return [
            'id' => $booking->id,
            'status' => $booking->status,
            'appointment_date' => $booking->appointment_date,
            'appointment_time' => $booking->appointment_time,
            'created_at' => $booking->created_at,
            'animals' => $booking->animals->map(function($animal) {
                return [
                    'id' => $animal->id,
                    'name' => $animal->name,
                    'species' => $animal->species,
                    'breed' => $animal->breed,
                    'gender' => $animal->gender,
                    'image_url' => $animal->images->first()?->url ?? null,
                ];
            })->values(),
            'adoptions' => $booking->adoptions->map(function($adoption) {
                return [
                    'id' => $adoption->id,
                    'fee' => $adoption->fee,
                    'remarks' => $adoption->remarks,
                    'created_at' => \Carbon\Carbon::parse($adoption->created_at)->format('M d, Y'),
                    'animal' => $adoption->animal ? [
                        'id' => $adoption->animal->id,
                        'name' => $adoption->animal->name,
                        'species' => $adoption->animal->species,
                        'breed' => $adoption->animal->breed,
                        'gender' => $adoption->animal->gender,
                        'image_url' => $adoption->animal->images->first()?->url ?? null,
                    ] : null,
                    'transaction' => $adoption->transaction ? [
                        'id' => $adoption->transaction->id,
                        'amount' => $adoption->transaction->amount,
                        'status' => $adoption->transaction->status,
                        'bill_code' => $adoption->transaction->bill_code,
                        'reference_no' => $adoption->transaction->reference_no,
                        'created_at' => \Carbon\Carbon::parse($adoption->transaction->created_at)->format('M d, Y h:i A'),
                    ] : null,
                ];
            })->values(),
        ];
    })->values();
@endphp

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

    // Close adoption modal
    function closeAdoptionModal() {
        const modal = document.getElementById('adoptionDetailModal');
        const content = document.getElementById('adoptionModalContent');

        // Animate out
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 200);
    }

    // Close on backdrop click
    document.getElementById('adoptionDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAdoptionModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('adoptionDetailModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAdoptionModal();
            }
        }
    });

    // Close payment status modal
    function closePaymentModal() {
        const modal = document.getElementById('paymentStatusModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
</script>
</body>
</html>
