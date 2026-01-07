@auth
    @if(Auth::user()->hasRole('admin'))
        {{-- Admin View with Admin Layout --}}
        @php
        $breadcrumbs = [
            ['label' => 'Animal Management', 'url' => route('animal-management.index'), 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'],
            ['label' => $animal->name, 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>']
        ];

        // Safely load images with automatic fallback when Eilya database is offline
        $animalImages = $animal->getImagesOrEmpty();
        $hasImages = $animalImages->isNotEmpty();
        @endphp

        <x-admin-layout title="{{ $animal->name }} - Animal Details" :breadcrumbs="$breadcrumbs">
    {{-- Additional Styles --}}
    @push('styles')
        <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slideIn {
                from { opacity: 0; transform: translateX(-20px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            .fade-in { animation: fadeIn 0.5s ease-out; }
            .slide-in { animation: slideIn 0.4s ease-out; }
            .skeleton { animation: pulse 1.5s ease-in-out infinite; }
            .hover-scale { transition: transform 0.3s ease; }
            .hover-scale:hover { transform: scale(1.02); }
            .glass-effect {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            }
            .gradient-border {
                border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-image-slice: 1;
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
            /* Smooth line clamp */
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        </style>
    @endpush

    {{-- Page Content --}}
    <div class="space-y-4">
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
    </div>

    {{-- Modals --}}
    @include('adopter-animal-matching.animal-modal')
    @include('animal-management.components.medical-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
    @include('animal-management.components.vaccination-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
    @include('booking-adoption.book-animal')

    @role('caretaker|public user|adopter')
    @include('booking-adoption.visit-list')
    @include('animal-management.edit-modal', ['animal' => $animal])
    @endrole

    {{-- Scripts --}}
    @push('scripts')
        <script>
            // Image Gallery Functions
            function changeImage(imagePath) {
                document.getElementById('mainImage').src = imagePath;
            }

            // Modal Functions
            function openMedicalModal() {
                const modal = document.getElementById('medicalModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeMedicalModal() {
                const modal = document.getElementById('medicalModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function openVaccinationModal() {
                const modal = document.getElementById('vaccinationModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeVaccinationModal() {
                const modal = document.getElementById('vaccinationModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            // Close modals when clicking outside
            document.getElementById('medicalModal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMedicalModal();
                }
            });

            document.getElementById('vaccinationModal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeVaccinationModal();
                }
            });

            // Image Swiper
            let currentImages = [
                @foreach($animalImages as $image)
                { path: "{{ $image->url }}" },
                @endforeach
            ];
            let currentImageIndex = 0;

            function displayCurrentImage() {
                const content = document.getElementById('imageSwiperContent');
                const image = currentImages[currentImageIndex];
                const mainImage = content.querySelector('img');

                if (mainImage) {
                    mainImage.style.opacity = '0';
                    setTimeout(() => {
                        mainImage.src = image.path;
                        mainImage.style.opacity = '1';
                    }, 200);
                } else {
                    content.innerHTML = `<img src="${image.path}" class="max-w-full max-h-full object-contain transition-opacity duration-500" id="mainDisplayImage">`;
                }

                document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;

                // Update thumbnails
                currentImages.forEach((_, index) => {
                    const thumb = document.getElementById(`thumbnail-${index}`);
                    if (thumb) {
                        thumb.className = `group flex-shrink-0 w-24 h-24 cursor-pointer rounded-xl overflow-hidden border-3 transition-all duration-300 ${
                            index === currentImageIndex ? 'border-purple-600 ring-2 ring-purple-300 shadow-lg' : 'border-gray-300 hover:border-purple-400 hover:shadow-md'
                        }`;
                    }
                });
            }

            function nextImage() {
                currentImageIndex = (currentImageIndex + 1) % currentImages.length;
                displayCurrentImage();
            }

            function prevImage() {
                currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
                displayCurrentImage();
            }

            function goToImage(index) {
                currentImageIndex = index;
                displayCurrentImage();
            }

            // Fullscreen functionality
            function toggleFullscreen() {
                const mainImage = document.getElementById('mainDisplayImage');
                if (!mainImage) return;

                if (!document.fullscreenElement) {
                    if (mainImage.requestFullscreen) {
                        mainImage.requestFullscreen();
                    } else if (mainImage.webkitRequestFullscreen) {
                        mainImage.webkitRequestFullscreen();
                    } else if (mainImage.msRequestFullscreen) {
                        mainImage.msRequestFullscreen();
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                if(currentImages.length > 0) {
                    displayCurrentImage();
                    if(currentImages.length > 1) {
                        document.getElementById('prevImageBtn')?.classList.remove('hidden');
                        document.getElementById('nextImageBtn')?.classList.remove('hidden');
                        document.getElementById('imageCounter')?.classList.remove('hidden');
                    }
                }

                // Event listeners for navigation
                document.getElementById('prevImageBtn')?.addEventListener('click', prevImage);
                document.getElementById('nextImageBtn')?.addEventListener('click', nextImage);

                // Keyboard navigation
                document.addEventListener('keydown', function(e){
                    if(currentImages.length < 2) return;
                    if(e.key === 'ArrowLeft') prevImage();
                    if(e.key === 'ArrowRight') nextImage();
                });
            });
        </script>
    @endpush
</x-admin-layout>
    @else
        {{-- Non-Admin View with Standalone Layout --}}
        @php
        // Safely load images with automatic fallback when Eilya database is offline
        $animalImages = $animal->getImagesOrEmpty();
        $hasImages = $animalImages->isNotEmpty();
        @endphp

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{ $animal->name }} - Animal Details</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes slideIn {
                    from { opacity: 0; transform: translateX(-20px); }
                    to { opacity: 1; transform: translateX(0); }
                }
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                .fade-in { animation: fadeIn 0.5s ease-out; }
                .slide-in { animation: slideIn 0.4s ease-out; }
                .skeleton { animation: pulse 1.5s ease-in-out infinite; }
                .hover-scale { transition: transform 0.3s ease; }
                .hover-scale:hover { transform: scale(1.02); }
                .glass-effect {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                }
                .gradient-border {
                    border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-image-slice: 1;
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
                /* Smooth line clamp */
                .line-clamp-2 {
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }
            </style>
        </head>
        <body class="bg-gradient-to-br from-gray-50 to-purple-50 min-h-screen">
            @include('navbar')

            {{-- Page Content --}}
            <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-4">
                {{-- Back Button --}}
                <div class="mb-4">
                    <a href="{{ route('animal-management.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg shadow-sm border border-gray-200 transition-all duration-200 hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back to Animals</span>
                    </a>
                </div>

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
                    </div>
                </div>

                {{-- Main Content Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column - Images & Details ---->
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

                    <!-- Right Column - Sidebar ---->
                    @include('animal-management.components.sidebar', ['animal' => $animal, 'animalProfile' => $animalProfile ?? null, 'slots' => $slots ?? collect(), 'activeBooking' => $activeBooking ?? null])
                </div>
            </div>

            {{-- Modals --}}
            @include('adopter-animal-matching.animal-modal')
            @include('animal-management.components.medical-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
            @include('animal-management.components.vaccination-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
            @include('booking-adoption.book-animal')

            @role('caretaker|public user|adopter')
            @include('booking-adoption.visit-list')
            @include('animal-management.edit-modal', ['animal' => $animal])
            @endrole

            {{-- Scripts --}}
            <script>
                // Image Gallery Functions
                function changeImage(imagePath) {
                    document.getElementById('mainImage').src = imagePath;
                }

                // Modal Functions
                function openMedicalModal() {
                    const modal = document.getElementById('medicalModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeMedicalModal() {
                    const modal = document.getElementById('medicalModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                function openVaccinationModal() {
                    const modal = document.getElementById('vaccinationModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeVaccinationModal() {
                    const modal = document.getElementById('vaccinationModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                // Close modals when clicking outside
                document.getElementById('medicalModal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeMedicalModal();
                    }
                });

                document.getElementById('vaccinationModal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeVaccinationModal();
                    }
                });

                // Image Swiper
                let currentImages = [
                    @foreach($animalImages as $image)
                    { path: "{{ $image->url }}" },
                    @endforeach
                ];
                let currentImageIndex = 0;

                function displayCurrentImage() {
                    const content = document.getElementById('imageSwiperContent');
                    const image = currentImages[currentImageIndex];
                    const mainImage = content.querySelector('img');

                    if (mainImage) {
                        mainImage.style.opacity = '0';
                        setTimeout(() => {
                            mainImage.src = image.path;
                            mainImage.style.opacity = '1';
                        }, 200);
                    } else {
                        content.innerHTML = `<img src="${image.path}" class="max-w-full max-h-full object-contain transition-opacity duration-500" id="mainDisplayImage">`;
                    }

                    document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;

                    // Update thumbnails
                    currentImages.forEach((_, index) => {
                        const thumb = document.getElementById(`thumbnail-${index}`);
                        if (thumb) {
                            thumb.className = `group flex-shrink-0 w-24 h-24 cursor-pointer rounded-xl overflow-hidden border-3 transition-all duration-300 ${
                                index === currentImageIndex ? 'border-purple-600 ring-2 ring-purple-300 shadow-lg' : 'border-gray-300 hover:border-purple-400 hover:shadow-md'
                            }`;
                        }
                    });
                }

                function nextImage() {
                    currentImageIndex = (currentImageIndex + 1) % currentImages.length;
                    displayCurrentImage();
                }

                function prevImage() {
                    currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
                    displayCurrentImage();
                }

                function goToImage(index) {
                    currentImageIndex = index;
                    displayCurrentImage();
                }

                // Fullscreen functionality
                function toggleFullscreen() {
                    const mainImage = document.getElementById('mainDisplayImage');
                    if (!mainImage) return;

                    if (!document.fullscreenElement) {
                        if (mainImage.requestFullscreen) {
                            mainImage.requestFullscreen();
                        } else if (mainImage.webkitRequestFullscreen) {
                            mainImage.webkitRequestFullscreen();
                        } else if (mainImage.msRequestFullscreen) {
                            mainImage.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                }

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', function() {
                    if(currentImages.length > 0) {
                        displayCurrentImage();
                        if(currentImages.length > 1) {
                            document.getElementById('prevImageBtn')?.classList.remove('hidden');
                            document.getElementById('nextImageBtn')?.classList.remove('hidden');
                            document.getElementById('imageCounter')?.classList.remove('hidden');
                        }
                    }

                    // Event listeners for navigation
                    document.getElementById('prevImageBtn')?.addEventListener('click', prevImage);
                    document.getElementById('nextImageBtn')?.addEventListener('click', nextImage);

                    // Keyboard navigation
                    document.addEventListener('keydown', function(e){
                        if(currentImages.length < 2) return;
                        if(e.key === 'ArrowLeft') prevImage();
                        if(e.key === 'ArrowRight') nextImage();
                    });
                });
            </script>
        </body>
        </html>
    @endif
@endauth
