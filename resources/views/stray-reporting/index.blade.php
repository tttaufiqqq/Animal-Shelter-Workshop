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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Report #</th>
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
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">#{{ $report->id }}</td>
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

        images.forEach(imagePath => {
            const div = document.createElement('div');
            div.className = 'cursor-pointer';
            div.innerHTML = `
                    <img src="${imagePath}"
                         alt="Report Image"
                         class="w-full h-40 object-cover rounded border hover:opacity-75"
                         onclick="openFullImage('${imagePath}')">
                `;
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
