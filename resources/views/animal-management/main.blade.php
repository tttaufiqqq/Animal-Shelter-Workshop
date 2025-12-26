<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        .slide-down {
            animation: slideDown 0.3s ease-out;
        }

        .scale-in {
            animation: scaleIn 0.3s ease-out;
        }

        /* Card hover effects */
        .animal-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animal-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .animal-card:hover .animal-image {
            transform: scale(1.05);
        }

        .animal-image {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* Badge pulse animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Loading skeleton */
        @keyframes shimmer {
            0% { background-position: -468px 0; }
            100% { background-position: 468px 0; }
        }

        .skeleton {
            animation: shimmer 1.5s infinite linear;
            background: linear-gradient(to right, #f0f0f0 8%, #e0e0e0 18%, #f0f0f0 33%);
            background-size: 800px 104px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-purple-50 min-h-screen">
@include('navbar')

<!-- Limited Connectivity Warning Banner -->
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm slide-down">
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
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    </div>
@endif

<!-- Page Header -->
<div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 text-white py-16 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <!-- Left: Title -->
            <div class="fade-in">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-white bg-opacity-20 p-3 rounded-2xl backdrop-blur-sm">
                        <i class="fas fa-paw text-4xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl md:text-5xl font-bold mb-1">Our Animals</h1>
                        <p class="text-purple-100 text-sm md:text-base">
                            <i class="fas fa-heart mr-1"></i>
                            Browse all animals currently in our care
                        </p>
                    </div>
                </div>
                <p class="text-purple-200 text-sm max-w-2xl">
                    Add animals to your visit list to schedule an appointment and meet them in person. Each animal is waiting for their forever home.
                </p>
            </div>

            @role('public user|caretaker|adopter')
            <!-- Right: Visit List Button -->
            <button onclick="openVisitModal()"
                    class="bg-white bg-opacity-20 hover:bg-white hover:text-purple-700 text-white px-6 py-4 rounded-2xl transition-all duration-300 flex items-center gap-3 shadow-lg backdrop-blur-sm hover:shadow-xl transform hover:scale-105">
                <div class="bg-white bg-opacity-30 p-2 rounded-lg">
                    <i class="fas fa-clipboard-list text-2xl"></i>
                </div>
                <div class="text-left">
                    <div class="font-bold text-lg">Visit List</div>
                    <div class="text-xs opacity-90">View saved animals</div>
                </div>
            </button>
            @endrole
        </div>
    </div>
</div>

@include('booking-adoption.visit-list')

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Success/Error Messages -->
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

    <!-- Caretaker Toggle (Only visible for caretakers) -->
    @role('caretaker')
        <div class="bg-gradient-to-r from-purple-100 to-indigo-100 rounded-xl shadow-md p-4 mb-4 scale-in border border-purple-300">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="bg-purple-600 text-white p-2 rounded-lg">
                        <i class="fas fa-filter text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Caretaker View</h3>
                        <p class="text-xs text-gray-600">Choose which animals to display</p>
                    </div>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <a href="{{ route('animal-management.index', array_merge(request()->except('rescued_by_me'), ['rescued_by_me' => 'true'])) }}"
                       class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300 text-center shadow-sm hover:shadow-md {{ request('rescued_by_me') === 'true' ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : 'bg-white text-purple-700 hover:bg-purple-50' }}">
                        <i class="fas fa-user-check mr-1 text-xs"></i>
                        My Rescues
                    </a>
                    <a href="{{ route('animal-management.index', request()->except('rescued_by_me')) }}"
                       class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300 text-center shadow-sm hover:shadow-md {{ request('rescued_by_me') !== 'true' ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : 'bg-white text-purple-700 hover:bg-purple-50' }}">
                        <i class="fas fa-paw mr-1 text-xs"></i>
                        All Animals
                    </a>
                </div>
            </div>

            @if(request('rescued_by_me') === 'true')
                <div class="mt-2 bg-white bg-opacity-60 backdrop-blur-sm rounded-lg p-2 flex items-center gap-2 text-xs text-purple-800">
                    <i class="fas fa-info-circle text-xs"></i>
                    <span>Showing only animals from rescues you've been assigned to</span>
                </div>
            @endif
        </div>
    @endrole

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-md p-4 md:p-5 mb-6 scale-in border border-purple-100">
        <div class="flex items-center gap-2 mb-4">
            <div class="bg-purple-100 p-2 rounded-lg">
                <i class="fas fa-filter text-purple-700 text-base"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800">Filter Animals</h2>
                <p class="text-xs text-gray-600">Find the perfect companion</p>
            </div>
        </div>

        <form method="GET" action="{{ route('animal-management.index') }}" class="space-y-4">
            <!-- Hidden field to preserve rescued_by_me filter -->
            @if(request('rescued_by_me'))
                <input type="hidden" name="rescued_by_me" value="{{ request('rescued_by_me') }}">
            @endif

            <!-- Filter Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="xl:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-search text-purple-600 text-xs"></i>
                        Search by Name
                    </label>
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Type animal name..."
                               class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                </div>

                <!-- Species -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-dog text-purple-600 text-xs"></i>
                        Species
                    </label>
                    <select name="species"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 appearance-none bg-white cursor-pointer">
                        <option value="">All Species</option>
                        <option value="Dog" {{ request('species') == 'Dog' ? 'selected' : '' }}>🐕 Dog</option>
                        <option value="Cat" {{ request('species') == 'Cat' ? 'selected' : '' }}>🐈 Cat</option>
                    </select>
                </div>

                <!-- Health Condition -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-heartbeat text-purple-600 text-xs"></i>
                        Health
                    </label>
                    <select name="health_details"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 appearance-none bg-white cursor-pointer">
                        <option value="">All Conditions</option>
                        <option value="Healthy" {{ request('health_details') == 'Healthy' ? 'selected' : '' }}>✅ Healthy</option>
                        <option value="Sick" {{ request('health_details') == 'Sick' ? 'selected' : '' }}>🤒 Sick</option>
                        <option value="Need Observation" {{ request('health_details') == 'Need Observation' ? 'selected' : '' }}>👁️ Need Observation</option>
                    </select>
                </div>

                <!-- Adoption Status -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-home text-purple-600 text-xs"></i>
                        Adoption Availability
                    </label>
                    <select name="adoption_status"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 appearance-none bg-white cursor-pointer">
                        <option value="">All Status</option>
                        <option value="Not Adopted" {{ request('adoption_status') == 'Not Adopted' ? 'selected' : '' }}>💚 Available for Adoption</option>
                        <option value="Adopted" {{ request('adoption_status') == 'Adopted' ? 'selected' : '' }}>💙 Adopted</option>
                    </select>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-venus-mars text-purple-600 text-xs"></i>
                        Gender
                    </label>
                    <select name="gender"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 appearance-none bg-white cursor-pointer">
                        <option value="">All Genders</option>
                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>♂️ Male</option>
                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>♀️ Female</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pt-3 border-t border-gray-200">
                <div class="flex flex-wrap gap-2">
                    <button type="submit"
                            class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-5 py-2 text-sm rounded-lg font-bold transition-all duration-300 shadow-md hover:shadow-lg flex items-center gap-1.5">
                        <i class="fas fa-search text-xs"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('animal-management.index') }}"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 text-sm rounded-lg font-bold transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-1.5">
                        <i class="fas fa-redo text-xs"></i>
                        Clear All
                    </a>
                </div>
                <div class="flex items-center gap-1.5 bg-purple-50 px-3 py-1.5 rounded-lg border border-purple-200">
                    <i class="fas fa-paw text-purple-600 text-xs"></i>
                    <p class="text-gray-700 text-xs">
                        Showing <span class="font-bold text-purple-700">{{ $animals->total() }}</span> animal{{ $animals->total() != 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </form>
    </div>

    <!-- Animals Grid or Table -->
    @if($animals->count() > 0)
        @if(request('rescued_by_me') === 'true' && Auth::check() && Auth::user()->hasRole('caretaker'))
            <!-- Table View for Caretakers -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden fade-in border border-purple-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-purple-600 to-indigo-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    <i class="fas fa-paw mr-2"></i>Animal
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    <i class="fas fa-heartbeat mr-2"></i>Health Status
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    <i class="fas fa-dna mr-2"></i>Species & Gender
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    <i class="fas fa-birthday-cake mr-2"></i>Age
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Location
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    <i class="fas fa-home mr-2"></i>Status
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-white uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($animals as $animal)
                                <tr class="hover:bg-purple-50 transition-colors duration-200">
                                    <!-- Animal Info -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 flex-shrink-0 rounded-full overflow-hidden border-2 border-purple-200 shadow-sm">
                                                @if($animal->relationLoaded('images') && $animal->images && $animal->images->count() > 0)
                                                    <img class="h-full w-full object-cover"
                                                         src="{{ $animal->images->first()->url }}"
                                                         alt="{{ $animal->name }}">
                                                @else
                                                    <div class="h-full w-full bg-gradient-to-br from-purple-200 to-indigo-200 flex items-center justify-center text-2xl">
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
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ $animal->name }}</div>
                                                <div class="text-xs text-gray-500">ID: #{{ $animal->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Health Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $healthConfig = match($animal->health_details) {
                                                'Healthy' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-300', 'icon' => '✅'],
                                                'Sick' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-300', 'icon' => '🤒'],
                                                'Need Observation' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'border' => 'border-yellow-300', 'icon' => '👁️'],
                                                default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'icon' => '❓']
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border-2 {{ $healthConfig['bg'] }} {{ $healthConfig['text'] }} {{ $healthConfig['border'] }} shadow-sm">
                                            <span class="mr-1.5">{{ $healthConfig['icon'] }}</span>
                                            {{ $animal->health_details ?? 'Unknown' }}
                                        </span>
                                    </td>

                                    <!-- Species & Gender -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-900">{{ $animal->species }}</span>
                                            @if(strtolower($animal->gender) == 'male')
                                                <span class="text-blue-500 text-lg" title="Male">♂</span>
                                            @else
                                                <span class="text-pink-500 text-lg" title="Female">♀</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Age -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 font-medium">{{ $animal->age }}</span>
                                    </td>

                                    <!-- Location -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if($animal->slot)
                                                <div class="flex items-center gap-1.5">
                                                    <i class="fas fa-map-pin text-purple-500 text-xs"></i>
                                                    <span class="font-medium">Slot {{ $animal->slot->name ?? $animal->slot->id }}</span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $animal->slot->section->name ?? 'Unknown Section' }}</div>
                                            @else
                                                <span class="text-gray-400 italic text-xs">Not Assigned</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Adoption Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($animal->adoption_status == 'Not Adopted')
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 border-2 border-green-300 shadow-sm">
                                                <i class="fas fa-heart mr-1.5"></i>
                                                Available
                                            </span>
                                        @elseif($animal->adoption_status == 'Adopted')
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 border-2 border-blue-300 shadow-sm">
                                                <i class="fas fa-home mr-1.5"></i>
                                                Adopted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border-2 border-gray-300">
                                                {{ $animal->adoption_status }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('animal-management.show', $animal->id) }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white text-sm font-bold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                            <i class="fas fa-eye"></i>
                                            <span>View</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer with Summary -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-t-2 border-purple-200">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <i class="fas fa-info-circle text-purple-600"></i>
                            <span>Showing <span class="font-bold text-purple-700">{{ $animals->count() }}</span> rescued animal{{ $animals->count() != 1 ? 's' : '' }} on this page</span>
                        </div>
                        <div class="text-xs text-gray-600">
                            <i class="fas fa-ambulance text-purple-600 mr-1"></i>
                            Total rescued by you: <span class="font-bold">{{ $animals->total() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Card View for Regular Users -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                @foreach($animals as $animal)
                <div class="animal-card bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-transparent hover:border-purple-200 fade-in">
                    <!-- Animal Image -->
                    <div class="h-56 bg-gradient-to-br from-purple-200 via-purple-300 to-indigo-300 flex items-center justify-center overflow-hidden relative group">
                        @if($animal->relationLoaded('images') && $animal->images && $animal->images->count() > 0)
                            <img src="{{ $animal->images->first()->url }}"
                                 alt="{{ $animal->name }}"
                                 class="animal-image w-full h-full object-cover">
                        @else
                            <div class="animal-image">
                                @if(strtolower($animal->species) == 'dog')
                                    <span class="text-9xl">🐕</span>
                                @elseif(strtolower($animal->species) == 'cat')
                                    <span class="text-9xl">🐈</span>
                                @else
                                    <span class="text-9xl">🐾</span>
                                @endif
                            </div>
                        @endif

                        <!-- Image Overlay Badge -->
                        <div class="absolute top-3 right-3">
                            @if($animal->adoption_status == 'Not Adopted')
                                <span class="inline-flex items-center px-3 py-1.5 bg-green-500 text-white rounded-full text-xs font-bold shadow-lg backdrop-blur-sm">
                                    <i class="fas fa-heart mr-1"></i>
                                    Available for Adoption
                                </span>
                            @elseif($animal->adoption_status == 'Adopted')
                                <span class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-full text-xs font-bold shadow-lg backdrop-blur-sm">
                                    <i class="fas fa-home mr-1"></i>
                                    Adopted
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white rounded-full text-xs font-bold shadow-lg backdrop-blur-sm">
                                    {{ $animal->adoption_status }}
                                </span>
                            @endif
                        </div>

                        <!-- Quick View Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="bg-white text-purple-700 px-4 py-2 rounded-full font-bold shadow-xl">
                                    <i class="fas fa-eye mr-2"></i>View Details
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="p-6">
                        <!-- Name -->
                        <div class="mb-4">
                            <h3 class="text-2xl font-bold text-gray-800 mb-1 flex items-center gap-2">
                                {{ $animal->name }}
                                @if(strtolower($animal->gender) == 'male')
                                    <span class="text-blue-500 text-lg">♂</span>
                                @else
                                    <span class="text-pink-500 text-lg">♀</span>
                                @endif
                            </h3>
                        </div>

                        <!-- Details Grid -->
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center gap-3 text-sm">
                                <div class="bg-purple-100 p-2 rounded-lg">
                                    <i class="fas fa-paw text-purple-600"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-600 text-xs font-semibold uppercase">Species</span>
                                    <p class="text-gray-900 font-bold">{{ $animal->species }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-sm">
                                <div class="bg-indigo-100 p-2 rounded-lg">
                                    <i class="fas fa-birthday-cake text-indigo-600"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-600 text-xs font-semibold uppercase">Age</span>
                                    <p class="text-gray-900 font-bold">{{ $animal->age }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-sm">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-600 text-xs font-semibold uppercase">Location</span>
                                    <p class="text-gray-900 font-bold text-xs">
                                        @if($animal->slot)
                                            Slot {{ $animal->slot->name ?? $animal->slot->id }} - {{ $animal->slot->section->name ?? 'Unknown' }}
                                        @else
                                            <span class="text-gray-400 italic">Not Assigned</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Health Details -->
                        @if($animal->health_details)
                            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-3 rounded-xl mb-4 border border-purple-100">
                                <p class="text-gray-700 text-sm line-clamp-2 leading-relaxed">
                                    <i class="fas fa-info-circle text-purple-600 mr-1"></i>
                                    {{ Str::limit($animal->health_details, 80) }}
                                </p>
                            </div>
                        @endif

                        <!-- Action Button -->
                        <a href="{{ route('animal-management.show', $animal->id) }}"
                           class="block w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white py-3 rounded-xl font-bold transition-all duration-300 text-center shadow-md hover:shadow-xl transform hover:-translate-y-1">
                            <i class="fas fa-arrow-right mr-2"></i>
                            View Full Profile
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination for Card View -->
            <div class="mt-12 flex justify-center fade-in">
                <div class="bg-white rounded-2xl shadow-lg p-4">
                    {{ $animals->links() }}
                </div>
            </div>
        @endif

        <!-- Pagination for Table View -->
        @if(request('rescued_by_me') === 'true' && Auth::check() && Auth::user()->hasRole('caretaker'))
            <div class="mt-6 flex justify-center fade-in">
                <div class="bg-white rounded-2xl shadow-lg p-4">
                    {{ $animals->links() }}
                </div>
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-xl p-12 md:p-16 text-center scale-in">
            <div class="max-w-md mx-auto">
                <div class="bg-purple-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-5xl text-purple-600"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 mb-3">No Animals Found</h3>
                <p class="text-gray-600 mb-8 text-lg">
                    We couldn't find any animals matching your current filters. Try adjusting your search criteria or clear all filters to see all available animals.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('animal-management.index') }}"
                       class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-8 py-4 rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="fas fa-redo"></i>
                        Clear All Filters
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto-dismiss success/error messages
    setTimeout(function() {
        const alerts = document.querySelectorAll('.slide-down');
        alerts.forEach(alert => {
            if (alert.classList.contains('from-green-50') || alert.classList.contains('from-red-50')) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            }
        });
    }, 5000);

    // Add stagger animation to cards
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.animal-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.05}s`;
        });
    });
</script>

</body>
</html>
