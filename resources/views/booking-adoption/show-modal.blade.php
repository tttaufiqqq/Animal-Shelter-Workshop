<!-- Booking Details Modal -->
<div id="bookingDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <h2 class="text-2xl font-bold">Booking Details</h2>
                        <p class="text-purple-100 text-sm">Booking #{{ $booking->id }}</p>
                    </div>
                </div>
                <button onclick="closeBookingDetailsModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Status Badge -->
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Booking Status</h3>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    @if($booking->status == 'Pending') bg-yellow-100 text-yellow-700
                    @elseif($booking->status == 'Confirmed') bg-blue-100 text-blue-700
                    @elseif($booking->status == 'Completed') bg-green-100 text-green-700
                    @elseif($booking->status == 'Cancelled') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-700
                    @endif">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    {{ $booking->status }}
                </span>
            </div>

            <!-- Animal Information -->
            @if($booking->animal)
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-300 rounded-xl p-6 shadow-md">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center text-xl">
                    <i class="fas fa-paw text-purple-600 mr-3 text-2xl"></i>
                    Animal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Animal Image -->
                    <div class="md:col-span-1">
                        @if($booking->animal->images && $booking->animal->images->count() > 0)
                            <img src="{{ asset('storage/' . $booking->animal->images->first()->image_path) }}" 
                                 alt="{{ $booking->animal->name }}" 
                                 class="w-full h-48 object-cover rounded-xl shadow-lg border-4 border-white">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-purple-300 to-purple-400 rounded-xl flex items-center justify-center shadow-lg border-4 border-white">
                                <span class="text-6xl">
                                    @if(strtolower($booking->animal->species) == 'dog') 🐕
                                    @elseif(strtolower($booking->animal->species) == 'cat') 🐈
                                    @else 🐾
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Animal Details -->
                    <div class="md:col-span-2 space-y-3">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Name</span>
                                    <p class="text-gray-800 font-bold text-xl">{{ $booking->animal->name }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Species</span>
                                    <p class="text-gray-800 font-medium text-lg">{{ $booking->animal->species }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Age</span>
                                    <p class="text-gray-800 font-medium">{{ $booking->animal->age }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Gender</span>
                                    <p class="text-gray-800 font-medium">{{ $booking->animal->gender }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($booking->animal->health_details)
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-2">Health Details</span>
                            <p class="text-gray-700 text-sm leading-relaxed">{{ Str::limit($booking->animal->health_details, 150) }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-purple-200">
                    <a href="{{ route('animal-management.show', $booking->animal->id) }}" 
                       class="inline-flex items-center text-purple-600 hover:text-purple-700 font-semibold">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        View Full Animal Profile
                    </a>
                </div>
            </div>
            @endif

            <!-- Appointment Details -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-6 shadow-md">
               <h3 class="font-bold text-gray-800 mb-4 flex items-center text-xl">
                  <i class="fas fa-calendar-check text-blue-600 mr-3 text-2xl"></i>
                  Appointment Details
               </h3>
               <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center mb-2">
                           <i class="fas fa-calendar-alt text-blue-600 mr-3 text-xl"></i>
                           <span class="text-xs font-semibold text-gray-500 uppercase">Date</span>
                        </div>
                        <p class="text-gray-800 font-bold text-lg">
                           {{ \Carbon\Carbon::parse($booking->appointment_date)->format('F d, Y') }}
                        </p>
                  </div>
                  <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center mb-2">
                           <i class="fas fa-clock text-blue-600 mr-3 text-xl"></i>
                           <span class="text-xs font-semibold text-gray-500 uppercase">Time</span>
                        </div>
                        <p class="text-gray-800 font-bold text-lg">
                           {{ date('h:i A', strtotime($booking->appointment_time)) }}
                        </p>
                  </div>
               </div>
               <div class="mt-4 bg-white rounded-lg p-4 shadow-sm">
                  <div class="flex items-center mb-2">
                        <i class="fas fa-info-circle text-blue-600 mr-3 text-xl"></i>
                        <span class="text-xs font-semibold text-gray-500 uppercase">Booked On</span>
                  </div>
                  <p class="text-gray-800 font-medium">
                        {{ $booking->created_at->format('F d, Y') }}
                  </p>
               </div>
            </div>

            <!-- User Information -->
            @if($booking->user)
            <div class="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-xl p-6 shadow-md">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center text-xl">
                    <i class="fas fa-user text-green-600 mr-3 text-2xl"></i>
                    Booker Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-user-circle text-green-600 mr-3 text-xl"></i>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Name</span>
                        </div>
                        <p class="text-gray-800 font-medium text-lg">{{ $booking->user->name }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-envelope text-green-600 mr-3 text-xl"></i>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Email</span>
                        </div>
                        <p class="text-gray-800 font-medium">{{ $booking->user->email }}</p>
                    </div>
                    <div class="mt-4 bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center mb-2">
                              <i class="fas fa-info-circle text-blue-600 mr-3 text-xl"></i>
                              <span class="text-xs font-semibold text-gray-500 uppercase">Phone Number</span>
                        </div>
                        <p class="text-gray-800 font-medium">
                              {{ $booking->user->phoneNum }}
                        </p>
                     </div>
                </div>
            </div>
            @endif

            <!-- Important Information -->
            @if(in_array($booking->status, ['Pending', 'Confirmed']))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-5">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center text-lg">
                    <i class="fas fa-exclamation-circle text-yellow-600 mr-2"></i>
                    Important Reminders
                </h3>
                <ul class="text-sm text-gray-700 space-y-2 list-disc list-inside">
                    <li>Please arrive 10 minutes before your scheduled appointment</li>
                    <li>Bring a valid government-issued ID</li>
                    <li>Be prepared to discuss your living situation and pet care experience</li>
                    <li>If you need to reschedule or cancel, please notify us at least 24 hours in advance</li>
                </ul>
            </div>
            @endif
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 p-6 border-t border-gray-200 flex flex-wrap justify-end gap-3">
            <button onclick="closeBookingDetailsModal()" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300 shadow-md">
               <i class="fas fa-times mr-2"></i>Close
            </button>
            @if(in_array($booking->status, ['Pending', 'Confirmed']))
                <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-300 shadow-md">
                        <i class="fas fa-times-circle mr-2"></i>Cancel Booking
                    </button>
                </form>
                <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-300 shadow-md">
                        <i class="fas fa-times-circle mr-2"></i>Confirm Booking
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>