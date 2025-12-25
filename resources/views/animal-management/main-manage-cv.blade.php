<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinics & Vets - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
    <div id="connectivityBanner" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($dbDisconnected as $connection => $info)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ $info['module'] }}
                    </span>
                    @endforeach
                </div>
            </div>
            <button onclick="closeConnectivityBanner()" class="flex-shrink-0 ml-4 text-yellow-400 hover:text-yellow-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <script>
        function closeConnectivityBanner() {
            document.getElementById('connectivityBanner').style.display = 'none';
        }
    </script>
@endif

<!-- Page Header -->
<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-2">Clinics & Veterinarians</h1>
        <p class="text-purple-100">Our trusted medical partners for animal care</p>
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
    <!-- Add New Clinic/Vet Cards --> @role('admin')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Add Clinic Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-8 text-white">
                <div class="flex items-center mb-2">
                    <span class="text-4xl mr-4">🏥</span>
                    <h2 class="text-2xl font-bold">Add New Clinic</h2>
                </div>
                <p class="text-blue-100">Register a new veterinary clinic</p>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Add clinics that provide medical services for our shelter animals.</p>
                <button onclick="openModal('clinic')" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 transition duration-300 shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Add Clinic
                </button>
            </div>
        </div>

        <!-- Add Vet Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-white">
                <div class="flex items-center mb-2">
                    <span class="text-4xl mr-4">👨‍⚕️</span>
                    <h2 class="text-2xl font-bold">Add New Veterinarian</h2>
                </div>
                <p class="text-green-100">Register a new veterinarian</p>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Add veterinarians who provide care for our animals.</p>
                <button onclick="openModal('vet')" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-3 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition duration-300 shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Add Veterinarian
                </button>
            </div>
        </div>
    </div> @endrole

    <!-- Clinics Section -->
    <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-hospital text-blue-600 mr-3"></i>
                Clinics
            </h2>
            <span class="text-gray-600">{{ $clinics->count() }} Clinics</span>
        </div>

        @if($clinics->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $colors = ['blue', 'cyan', 'teal', 'indigo', 'sky', 'violet', 'purple', 'fuchsia'];
                @endphp

                @foreach($clinics as $index => $clinic)
                    @php
                        $color = $colors[$index % count($colors)];
                    @endphp

                        <!-- Clinic Card -->
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                        <div class="h-32 bg-gradient-to-br from-{{ $color }}-400 to-{{ $color }}-500 flex items-center justify-center">
                            <span class="text-6xl">🏥</span>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $clinic->name }}</h3>
                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <p class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-{{ $color }}-600 mt-1 mr-2 flex-shrink-0"></i>
                                    <span>{{ $clinic->address }}</span>
                                </p>
                                <p class="flex items-center">
                                    <i class="fas fa-phone text-{{ $color }}-600 mr-2"></i>
                                    <span>{{ $clinic->contactNum }}</span>
                                </p>
                                @if($clinic->latitude && $clinic->longitude)
                                    <p class="flex items-center">
                                        <i class="fas fa-location-dot text-{{ $color }}-600 mr-2"></i>
                                        <span class="text-xs">{{ number_format($clinic->latitude, 4) }}, {{ number_format($clinic->longitude, 4) }}</span>
                                    </p>
                                @endif
                            </div>
                            <div class="flex space-x-2">
                                @if($clinic->latitude && $clinic->longitude)
                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $clinic->latitude }},{{ $clinic->longitude }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="flex-1 bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white py-2 rounded-lg font-medium transition duration-300 text-center">
                                        <i class="fas fa-location-arrow mr-1"></i>Go to Clinic
                                    </a>
                                @else
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($clinic->address) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="flex-1 bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white py-2 rounded-lg font-medium transition duration-300 text-center">
                                        <i class="fas fa-location-arrow mr-1"></i>Go to Clinic
                                    </a>
                                @endif
                                @role('admin')
                                <button onclick="editClinic({{ $clinic->id }})" class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteClinic({{ $clinic->id }})" class="px-4 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition duration-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endrole
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">🏥</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No Clinics Yet</h3>
                <p class="text-gray-600 mb-6">Start by adding your first veterinary clinic</p>
                @role('admin')<button onclick="openModal('clinic')" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add First Clinic
                </button>@endrole
            </div>
        @endif
    </div>

    <!-- Edit Clinic Modal -->
    <div id="editClinicModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold">Edit Clinic</h2>
                    <button onclick="closeEditClinicModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <form id="editClinicForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Clinic Name <span class="text-red-600">*</span></label>
                    <input type="text" id="edit_clinic_name" name="name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="Enter clinic name" required>
                </div>

                <!-- Address Search -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Search Address</label>
                    <div class="flex gap-2">
                        <input type="text" id="editClinicAddressSearch" placeholder="Enter address to search..."
                               class="flex-1 border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition">
                        <button type="button" id="editClinicSearchBtn"
                                class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-300">
                            <i class="fas fa-search mr-1"></i>Search
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Search for an address or click on the map to pin a location</p>
                </div>

                <!-- Map -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Select Location on Map <span class="text-red-600">*</span>
                    </label>
                    <div id="editClinicMap" class="w-full h-64 rounded-lg border border-gray-300"></div>
                    <p class="text-sm text-red-600 mt-2 hidden" id="editClinicMapError">Please select a location on the map</p>
                </div>

                <!-- Latitude & Longitude -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Latitude <span class="text-red-600">*</span></label>
                        <input type="text" id="edit_clinicLatitude" name="latitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="Auto-filled" readonly required>
                    </div>
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Longitude <span class="text-red-600">*</span></label>
                        <input type="text" id="edit_clinicLongitude" name="longitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="Auto-filled" readonly required>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Address <span class="text-red-600">*</span></label>
                    <textarea id="edit_clinicAddress" name="address" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" rows="3" placeholder="Full address will be auto-filled" required></textarea>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                    <input type="tel" id="edit_phone" name="contactNum" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="+60 3-1234 5678" required>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditClinicModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-300">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Veterinarians Section -->
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-md text-green-600 mr-3"></i>
                Vets
            </h2>
            <span class="text-gray-600">{{ $vets->count() }} Veterinarian{{ $vets->count() != 1 ? 's' : '' }}</span>
        </div>

        @if($vets->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    // Array of gradient colors for variety
                    $colors = ['green', 'emerald', 'lime', 'teal', 'cyan'];
                @endphp

                @foreach($vets as $index => $vet)
                    @php
                        // Cycle through colors
                        $color = $colors[$index % count($colors)];
                    @endphp

                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                        <div class="h-32 bg-gradient-to-br from-{{ $color }}-400 to-{{ $color }}-500 flex items-center justify-center">
                            <span class="text-6xl">{{ $index % 2 == 0 ? '👨‍⚕️' : '👩‍⚕️' }}</span>
                        </div>
                        <div class="p-6">
                            <div class="mb-2">
                                <h3 class="text-xl font-bold text-gray-800">{{ $vet->name }}</h3>
                                <p class="text-sm text-{{ $color }}-600 font-semibold">DVM, {{ $vet->specialization }}</p>
                            </div>
                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <!-- Clinic Name -->
                                <p class="flex items-center">
                                    <i class="fas fa-hospital text-{{ $color }}-600 mr-2"></i>
                                    <span>{{ $vet->clinic ? $vet->clinic->name : 'No clinic assigned' }}</span>
                                </p>

                                <!-- Phone -->
                                <p class="flex items-center">
                                    <i class="fas fa-phone text-{{ $color }}-600 mr-2"></i>
                                    <span>{{ $vet->contactNum }}</span>
                                </p>

                                <!-- Email -->
                                <p class="flex items-center">
                                    <i class="fas fa-envelope text-{{ $color }}-600 mr-2"></i>
                                    <span>{{ $vet->email }}</span>
                                </p>

                                <!-- License Number -->
                                <p class="flex items-center">
                                    <i class="fas fa-id-card text-{{ $color }}-600 mr-2"></i>
                                    <span>License: {{ $vet->license_no }}</span>
                                </p>

                                <!-- Years of Experience (if available) -->
                                @if(isset($vet->years_of_experience))
                                    <p class="flex items-center">
                                        <i class="fas fa-graduation-cap text-{{ $color }}-600 mr-2"></i>
                                        <span>{{ $vet->years_of_experience }} years experience</span>
                                    </p>
                                @endif
                            </div>
                            <div class="flex space-x-2">
                                @role('admin')
                                <button onclick="editVet({{ $vet->id }})" class="flex-1 bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white py-2 rounded-lg font-medium transition duration-300">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button onclick="deleteVet({{ $vet->id }})" class="px-4 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition duration-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endrole
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">👨‍⚕️</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No Veterinarians Yet</h3>
                <p class="text-gray-600 mb-6">Start by adding your first veterinarian to the system.</p>
                @role('admin')<button onclick="openModal('vet')" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300 shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Add First Veterinarian
                </button>@endrole
            </div>
        @endif
    </div>

    <!-- Modal for Add Clinics (Hidden by default) -->
    <div id="clinicModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold">Add New Clinic</h2>
                    <button onclick="closeModal('clinic')" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('animal-management.store-clinics') }}" class="p-6 space-y-4">
                @csrf
                @method('POST')
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Clinic Name <span class="text-red-600">*</span></label>
                    <input type="text" name="clinic_name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Enter clinic name" required>
                </div>

                <!-- Address Search -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Search Address</label>
                    <div class="flex gap-2">
                        <input type="text" id="clinicAddressSearch" placeholder="Enter address to search..."
                               class="flex-1 border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                        <button type="button" id="clinicSearchBtn"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300">
                            <i class="fas fa-search mr-1"></i>Search
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Search for an address or click on the map to pin a location</p>
                </div>

                <!-- Map -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Select Location on Map <span class="text-red-600">*</span>
                    </label>
                    <div id="clinicMap" class="w-full h-64 rounded-lg border border-gray-300"></div>
                    <p class="text-sm text-red-600 mt-2 hidden" id="clinicMapError">Please select a location on the map</p>
                </div>

                <!-- Latitude & Longitude -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Latitude <span class="text-red-600">*</span></label>
                        <input type="text" id="clinicLatitude" name="latitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Auto-filled" readonly required>
                    </div>
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Longitude <span class="text-red-600">*</span></label>
                        <input type="text" id="clinicLongitude" name="longitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Auto-filled" readonly required>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Address <span class="text-red-600">*</span></label>
                    <textarea id="clinicAddress" name="address" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" rows="3" placeholder="Full address will be auto-filled" required></textarea>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                    <input type="tel" name="phone" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="+60 3-1234 5678" required>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal('clinic')" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300">
                        <i class="fas fa-plus mr-2"></i>Add Clinic
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Add Vet -->
    <div id="vetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold">Add New Vet</h2>
                    <button onclick="closeModal('vet')" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('animal-management.store-vets') }}" class="p-6 space-y-4">
                @csrf
                @method('POST')
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Full Name <span class="text-red-600">*</span></label>
                    <input type="text" name="full_name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="Dr. Name" required>
                </div>
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Specialization <span class="text-red-600">*</span></label>
                    <input type="text" name="specialization" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="e.g., Small Animals, Surgery" required>
                </div>
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">License Number <span class="text-red-600">*</span></label>
                    <input type="text" name="license_no" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="e.g., 408688" required>
                </div>
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Clinic <span class="text-red-600">*</span></label>
                    <select name="clinicID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white">
                        @if($clinics->count() > 0)
                            <option value="">Select Clinic</option>
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        @else
                            <option disabled>No clinics available — please add one first.</option>
                        @endif
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                        <input type="tel" name="phone" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="+60 12-345 6789" required>
                    </div>
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Email <span class="text-red-600">*</span></label>
                        <input type="email" name="email" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="dr.name@example.com" required>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal('vet')" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300">
                        <i class="fas fa-plus mr-2"></i>Add Veterinarian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Vet Modal -->
    <div id="editVetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold">Edit Veterinarian</h2>
                    <button onclick="closeEditVetModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <form id="editVetForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Full Name <span class="text-red-600">*</span></label>
                    <input type="text" id="edit_vet_name" name="name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="Dr. Name" required>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Specialization <span class="text-red-600">*</span></label>
                    <input type="text" id="edit_vet_specialization" name="specialization" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="e.g., Small Animals, Surgery" required>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">License Number <span class="text-red-600">*</span></label>
                    <input type="text" id="edit_vet_license_no" name="license_no" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="e.g., 408688" required>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Clinic <span class="text-red-600">*</span></label>
                    <select id="edit_vet_clinicID" name="clinicID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-orange-500 focus:ring focus:ring-orange-200 transition" required>
                        @if($clinics->count() > 0)
                            <option value="">Select Clinic</option>
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        @else
                            <option disabled>No clinics available — please add one first.</option>
                        @endif
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                        <input type="tel" id="edit_vet_contactNum" name="contactNum" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="+60 12-345 6789" required>
                    </div>
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">Email <span class="text-red-600">*</span></label>
                        <input type="email" id="edit_vet_email" name="email" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="dr.name@example.com" required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditVetModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-semibold rounded-lg hover:from-orange-600 hover:to-orange-700 transition duration-300">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        let clinicMap;
        let clinicMarker;

        // Open modal function
        function openModal(type) {
            if (type === 'clinic') {
                document.getElementById('clinicModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                // Initialize map after modal is visible
                setTimeout(() => {
                    initClinicMap();
                }, 100);
            } else if (type === 'vet') {
                document.getElementById('vetModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        // Close modal function - FIXED
        function closeModal(type) {
            if (type === 'clinic') {
                document.getElementById('clinicModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                // Reset map
                if (clinicMap) {
                    clinicMap.remove();
                    clinicMap = null;
                    clinicMarker = null;
                }
                // Reset form fields
                document.getElementById('clinicLatitude').value = '';
                document.getElementById('clinicLongitude').value = '';
                document.getElementById('clinicAddress').value = '';
                document.getElementById('clinicAddressSearch').value = '';
                document.getElementById('clinicMapError').classList.add('hidden');
            } else if (type === 'vet') {
                document.getElementById('vetModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal when clicking outside
        document.getElementById('clinicModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('clinic');
            }
        });

        document.getElementById('vetModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('vet');
            }
        });

        // Initialize map when modal opens
        function initClinicMap() {
            if (!clinicMap) {
                // Default center to Malaysia (Seremban, Negeri Sembilan)
                clinicMap = L.map('clinicMap').setView([2.7258, 101.9424], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(clinicMap);

                // Fix map display issue
                setTimeout(() => {
                    clinicMap.invalidateSize();
                }, 200);

                // Click on map to pin location
                clinicMap.on('click', function (e) {
                    const { lat, lng } = e.latlng;
                    updateClinicLocation(lat, lng);

                    // Reverse geocode to get address when clicking on map
                    reverseGeocodeClinic(lat, lng);
                });
            }
        }

        // Function to update marker and form fields
        function updateClinicLocation(lat, lng, address = '') {
            if (clinicMarker) {
                clinicMarker.setLatLng([lat, lng]);
            } else {
                clinicMarker = L.marker([lat, lng]).addTo(clinicMap);
            }

            document.getElementById('clinicLatitude').value = lat.toFixed(6);
            document.getElementById('clinicLongitude').value = lng.toFixed(6);

            if (address) {
                document.getElementById('clinicAddress').value = address;
            }

            clinicMap.setView([lat, lng], 15);

            // Hide map error when location is selected
            document.getElementById('clinicMapError').classList.add('hidden');
        }

        // Reverse geocode function
        function reverseGeocodeClinic(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('clinicAddress').value = data.display_name;
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding error:', error);
                });
        }

        // Search address functionality
        function searchClinicAddress() {
            const query = document.getElementById('clinicAddressSearch').value.trim();
            const searchBtn = document.getElementById('clinicSearchBtn');

            if (!query) {
                alert('Please enter an address to search');
                return;
            }

            // Show loading state
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Searching...';
            searchBtn.disabled = true;

            // Try first search with Malaysia country code
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=my`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        const address = result.display_name;

                        updateClinicLocation(lat, lng, address);
                    } else {
                        // If no results with country code, try without it but add "Malaysia" to query
                        return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Malaysia')}&limit=5`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    const result = data[0];
                                    const lat = parseFloat(result.lat);
                                    const lng = parseFloat(result.lon);
                                    const address = result.display_name;

                                    updateClinicLocation(lat, lng, address);
                                } else {
                                    alert('Address not found. Please try:\n\n1. A more specific address (include area/city)\n2. Click directly on the map instead\n3. Use a well-known landmark nearby');
                                }
                            });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error searching address. Please try again or click on the map to select location manually.');
                })
                .finally(() => {
                    searchBtn.innerHTML = '<i class="fas fa-search mr-1"></i>Search';
                    searchBtn.disabled = false;
                });
        }

        // Add event listeners when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Search on button click
            const searchBtn = document.getElementById('clinicSearchBtn');
            if (searchBtn) {
                searchBtn.addEventListener('click', searchClinicAddress);
            }

            // Search on Enter key
            const addressSearch = document.getElementById('clinicAddressSearch');
            if (addressSearch) {
                addressSearch.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchClinicAddress();
                    }
                });
            }

            // Form validation
            const clinicForm = document.querySelector('#clinicModal form');
            if (clinicForm) {
                clinicForm.addEventListener('submit', function(e) {
                    const latitude = document.getElementById('clinicLatitude').value;
                    const longitude = document.getElementById('clinicLongitude').value;

                    if (!latitude || !longitude) {
                        e.preventDefault();
                        document.getElementById('clinicMapError').classList.remove('hidden');
                        document.getElementById('clinicMap').scrollIntoView({ behavior: 'smooth', block: 'center' });

                        alert('Please select a location on the map before submitting the form.');
                        return false;
                    }
                });
            }
        });
        let editClinicMap;
        let editClinicMarker;
        let editClinicGeocoder;

        // Initialize edit map when modal opens
        function initEditClinicMap(lat, lng) {
            if (!editClinicMap) {
                editClinicMap = L.map('editClinicMap').setView([lat || 2.7297, lng || 101.9381], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(editClinicMap);

                editClinicMap.on('click', function(e) {
                    setEditClinicLocation(e.latlng.lat, e.latlng.lng);
                });
            } else {
                editClinicMap.setView([lat || 2.7297, lng || 101.9381], 13);
            }

            // Set initial marker if coordinates exist
            if (lat && lng) {
                setEditClinicLocation(lat, lng);
            }

            // Fix map display issue
            setTimeout(() => {
                editClinicMap.invalidateSize();
            }, 100);
        }

        function setEditClinicLocation(lat, lng) {
            if (editClinicMarker) {
                editClinicMap.removeLayer(editClinicMarker);
            }

            editClinicMarker = L.marker([lat, lng]).addTo(editClinicMap);
            editClinicMap.setView([lat, lng], 15);

            document.getElementById('edit_clinicLatitude').value = lat.toFixed(6);
            document.getElementById('edit_clinicLongitude').value = lng.toFixed(6);

            // Reverse geocode to get address
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('edit_clinicAddress').value = data.display_name;
                    }
                });
        }

        // Search address for edit modal
        document.getElementById('editClinicSearchBtn')?.addEventListener('click', function() {
            const address = document.getElementById('editClinicAddressSearch').value;
            if (!address) return;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        setEditClinicLocation(parseFloat(result.lat), parseFloat(result.lon));
                    } else {
                        alert('Address not found. Please try a different search term.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error searching for address');
                });
        });

        // Edit Clinic Function
        function editClinic(clinicId) {
            fetch(`/clinics/${clinicId}/edit`)
                .then(response => response.json())
                .then(data => {
                    // Populate form fields
                    document.getElementById('edit_clinic_name').value = data.name || '';
                    document.getElementById('edit_clinicAddress').value = data.address || '';
                    document.getElementById('edit_phone').value = data.contactNum || '';
                    document.getElementById('edit_clinicLatitude').value = data.latitude || '';
                    document.getElementById('edit_clinicLongitude').value = data.longitude || '';

                    // Update form action
                    document.getElementById('editClinicForm').action = `/clinics/${clinicId}`;

                    // Show modal
                    document.getElementById('editClinicModal').classList.remove('hidden');

                    // Initialize map with clinic location
                    setTimeout(() => {
                        initEditClinicMap(parseFloat(data.latitude), parseFloat(data.longitude));
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load clinic data');
                });
        }

        // Close Edit Modal
        function closeEditClinicModal() {
            document.getElementById('editClinicModal').classList.add('hidden');
            document.getElementById('editClinicForm').reset();

            // Clear marker
            if (editClinicMarker && editClinicMap) {
                editClinicMap.removeLayer(editClinicMarker);
                editClinicMarker = null;
            }
        }

        // Close modal when clicking outside
        document.getElementById('editClinicModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditClinicModal();
            }
        });

        // Delete Clinic Function
        function deleteClinic(clinicId) {
            if (confirm('Are you sure you want to delete this clinic? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/clinics/${clinicId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
        function editVet(vetId) {
            console.log('Edit vet clicked:', vetId); // Debug

            const modal = document.getElementById('editVetModal');
            if (!modal) {
                console.error('Modal not found!');
                return;
            }

            modal.classList.remove('hidden');

            const url = `/vets/${vetId}/edit`;

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Vet data received:', data); // Debug

                    // Check if elements exist before setting values
                    const nameInput = document.getElementById('edit_vet_name');
                    const specializationInput = document.getElementById('edit_vet_specialization');
                    const licenseInput = document.getElementById('edit_vet_license_no');
                    const clinicSelect = document.getElementById('edit_vet_clinicID');
                    const contactInput = document.getElementById('edit_vet_contactNum');
                    const emailInput = document.getElementById('edit_vet_email');

                    if (!nameInput || !specializationInput || !licenseInput || !clinicSelect || !contactInput || !emailInput) {
                        console.error('One or more form fields not found!');
                        console.log('Name:', nameInput);
                        console.log('Specialization:', specializationInput);
                        console.log('License:', licenseInput);
                        console.log('Clinic:', clinicSelect);
                        console.log('Contact:', contactInput);
                        console.log('Email:', emailInput);
                        alert('Error: Form fields not found. Please refresh the page.');
                        return;
                    }

                    // Populate form fields
                    nameInput.value = data.name || '';
                    specializationInput.value = data.specialization || '';
                    licenseInput.value = data.license_no || '';
                    clinicSelect.value = data.clinicID || '';
                    contactInput.value = data.contactNum || '';
                    emailInput.value = data.email || '';

                    // Update form action
                    document.getElementById('editVetForm').action = `/vets/${vetId}`;
                })
                .catch(error => {
                    console.error('Error details:', error);
                    alert('Failed to load veterinarian data: ' + error.message);
                    closeEditVetModal();
                });
        }

        // Close Edit Vet Modal
        function closeEditVetModal() {
            const modal = document.getElementById('editVetModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            const form = document.getElementById('editVetForm');
            if (form) {
                form.reset();
            }
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editVetModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditVetModal();
                    }
                });
            }
        });

        // Delete Vet Function
        function deleteVet(vetId) {
            if (confirm('Are you sure you want to delete this veterinarian? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/vets/${vetId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }

    </script>
</body>
</html>
