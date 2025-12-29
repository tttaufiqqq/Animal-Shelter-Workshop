<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescue #{{ $rescue->id }} - Stray Animals Shelter</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
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

    /* Ensure maps stay below modals and overlays */
    .leaflet-map {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
    }

    /* Fix Leaflet container styling */
    .leaflet-container {
        height: 100%;
        width: 100%;
    }

    /* Ensure loading overlay is above everything */
    #loadingOverlay {
        z-index: 999999 !important;
    }

    /* Ensure modals are above map */
    #remarksModal, #imageModal {
        z-index: 100000 !important;
    }
</style>
<body class="bg-gray-50 min-h-screen">

<!-- Navbar -->
@include('navbar')

<!-- Page Header -->
@include('stray-reporting.partials.rescue-header')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Success Alert --}}
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm fade-in-up">
            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-green-700">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm fade-in-up">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-red-700">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm fade-in-up">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-red-700 mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Responsive Layout: Mobile-First, Desktop-Optimized -->
    <div class="max-w-7xl mx-auto">
        <!-- Mobile: Single Column | Desktop: 2 Columns (60/40 split) -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            <!-- Left Column: Main Content (Mobile: Full Width | Desktop: 3/5 = 60%) -->
            <div class="lg:col-span-3 space-y-6">
                <!-- 1. PHOTOS: See what you're looking for (Large display) -->
                @include('stray-reporting.partials.rescue-images')

                <!-- 2. RESCUE DETAILS: Location, Navigation, Contact -->
                @include('stray-reporting.partials.report-details')

                <!-- MAP: Mobile only - show below details -->
                <div class="block lg:hidden">
                    @include('stray-reporting.partials.location-map', ['mapId' => 'mobile'])
                </div>
            </div>

            <!-- Right Column: Sidebar (Mobile: Full Width | Desktop: 2/5 = 40%) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 3. MAP: Visual reference (desktop only in sidebar) -->
                <div class="hidden lg:block">
                    @include('stray-reporting.partials.location-map', ['mapId' => 'desktop'])
                </div>

                <!-- 4. UPDATE STATUS: Action buttons (prominent in sidebar) -->
                @include('stray-reporting.partials.status-update')
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay (Full Screen) -->
<div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-90 backdrop-blur-md hidden z-[99999] flex items-center justify-center" style="backdrop-filter: blur(8px);">
    <div class="bg-white rounded-lg shadow-2xl p-8 flex flex-col items-center gap-4 border-2 border-gray-200">
        <svg class="animate-spin h-16 w-16 text-purple-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-800 font-bold text-xl">Updating rescue status...</p>
        <p class="text-gray-600 text-base">Please wait</p>
    </div>
</div>

<!-- Modals -->
@include('stray-reporting.modals.image-modal')
@include('stray-reporting.modals.remarks-modal')
@include('stray-reporting.modals.success-remarks-modal')
@include('stray-reporting.modals.animal-addition-modal')

<!-- JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/rescue-status-update.js') }}"></script>
<script>
// ==================== GLOBAL VARIABLE INITIALIZATION ====================
// Variables that use Blade template values must be initialized here

// Rescue ID for this page
const rescueId = {{ $rescue->id }};

// Initialize the Leaflet maps (both mobile and desktop)
function initializeMap(mapId) {
    const mapElement = document.getElementById('map-' + mapId);
    if (!mapElement) return null;

    const map = L.map('map-' + mapId).setView([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const marker = L.marker([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}]).addTo(map);
    marker.bindPopup(`
        <div class="p-2">
            <strong class="text-sm">Rescue Location</strong><br>
            <span class="text-xs">{{ $rescue->report->address }}</span><br>
            <span class="text-xs">{{ $rescue->report->city }}, {{ $rescue->report->state }}</span>
        </div>
    `).openPopup();

    const circle = L.circle([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}], {
        color: '#9333ea',
        fillColor: '#9333ea',
        fillOpacity: 0.15,
        radius: 100
    }).addTo(map);

    return map;
}

// Initialize both maps after DOM is loaded
let mobileMap, desktopMap;
document.addEventListener('DOMContentLoaded', function() {
    mobileMap = initializeMap('mobile');
    desktopMap = initializeMap('desktop');
});

// Initialize rescue images array from Blade template
let rescueImages = [
    @if($rescue->report->images && $rescue->report->images->count() > 0)
        @foreach($rescue->report->images as $image)
            { path: "{{ $image->url }}" },
        @endforeach
    @endif
];
let rescueImageIndex = 0;

// Initialize status variables
let selectedStatus = '';

// Initialize animal form variables
let currentStep = 1;
let totalAnimals = 0;
let currentAnimalIndex = 0;
let addedAnimals = [];
let animalImagesMap = {}; // Store images for each animal

// ==================== EVENT LISTENERS ====================

// Initialize image swiper on page load
rescueDisplayImage();

// Setup image navigation buttons (if multiple images exist)
@if($rescue->report->images && $rescue->report->images->count() > 1)
    document.getElementById('rescuePrevImageBtn').addEventListener('click', rescuePrevImage);
    document.getElementById('rescueNextImageBtn').addEventListener('click', rescueNextImage);
@endif

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeRemarksModal();
    }
    @if($rescue->report->images && $rescue->report->images->count() > 1)
        if (e.key === 'ArrowLeft') rescuePrevImage();
        if (e.key === 'ArrowRight') rescueNextImage();
    @endif
});

// Auto-scroll to alerts and auto-dismiss success alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.fade-in-up');
    if (alerts.length > 0) {
        alerts[0].scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Auto-dismiss success alerts after 5 seconds
        const successAlert = document.querySelector('.bg-green-50');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-20px)';
                successAlert.style.transition = 'all 0.5s ease-out';
                setTimeout(() => successAlert.remove(), 500);
            }, 5000);
        }
    }
});
</script>

<style>
    /* Spinner animation */
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Fade-in animation for step transitions */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    /* Fade-in-up animation for alerts */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Apply fade-in to modal body content */
    #successModalBody > div:not(.hidden) {
        animation: fadeIn 0.4s ease-out;
    }

    /* Smooth progress indicator transitions */
    #progressIndicator > div > div {
        transition: all 0.3s ease;
    }

    /* Input focus effects */
    input:focus, select:focus, textarea:focus {
        outline: none;
    }
</style>
</body>
</html>
