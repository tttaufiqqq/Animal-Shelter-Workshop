<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $animal->name }} - Stray Animal Shelter</title>
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
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
@include('navbar')
@include('adopter-animal-matching.animal-modal')


<!-- Page Header -->
<div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 text-white py-12 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if (session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="font-semibold text-green-700">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <p class="font-semibold text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    @php
        // Safely load images with automatic fallback when Eilya database is offline
        $animalImages = $animal->getImagesOrEmpty();
        $hasImages = $animalImages->isNotEmpty();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Images -->
        <div class="lg:col-span-2 space-y-6">
            <div class="fade-in bg-white rounded-2xl shadow-xl overflow-hidden relative hover-scale">
                <!-- Main Image Display -->
                <div class="relative w-full aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center transition-all duration-500">
                        @if($hasImages)
                            <img src="{{ $animalImages->first()->url }}"
                                 alt="{{ $animal->name }}"
                                 class="max-w-full max-h-full object-contain transition-opacity duration-500"
                                 id="mainDisplayImage">
                        @else
                            <div class="aspect-video bg-gradient-to-br from-purple-300 via-purple-400 to-purple-500 flex items-center justify-center w-full h-full">
                                <span class="text-9xl animate-pulse">
                                    @if(strtolower($animal->species) == 'dog')
                                        🐕
                                    @elseif(strtolower($animal->species) == 'cat')
                                        🐈
                                    @else
                                        🐾
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Navigation Arrows -->
                    <button id="prevImageBtn" class="hidden absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-purple-600 rounded-full w-12 h-12 flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-110 z-10 group">
                        <i class="fas fa-chevron-left text-xl group-hover:-translate-x-0.5 transition-transform"></i>
                    </button>
                    <button id="nextImageBtn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-purple-600 rounded-full w-12 h-12 flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-110 z-10 group">
                        <i class="fas fa-chevron-right text-xl group-hover:translate-x-0.5 transition-transform"></i>
                    </button>

                    <!-- Image Counter -->
                    <div id="imageCounter" class="hidden absolute bottom-4 right-4 glass-effect border border-white/30 text-gray-800 px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                        <i class="fas fa-images mr-2 text-purple-600"></i>
                        <span id="currentImageIndex">1</span> / <span id="totalImages">{{ $animalImages->count() ?: 1 }}</span>
                    </div>

                    <!-- Fullscreen Button -->
                    @if($hasImages)
                    <button onclick="toggleFullscreen()" class="absolute top-4 right-4 glass-effect border border-white/30 text-gray-700 hover:text-purple-600 px-3 py-2 rounded-full text-sm font-semibold shadow-lg transition-all hover:scale-110">
                        <i class="fas fa-expand mr-1"></i> View
                    </button>
                    @endif
                </div>

                <!-- Thumbnails -->
                <div id="thumbnailContainer" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
                    <div id="thumbnailStrip" class="flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-purple-300 scrollbar-track-gray-100">
                        @if($hasImages)
                            @foreach($animalImages as $index => $image)
                                <div onclick="goToImage({{ $index }})"
                                     class="group flex-shrink-0 w-24 h-24 cursor-pointer rounded-xl overflow-hidden border-3 transition-all duration-300 {{ $index == 0 ? 'border-purple-600 ring-2 ring-purple-300 shadow-lg' : 'border-gray-300 hover:border-purple-400 hover:shadow-md' }}"
                                     id="thumbnail-{{ $index }}">
                                    <img src="{{ $image->url }}"
                                         alt="{{ $animal->name }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                            @endforeach
                        @else
                            <!-- Show placeholder as single thumbnail -->
                            <div class="flex-shrink-0 w-24 h-24 cursor-pointer rounded-xl overflow-hidden border-3 border-purple-300 bg-purple-100 flex items-center justify-center text-5xl">
                                @if(strtolower($animal->species) == 'dog')
                                    🐕
                                @elseif(strtolower($animal->species) == 'cat')
                                    🐈
                                @else
                                    🐾
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>



            <!-- Health Details Card -->
            <div class="fade-in bg-gradient-to-br from-white to-purple-50/30 rounded-2xl shadow-xl p-6 border border-purple-100 hover-scale">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-3 rounded-xl shadow-lg">
                        <i class="fas fa-heartbeat text-white text-xl"></i>
                    </div>
                    <span>Health Details</span>
                </h2>
                <div class="bg-white rounded-xl p-5 shadow-inner border border-gray-100">
                    <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $animal->health_details }}</p>
                </div>
            </div>

            <!-- Rescue Information -->
            @if($animal->rescue)
                <div class="fade-in bg-gradient-to-br from-white to-pink-50/30 rounded-2xl shadow-xl p-6 border border-pink-100 hover-scale">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                        <div class="bg-gradient-to-br from-pink-500 to-rose-600 p-3 rounded-xl shadow-lg">
                            <i class="fas fa-hand-holding-heart text-white text-xl"></i>
                        </div>
                        <span>Rescue Information</span>
                    </h2>
                    <div class="bg-white rounded-xl p-5 shadow-inner border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="bg-pink-100 p-2 rounded-lg">
                                <i class="fas fa-hashtag text-pink-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase">Rescue ID</p>
                                <p class="text-gray-800 font-bold">#{{ $animal->rescue->id }}</p>
                            </div>
                        </div>
                        @if($animal->rescue->report)
                            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                                <div class="bg-pink-100 p-2 rounded-lg">
                                    <i class="fas fa-map-marker-alt text-pink-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase">Location</p>
                                    <p class="text-gray-800 font-medium">{{ $animal->rescue->report->address }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                                <div class="bg-pink-100 p-2 rounded-lg">
                                    <i class="fas fa-city text-pink-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase">City</p>
                                    <p class="text-gray-800 font-medium">{{ $animal->rescue->report->city }}, {{ $animal->rescue->report->state }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-center gap-3">
                            <div class="bg-pink-100 p-2 rounded-lg">
                                <i class="fas fa-calendar text-pink-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase">Rescue Date</p>
                                <p class="text-gray-800 font-bold">{{ $animal->rescue->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Medical Records Section -->
            <div class="fade-in bg-gradient-to-br from-white to-blue-50/30 rounded-2xl shadow-xl p-6 border border-blue-100 hover-scale">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-xl shadow-lg">
                            <i class="fas fa-notes-medical text-white text-xl"></i>
                        </div>
                        <span>Medical Records</span>
                    </h2>
                    @role('caretaker')
                    <button onclick="openMedicalModal()" class="group bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
                        <span class="hidden sm:inline">Add Record</span>
                    </button>
                    @endrole
                </div>

                @if($animal->medicals && $animal->medicals->count() > 0)
                    <div class="space-y-3">
                        @foreach($animal->medicals->sortByDesc('created_at') as $medical)
                            <div class="bg-white border-l-4 border-blue-500 rounded-xl p-4 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-start justify-between mb-3">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 shadow-sm">
                                        <i class="fas fa-stethoscope"></i>
                                        {{ $medical->treatment_type }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-semibold bg-gray-100 px-3 py-1 rounded-full">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ \Carbon\Carbon::parse($medical->created_at)->format('M d, Y') }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-file-medical text-blue-600"></i>
                                    {{ $medical->diagnosis }}
                                </h4>
                                <p class="text-gray-700 text-sm mb-2 bg-gray-50 p-3 rounded-lg"><strong class="text-blue-600">Action:</strong> {{ $medical->action }}</p>
                                @if($medical->remarks)
                                    <p class="text-gray-600 text-sm mb-3 italic bg-blue-50 p-3 rounded-lg"><strong class="text-blue-600">Remarks:</strong> {{ $medical->remarks }}</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-4 text-sm pt-2 border-t border-gray-100">
                                    @if($medical->vet)
                                        <div class="flex items-center text-gray-600">
                                            <div class="bg-blue-100 p-1.5 rounded-lg mr-2">
                                                <i class="fas fa-user-md text-blue-600"></i>
                                            </div>
                                            <span class="font-medium">{{ $medical->vet->name }}</span>
                                        </div>
                                    @endif
                                    @if($medical->costs)
                                        <div class="flex items-center text-green-700 font-bold">
                                            <div class="bg-green-100 p-1.5 rounded-lg mr-2">
                                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                            </div>
                                            <span>RM {{ number_format($medical->costs, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gradient-to-br from-gray-50 to-blue-50/50 rounded-xl p-8 text-center border-2 border-dashed border-gray-300">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-notes-medical text-3xl text-blue-400"></i>
                        </div>
                        <p class="font-bold text-gray-700 mb-1">No medical records yet</p>
                        <p class="text-sm text-gray-500">Medical treatment records will appear here</p>
                    </div>
                @endif
            </div>

            <!-- Vaccination Records Section -->
            <div class="fade-in bg-gradient-to-br from-white to-green-50/30 rounded-2xl shadow-xl p-6 border border-green-100 hover-scale">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl shadow-lg">
                            <i class="fas fa-syringe text-white text-xl"></i>
                        </div>
                        <span>Vaccination Records</span>
                    </h2>
                    @role('caretaker')
                    <button onclick="openVaccinationModal()" class="group bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-5 py-2.5 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
                        <span class="hidden sm:inline">Add Vaccination</span>
                    </button>
                    @endrole
                </div>

                @if($animal->vaccinations && $animal->vaccinations->count() > 0)
                    <div class="space-y-3">
                        @foreach($animal->vaccinations->sortByDesc('created_at') as $vaccination)
                            <div class="bg-white border-l-4 border-green-500 rounded-xl p-4 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-start justify-between mb-3">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-green-200 text-green-700 shadow-sm">
                                        <i class="fas fa-shield-alt"></i>
                                        {{ $vaccination->type }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-semibold bg-gray-100 px-3 py-1 rounded-full">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ \Carbon\Carbon::parse($vaccination->created_at)->format('M d, Y') }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-syringe text-green-600"></i>
                                    {{ $vaccination->name }}
                                </h4>
                                @if($vaccination->remarks)
                                    <p class="text-gray-600 text-sm mb-3 italic bg-green-50 p-3 rounded-lg">{{ $vaccination->remarks }}</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-4 text-sm pt-2 border-t border-gray-100">
                                    @if($vaccination->vet)
                                        <div class="flex items-center text-gray-600">
                                            <div class="bg-green-100 p-1.5 rounded-lg mr-2">
                                                <i class="fas fa-user-md text-green-600"></i>
                                            </div>
                                            <span class="font-medium">{{ $vaccination->vet->name }}</span>
                                        </div>
                                    @endif
                                    @if($vaccination->next_due_date)
                                        <div class="flex items-center text-orange-700 font-semibold">
                                            <div class="bg-orange-100 p-1.5 rounded-lg mr-2">
                                                <i class="fas fa-calendar-check text-orange-600"></i>
                                            </div>
                                            <span>Due: {{ \Carbon\Carbon::parse($vaccination->next_due_date)->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                    @if($vaccination->costs)
                                        <div class="flex items-center text-green-700 font-bold">
                                            <div class="bg-green-100 p-1.5 rounded-lg mr-2">
                                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                            </div>
                                            <span>RM {{ number_format($vaccination->costs, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gradient-to-br from-gray-50 to-green-50/50 rounded-xl p-8 text-center border-2 border-dashed border-gray-300">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-syringe text-3xl text-green-400"></i>
                        </div>
                        <p class="font-bold text-gray-700 mb-1">No vaccination records yet</p>
                        <p class="text-sm text-gray-500">Vaccination records will appear here</p>
                    </div>
                @endif
            </div>

            <!-- Modal for Add Medical Record -->
            <div id="medicalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold">Add Medical Record</h2>
                            <button onclick="closeMedicalModal()" class="text-white hover:text-gray-200">
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.store') }}" class="p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="animalID" value="{{ $animal->id }}">

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Treatment Type <span class="text-red-600">*</span></label>
                            <select name="treatment_type" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" required>
                                <option value="">Select Treatment Type</option>
                                <option value="Checkup">Checkup</option>
                                <option value="Surgery">Surgery</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Dental">Dental</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">
                                Weight during treatment (kg) <span class="text-red-600">*</span>
                            </label>
                            <input
                                type="number"
                                name="weight"
                                step="0.01"
                                min="0"
                                value="{{ old('weight', $animal->weight) }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                placeholder="Enter animal weight"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Diagnosis <span class="text-red-600">*</span></label>
                            <textarea name="diagnosis" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Enter diagnosis" required></textarea>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Action Taken <span class="text-red-600">*</span></label>
                            <textarea name="action" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Enter action taken" required></textarea>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Remarks</label>
                            <textarea name="remarks" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Additional remarks (optional)"></textarea>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Veterinarian <span class="text-red-600">*</span></label>
                            <select name="vetID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" required>
                                <option value="">Select Veterinarian</option>
                                @foreach($vets as $vet)
                                    <option value="{{ $vet->id }}">{{ $vet->name }} - {{ $vet->specialization }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Cost (RM)</label>
                            <input type="number" name="costs" step="0.01" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="0.00">
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" onclick="closeMedicalModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300">
                                <i class="fas fa-plus mr-2"></i>Add Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal for Add Vaccination -->
            <div id="vaccinationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold">Add Vaccination Record</h2>
                            <button onclick="closeVaccinationModal()" class="text-white hover:text-gray-200">
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('vaccination-records.store') }}" class="p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="animalID" value="{{ $animal->id }}">

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Vaccine Name <span class="text-red-600">*</span></label>
                            <input type="text" name="name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="e.g., Rabies Vaccine" required>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Vaccine Type <span class="text-red-600">*</span></label>
                            <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" required>
                                <option value="">Select Type</option>
                                <option value="Rabies">Rabies</option>
                                <option value="DHPP">DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)</option>
                                <option value="Bordetella">Bordetella</option>
                                <option value="Leptospirosis">Leptospirosis</option>
                                <option value="Feline Distemper">Feline Distemper</option>
                                <option value="Feline Leukemia">Feline Leukemia</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">
                                Weight during taking the vaccine (kg) <span class="text-red-600">*</span>
                            </label>
                            <input
                                type="number"
                                name="weight"
                                step="0.01"
                                min="0"
                                value="{{ old('weight', $animal->weight) }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                placeholder="Enter animal weight"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Next Due Date</label>
                            <input type="date" name="next_due_date" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition">
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Remarks</label>
                            <textarea name="remarks" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="Additional notes (optional)"></textarea>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Veterinarian <span class="text-red-600">*</span></label>
                            <select name="vetID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" required>
                                <option value="">Select Veterinarian</option>
                                @foreach($vets as $vet)
                                    <option value="{{ $vet->id }}">{{ $vet->name }} - {{ $vet->specialization }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-800 font-semibold mb-2">Cost (RM)</label>
                            <input type="number" name="costs" step="0.01" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="0.00">
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" onclick="closeVaccinationModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300">
                                <i class="fas fa-plus mr-2"></i>Add Vaccination
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 flex justify-between">
                <div class="flex-1">
                    <div class="mb-4">
                        @if($animal->adoption_status == 'Not Adopted')
                            <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                    <i class="fas fa-check-circle mr-1"></i>Available for Adoption
                </span>
                        @elseif($animal->adoption_status == 'Adopted')
                            <span class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                    <i class="fas fa-home mr-1"></i>Adopted
                </span>
                        @else
                            <span class="inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">
                    {{ $animal->adoption_status }}
                </span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <!-- Left: Title -->
                        <h2 class="text-xl font-bold text-gray-800">Animal Information</h2>

                        @role('caretaker')
                        <!-- Right: Action Icons -->
                        <div class="flex space-x-4">
                            <a onclick="openEditModal({{ $animal->id }})"
                               class="text-purple-600 hover:text-purple-800 cursor-pointer transition duration-300"
                               title="Edit">
                                <i class="fas fa-edit text-xl"></i>
                            </a>

                            <form action="{{ route('animal-management.destroy', $animal->id) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this animal record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800 cursor-pointer transition duration-300 border-0 bg-transparent p-0"
                                        title="Delete">
                                    <i class="fas fa-trash text-xl"></i>
                                </button>
                            </form>
                        </div>
                        @endrole
                    </div>



                    <!-- Animal Details -->
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Species</span>
                            <span class="text-gray-800">{{ $animal->species }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Weight</span>
                            <span class="text-gray-800">{{ $animal->weight }} kg</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Age</span>
                            <span class="text-gray-800">{{ $animal->age }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Gender</span>
                            <span class="text-gray-800">{{ $animal->gender }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Arrival Date</span>
                            <span class="text-gray-800">{{ \Carbon\Carbon::parse($animal->arrival_date)->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-paw text-purple-600 mr-2"></i>
                    Animal Profile
                </h2>

                @if($animalProfile)
                    <!-- Header with Edit Button: This is displayed when the profile exists -->
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <!-- Button to open the modal for editing -->
                        @role('caretaker')<button type="button" onclick="openAnimalModal()" class="text-sm text-purple-600 hover:text-purple-800 font-semibold transition flex items-center">
                            <i class="fas fa-edit mr-1"></i> Edit Profile
                        </button>@endrole
                    </div>

                    <div class="space-y-3">

                        <!-- Size -->
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Size</span>
                            <span class="text-gray-800">{{ ucfirst($animalProfile->size) }}</span>
                        </div>

                        <!-- Energy Level -->
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Energy Level</span>
                            <span class="text-gray-800">{{ ucfirst($animalProfile->energy_level) }}</span>
                        </div>

                        <!-- Good with Kids (Boolean to Yes/No) -->
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Good with Kids</span>
                            <span class="text-gray-800">{{ $animalProfile->good_with_kids ? 'Yes' : 'No' }}</span>
                        </div>

                        <!-- Good with Pets (Boolean to Yes/No) -->
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Good with Pets</span>
                            <span class="text-gray-800">{{ $animalProfile->good_with_pets ? 'Yes' : 'No' }}</span>
                        </div>

                        <!-- Temperament -->
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Temperament</span>
                            <span class="text-gray-800">{{ ucfirst($animalProfile->temperament) }}</span>
                        </div>

                        <!-- Medical Needs -->
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Medical Needs</span>
                            <span class="text-gray-800">{{ ucfirst($animalProfile->medical_needs) }}</span>
                        </div>
                    </div>

                @else
                    <!-- Case when no profile exists -->
                    <p class="text-gray-500 mb-4">This animal does not have a profile yet. Click below to add one.</p>

                    <!-- Button to open the modal for creation -->
                    @role('caretaker')<button type="button" onclick="openAnimalModal()" class="w-full bg-purple-100 text-purple-700 py-3 rounded-lg font-semibold hover:bg-purple-200 transition duration-300 shadow-sm">
                        <i class="fas fa-plus mr-2"></i>Add Profile
                    </button>@endrole

                @endif

            </div>

            @include('adopter-animal-matching.animal-modal')


            <!-- Location Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt text-purple-600 mr-2"></i>
                    Assigned Slot
                </h2>

                @if($animal->slot)
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-700 font-semibold">Name</span>
                            <span class="text-purple-700 font-bold text-lg">
                    {{ $animal->slot->name ?? 'Slot ' . $animal->slot->id }}
                </span>
                        </div>

                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-700 font-semibold">Section</span>
                            <span class="text-gray-800">{{ $animal->slot->section->name ?? 'Unknown Section' }}</span>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-700 font-semibold">Status</span>

                            @php
                                $status = $animal->slot->status;
                                $colorClass = match($status) {
                                    'available' => 'bg-green-200 text-green-700',
                                    'occupied' => 'bg-red-200 text-red-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                            @endphp

                            <span class="px-2 py-1 rounded text-xs font-medium {{ $colorClass }}">
                     {{ ucfirst($status) }}
                </span>
                        </div>

                        {{-- Reassign Slot Form --}}
                        @role('admin|caretaker')
                        <div class="border-t border-purple-200 pt-4 mt-4">
                            <form action="{{ route('animals.assignSlot', $animal->id) }}" method="POST">
                                @csrf

                                <label class="block text-gray-700 font-semibold mb-2">Reassign Slot</label>
                                <select name="slot_id"
                                        class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        required>
                                    <option value="">Select Slot</option>
                                    @foreach($slots as $slot)
                                        <option value="{{ $slot->id }}"
                                            {{ $animal->slotID == $slot->id ? 'selected' : '' }}>
                                            Slot {{ $slot->name ?? $slot->id }} - {{ $slot->section->name ?? 'Unknown Section' }} ({{ ucfirst($slot->status) }})
                                        </option>
                                    @endforeach
                                </select>

                                <button type="submit"
                                        class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300 font-medium">
                                    <i class="fas fa-sync-alt mr-2"></i>Update Slot
                                </button>
                            </form>
                        </div>
                        @endrole
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <i class="fas fa-map-marker-slash text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-600 font-medium mb-4">No slot assigned</p>

                        {{-- Assign Slot Form --}}
                        @role('admin|caretaker')
                        <form action="{{ route('animals.assignSlot', $animal->id) }}" method="POST" class="mt-4">
                            @csrf

                            <label class="block text-gray-700 font-semibold mb-2">Assign Slot</label>
                            <select name="slot_id"
                                    class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    required>
                                <option value="">Select Slot</option>
                                @foreach($slots as $slot)
                                    <option value="{{ $slot->id }}">
                                        Slot {{ $slot->name ?? $slot->id }} - {{ $slot->section->name ?? 'Unknown Section' }} ({{ ucfirst($slot->status) }})
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit"
                                    class="w-full bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                                <i class="fas fa-plus mr-2"></i>Assign Slot
                            </button>
                        </form>
                        @endrole
                    </div>
                @endif
            </div>

            <!-- Action Card -->
            @role('public user|caretaker|adopter')
            @if($animal->adoption_status == 'Not Adopted')
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-heart text-purple-600 mr-2"></i>
                        Interested in Adopting?
                    </h2>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-paw text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-800 font-semibold">{{ $animal->name }}</p>
                                <p class="text-gray-500 text-sm">Give this friend a loving home</p>
                            </div>
                        </div>

                        <p class="text-gray-600 text-sm mb-4">
                            Add to your visit list and schedule an appointment to meet them in person!
                        </p>

                        <form action="{{ route('visit.list.add', $animal->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-plus"></i>
                                Add to Visit List
                            </button>
                        </form>

                        <p class="text-gray-400 text-xs text-center mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            No commitment required
                        </p>
                    </div>
                </div>
            @endif
            @endrole
        </div>
    </div>
</div>
<!-- Include the booking modal -->
@include('booking-adoption.book-animal')

<script>
    function changeImage(imagePath) {
        document.getElementById('mainImage').src = imagePath;
    }
    function openMedicalModal() {
        document.getElementById('medicalModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeMedicalModal() {
        document.getElementById('medicalModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openVaccinationModal() {
        document.getElementById('vaccinationModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeVaccinationModal() {
        document.getElementById('vaccinationModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
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

    let currentImages = [
            @foreach($animalImages as $image)
        { path: "{{ $image->url }}" },
        @endforeach
    ];
    let currentImageIndex = 0;

    function displayCurrentImage() {
        const content = document.getElementById('imageSwiperContent');
        const image = currentImages[currentImageIndex];

        content.innerHTML = `<img src="${image.path}" class="max-w-full max-h-full object-contain">`;
        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;

        // Update thumbnails
        currentImages.forEach((_, index) => {
            const thumb = document.getElementById(`thumbnail-${index}`);
            if (thumb) {
                thumb.className = `flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 transition duration-300 ${
                    index === currentImageIndex ? 'border-green-600' : 'border-gray-300 hover:border-green-400'
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

    // Init
    if(currentImages.length > 0) {
        displayCurrentImage();
        if(currentImages.length > 1) {
            document.getElementById('prevImageBtn').classList.remove('hidden');
            document.getElementById('nextImageBtn').classList.remove('hidden');
            document.getElementById('imageCounter').classList.remove('hidden');
        }
    }

    document.getElementById('prevImageBtn').addEventListener('click', prevImage);
    document.getElementById('nextImageBtn').addEventListener('click', nextImage);

    // Optional keyboard navigation
    document.addEventListener('keydown', function(e){
        if(currentImages.length < 2) return;
        if(e.key === 'ArrowLeft') prevImage();
        if(e.key === 'ArrowRight') nextImage();
    });

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

    // Add image transition effect
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
</script>
</body>
</html>
