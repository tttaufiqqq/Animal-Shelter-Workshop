{{-- Success Message --}}
@if (session('success'))
    <div class="flex items-start gap-3 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg shadow-sm animate-slideIn">
        <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-green-800">{{ session('success') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="text-green-600 hover:text-green-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Error Message --}}
@if (session('error'))
    <div class="flex items-start gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm animate-slideIn">
        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-red-800">{{ session('error') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Animal Header Card --}}
<div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 rounded-xl shadow-xl overflow-hidden">
    <div class="p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <!-- Left: Animal Info -->
            <div class="flex items-center gap-4 slide-in">
                <div class="bg-white/10 backdrop-blur p-4 rounded-xl">
                    <i class="fas fa-paw text-4xl text-white"></i>
                </div>
                <div class="text-white">
                    <h1 class="text-3xl font-bold tracking-tight">{{ $animal->name }}</h1>
                    <p class="text-purple-100 text-sm mt-1 flex items-center gap-2">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-dog"></i>
                            {{ $animal->species }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-birthday-cake"></i>
                            {{ $animal->age }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-venus-mars"></i>
                            {{ $animal->gender }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Right: Action Buttons -->
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

                @role('caretaker|public user|adopter')
                <button onclick="openVisitModal()"
                        class="group bg-white/10 hover:bg-white/20 text-white px-4 py-3 rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-xl backdrop-blur border border-white/20">
                    <i class="fas fa-list text-lg group-hover:scale-110 transition-transform"></i>
                    <span class="hidden sm:inline font-semibold">Visit List</span>
                </button>
                @endrole
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
            <div class="bg-white/10 backdrop-blur rounded-lg p-4">
                <p class="text-purple-200 text-xs">Status</p>
                <p class="text-white font-bold text-lg mt-1">
                    @if($animal->adoption_status == 'Not Adopted')
                        Available
                    @else
                        {{ $animal->adoption_status }}
                    @endif
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4">
                <p class="text-purple-200 text-xs">Weight</p>
                <p class="text-white font-bold text-lg mt-1">{{ $animal->weight }} kg</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4">
                <p class="text-purple-200 text-xs">Medical Records</p>
                <p class="text-white font-bold text-lg mt-1">{{ $animal->medicals->count() }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4">
                <p class="text-purple-200 text-xs">Vaccinations</p>
                <p class="text-white font-bold text-lg mt-1">{{ $animal->vaccinations->count() }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Main Content Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Images & Details -->
    <div class="lg:col-span-2 space-y-6">
        {{-- Image Gallery --}}
        @include('animal-management.components.image-gallery', ['animal' => $animal, 'animalImages' => $animalImages, 'hasImages' => $hasImages])

        {{-- Health Details --}}
        @include('animal-management.components.health-details', ['animal' => $animal])

        {{-- Rescue Information --}}
        @include('animal-management.components.rescue-info', ['animal' => $animal])

        {{-- Medical Records --}}
        @include('animal-management.components.medical-records', ['animal' => $animal])

        {{-- Vaccination Records --}}
        @include('animal-management.components.vaccination-records', ['animal' => $animal])
    </div>

    <!-- Right Column - Sidebar -->
    @include('animal-management.components.sidebar', ['animal' => $animal, 'animalProfile' => $animalProfile ?? null, 'slots' => $slots ?? collect(), 'activeBooking' => $activeBooking ?? null])
</div>
