<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stray Animal Reports - Stray Animals Shelter</title>

    {{-- Vite Assets (compiled Tailwind) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Leaflet CSS with error handling --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          onerror="console.warn('Leaflet CSS failed to load - map features may not work')" />

    {{-- CDN Timeout Handler --}}
    <style>
        /* Ensure page is visible even if CDN fails */
        body { opacity: 1 !important; }

        /* Hide any loading overlays after 3 seconds */
        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Include Navbar -->
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

<div class="mb-6 bg-purple-600 shadow p-6">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-white">Stray Animal Reports</h1>
        <p class="text-purple-100 text-sm mt-1">View and manage all submitted reports</p>
    </div>
</div>

<div class="max-w-7xl mx-auto mt-10 p-4 md:p-6 pb-10">
    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Status Filter Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <!-- Total Reports -->
        <a href="{{ route('reports.index') }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ !request('status') ? 'ring-2 ring-purple-500' : '' }}">
            <div class="text-3xl mb-2">📋</div>
            <p class="text-2xl font-bold text-purple-700 mb-1">{{ $totalReports }}</p>
            <p class="text-gray-600 text-sm">Total</p>
        </a>

        <!-- Pending -->
        <a href="{{ route('reports.index', ['status' => 'Pending']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Pending' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="text-3xl mb-2">⏳</div>
            <p class="text-2xl font-bold text-yellow-600 mb-1">{{ $statusCounts['Pending'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Pending</p>
        </a>

        <!-- In Progress -->
        <a href="{{ route('reports.index', ['status' => 'In Progress']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'In Progress' ? 'ring-2 ring-blue-500' : '' }}">
            <div class="text-3xl mb-2">🔄</div>
            <p class="text-2xl font-bold text-blue-600 mb-1">{{ $statusCounts['In Progress'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">In Progress</p>
        </a>

        <!-- Resolved -->
        <a href="{{ route('reports.index', ['status' => 'Resolved']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Resolved' ? 'ring-2 ring-green-500' : '' }}">
            <div class="text-3xl mb-2">✅</div>
            <p class="text-2xl font-bold text-green-600 mb-1">{{ $statusCounts['Resolved'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Resolved</p>
        </a>
    </div>

    <!-- Search and Filter Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
            <!-- Keep current status filter -->
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Search & Filter Reports</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- User Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reporter Name or Email</label>
                    <div class="relative">
                        <input type="text"
                               name="user_search"
                               value="{{ request('user_search') }}"
                               placeholder="Search by reporter name or email..."
                               class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Location Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <input type="text"
                           name="location"
                           value="{{ request('location') }}"
                           placeholder="City, state, or address..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Report ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report ID</label>
                    <input type="number"
                           name="report_id"
                           value="{{ request('report_id') }}"
                           placeholder="e.g. 123"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Date To (spans to align with From Date) -->
                <div class="lg:col-start-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition duration-300 flex items-center gap-2 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search
                </button>
                <a href="{{ route('reports.index') }}"
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition duration-300 flex items-center gap-2 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </a>

                @if(request()->hasAny(['user_search', 'location', 'report_id', 'date_from', 'date_to']))
                    <div class="ml-auto flex items-center text-sm text-purple-600 font-medium">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ collect(request()->only(['user_search', 'location', 'report_id', 'date_from', 'date_to']))->filter()->count() }} filter(s) active</span>
                    </div>
                @endif
            </div>
        </form>
    </div>

    @if($reports->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center">
            <p class="text-gray-600">No stray animal reports have been submitted.</p>
        </div>
    @else
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Report </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">City/State</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Images</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">REP {{ $report->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($report->report_status == 'Pending') bg-yellow-100 text-yellow-800
                                    @elseif($report->report_status == 'In Progress') bg-blue-100 text-blue-800
                                    @elseif($report->report_status == 'Resolved') bg-green-100 text-green-800
                                    @endif">
                                    {{ $report->report_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $report->address }}">{{ $report->address }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $report->city }}, {{ $report->state }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $report->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                @if($report->images->count() > 0)
                                    <a href="#" onclick="event.preventDefault(); showImagesModal({{ $report->id }}, {{ json_encode($report->images->map(fn($img) => asset('storage/' . $img->image_path))) }})" class="text-purple-600 hover:underline">
                                        {{ $report->images->count() }} image(s)
                                    </a>
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a href="{{ route('reports.show', $report->id) }}" class="text-purple-600 hover:underline mr-3">View</a>
                                <a href="#" onclick="event.preventDefault(); showMapModal({{ $report->latitude }}, {{ $report->longitude }}, '{{ addslashes($report->address) }}')" class="text-purple-600 hover:underline">Map</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    @endif
</div>

{{-- Map Modal --}}
<div id="mapModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeMapModal()">
    <div class="bg-white rounded shadow-lg max-w-4xl w-full" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center p-4 border-b">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Location Map</h3>
                <p id="mapModalAddress" class="text-sm text-gray-600 mt-1"></p>
            </div>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="modalMap" class="w-full" style="height: 400px;"></div>
    </div>
</div>

{{-- Images Modal --}}
<div id="imagesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeImagesModal()">
    <div class="bg-white rounded shadow-lg max-w-4xl w-full max-h-[90vh] overflow-auto" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white">
            <h3 class="text-lg font-semibold text-gray-900">Report Images</h3>
            <button onclick="closeImagesModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="imagesContainer" class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
    </div>
</div>

{{-- Leaflet JS with error handling --}}
<script>
    // Load Leaflet with timeout and error handling
    (function() {
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onerror = function() {
            console.warn('Leaflet JS failed to load - map features disabled');
            window.LEAFLET_AVAILABLE = false;
        };
        script.onload = function() {
            window.LEAFLET_AVAILABLE = true;
        };

        // Set timeout to prevent hanging
        const timeout = setTimeout(() => {
            if (typeof L === 'undefined') {
                console.warn('Leaflet loading timeout - map features disabled');
                window.LEAFLET_AVAILABLE = false;
            }
        }, 5000);

        script.addEventListener('load', () => clearTimeout(timeout));
        document.head.appendChild(script);
    })();
</script>

<script>
    let modalMapInstance = null;

    // Ensure page loads even if resources fail
    window.addEventListener('DOMContentLoaded', function() {
        // Remove any loading overlays
        document.body.style.opacity = '1';
        document.body.style.visibility = 'visible';
    });

    // Map modal functions with error handling
    function showMapModal(lat, lng, address) {
        // Check if Leaflet is available
        if (typeof L === 'undefined' || window.LEAFLET_AVAILABLE === false) {
            alert('Map feature is currently unavailable. Please check your internet connection.');
            return;
        }

        document.getElementById('mapModalAddress').textContent = address;
        document.getElementById('mapModal').classList.remove('hidden');

        setTimeout(() => {
            try {
                if (modalMapInstance) {
                    modalMapInstance.remove();
                }

                modalMapInstance = L.map('modalMap').setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(modalMapInstance);

                L.marker([lat, lng]).addTo(modalMapInstance);
            } catch (error) {
                console.error('Map initialization failed:', error);
                alert('Failed to load map. Please check your internet connection.');
                closeMapModal();
            }
        }, 100);
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
        if (modalMapInstance) {
            modalMapInstance.remove();
            modalMapInstance = null;
        }
    }

    // Images modal functions
    function showImagesModal(reportId, images) {
        const container = document.getElementById('imagesContainer');
        container.innerHTML = '';

        if (!images || images.length === 0) {
            container.innerHTML = '<p class="text-gray-500 col-span-full text-center py-8">No images available</p>';
            document.getElementById('imagesModal').classList.remove('hidden');
            return;
        }

        images.forEach(imagePath => {
            const div = document.createElement('div');
            div.className = 'cursor-pointer relative';

            const img = document.createElement('img');
            img.src = imagePath;
            img.alt = 'Report Image';
            img.className = 'w-full h-40 object-cover rounded border hover:opacity-75 transition';
            img.onclick = () => openFullImage(imagePath);

            // Add error handling for images that fail to load
            img.onerror = function() {
                this.onerror = null; // Prevent infinite loop
                this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZSBub3QgZm91bmQ8L3RleHQ+PC9zdmc+';
                this.className = 'w-full h-40 object-contain rounded border bg-gray-100';
                const errorMsg = document.createElement('p');
                errorMsg.className = 'text-xs text-red-500 mt-1 text-center';
                errorMsg.textContent = 'Image not found';
                this.parentElement.appendChild(errorMsg);
            };

            // Add loading state
            img.onload = function() {
                this.classList.add('loaded');
            };

            div.appendChild(img);
            container.appendChild(div);
        });

        document.getElementById('imagesModal').classList.remove('hidden');
    }

    function closeImagesModal() {
        document.getElementById('imagesModal').classList.add('hidden');
    }

    function openFullImage(imageSrc) {
        window.open(imageSrc, '_blank');
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMapModal();
            closeImagesModal();
        }
    });
</script>
</body>
</html>
