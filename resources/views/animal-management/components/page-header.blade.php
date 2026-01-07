<!-- Page Header -->
<div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 text-white py-6 shadow-xl">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <!-- Left: Back Button & Title -->
            <div class="flex items-center gap-4 slide-in">
                <a href="{{ route('animal-management.index') }}"
                   class="group inline-flex items-center justify-center bg-white/10 hover:bg-white/20 text-white p-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-x-1">
                    <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>

                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">{{ $animal->name }}</h1>
                    <p class="text-purple-100 text-sm mt-1">
                        <i class="fas fa-paw mr-1"></i>
                        {{ $animal->species }} • {{ $animal->age }} • {{ $animal->gender }}
                    </p>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            @role('caretaker|public user|adopter')
            <div class="flex gap-3 fade-in">
                @if(isset($activeBooking))
                    <div class="bg-yellow-400/20 border border-yellow-300/30 text-yellow-100 px-4 py-3 rounded-xl flex items-center gap-2 shadow-lg backdrop-blur">
                        <i class="fas fa-calendar-check text-lg"></i>
                        <div class="hidden sm:block">
                            <p class="text-xs font-semibold">Currently Booked</p>
                            <p class="text-xs opacity-90">{{ \Carbon\Carbon::parse($activeBooking->appointment_date)->format('M d, Y') }}</p>
                        </div>
                    </div>
                @endif

                <button onclick="openVisitModal()"
                        class="group bg-white/10 hover:bg-white/20 text-white px-4 py-3 rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-xl backdrop-blur border border-white/20">
                    <i class="fas fa-list text-lg group-hover:scale-110 transition-transform"></i>
                    <span class="hidden sm:inline font-semibold">Visit List</span>
                </button>
                @include('booking-adoption.visit-list')
            </div>

            {{-- Include the edit modal --}}
            @include('animal-management.edit-modal', ['animal' => $animal])
            @endrole
        </div>
    </div>
</div>
