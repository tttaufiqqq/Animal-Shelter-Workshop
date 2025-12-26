<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #{{ $report->id }} - Stray Animals Shelter</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
<body class="bg-gray-50 min-h-screen">

<!-- Include Navbar -->
@include('navbar')

<!-- Page Header -->
<div class="bg-purple-600 shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">Report #{{ $report->id }}</h1>
                <p class="text-purple-100 text-sm mt-1">Submitted on {{ $report->created_at->format('M j, Y') }}</p>
            </div>
            <a href="{{ route('reports.index') }}"
               class="inline-flex items-center gap-2 bg-white text-purple-700 px-4 py-2 rounded-lg hover:bg-purple-50 transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Reports
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border-l-4 border-green-500 rounded">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif
        @if (session('error'))
            <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm mx-6 mt-6">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <p class="font-semibold text-red-700">{{ session('error') }}</p>
            </div>
        @endif
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Report Information Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Report Information</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($report->report_status === 'Pending') bg-yellow-100 text-yellow-800
                            @elseif($report->report_status === 'In Progress') bg-blue-100 text-blue-800
                            @elseif($report->report_status === 'Resolved') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $report->report_status }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 pb-4 border-b border-gray-200">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Reported By</label>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $report->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $report->user->email ?? 'No email available' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Location Address</label>
                            <p class="text-sm text-gray-900">{{ $report->address }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">City</label>
                            <p class="text-sm text-gray-900">{{ $report->city }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">State</label>
                            <p class="text-sm text-gray-900">{{ $report->state }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Coordinates</label>
                            <p class="text-sm text-gray-900">{{ number_format($report->latitude, 6) }}, {{ number_format($report->longitude, 6) }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Description</label>
                            <p class="text-sm text-gray-900">
                                @if($report->description)
                                    {{ $report->description }}
                                @else
                                    <span class="text-gray-400 italic">No description provided</span>
                                @endif
                            </p>
                        </div>

                        <div class="md:col-span-2 pt-4 border-t border-gray-200">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Submitted</label>
                            <p class="text-sm text-gray-900">{{ $report->created_at->format('M j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Location Map</h2>
                </div>
                <div class="p-6">
                    <div id="map" class="h-96 rounded border border-gray-200 bg-gray-100"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Images Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Images</h2>
                </div>
                <div class="p-6">
                    <div class="relative w-full h-64 bg-gray-100 rounded border border-gray-200 overflow-hidden">
                        <!-- Main Image Display -->
                        <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center">
                            @if($report->images && $report->images->count() > 0)
                                <img src="{{ $report->images->first()->url }}"
                                    alt="Report Image 1"
                                    class="max-w-full max-h-full object-contain cursor-pointer"
                                    onclick="openImageModal(this.src)">
                            @else
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm mt-2">No images</span>
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Arrows -->
                        @if($report->images && $report->images->count() > 1)
                            <button id="prevImageBtn" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button id="nextImageBtn" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <!-- Image Counter -->
                            <div id="imageCounter" class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white px-2 py-1 rounded text-xs">
                                <span id="currentImageIndex">1</span> / <span id="totalImages">{{ $report->images->count() }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Thumbnail Strip -->
                    @if($report->images && $report->images->count() > 1)
                        <div class="mt-4 overflow-x-auto">
                            <div class="flex gap-2">
                                @foreach($report->images as $index => $image)
                                    <div onclick="goToImage({{ $index }})"
                                        class="flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 {{ $index == 0 ? 'border-purple-500' : 'border-gray-200 hover:border-purple-300' }}"
                                        id="thumbnail-{{ $index }}">
                                        <img src="{{ $image->url }}"
                                            alt="Thumbnail {{ $loop->iteration }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Assign to Caretaker -->
                    <form action="{{ route('reports.assign-caretaker', $report->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Assign to Caretaker</label>
                        <div class="flex gap-2">
                            <select name="caretaker_id" required id="caretakerSelect"
                                    class="flex-1 px-3 py-2 text-sm border @error('caretaker_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select caretaker...</option>
                                @foreach($caretakers as $caretaker)
                                    <option value="{{ $caretaker->id }}"
                                            {{ old('caretaker_id') == $caretaker->id || ($report->rescue && $report->rescue->caretakerID == $caretaker->id) ? 'selected' : '' }}>
                                        {{ $caretaker->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" id="assignBtn"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                <span id="assignBtnText">Assign</span>
                            </button>
                        </div>

                        @error('caretaker_id')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if($report->rescue && $report->rescue->caretaker)
                            <p class="mt-2 text-xs text-gray-600">
                                Currently assigned to <span class="font-medium text-gray-900">{{ $report->rescue->caretaker->name }}</span>
                            </p>
                        @endif
                    </form>

                    <!-- Delete Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <button type="button" onclick="openDeleteModal()" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Delete Report</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 backdrop-blur-md hidden z-[9999] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300" onclick="closeImageModal()">
    <div class="max-w-6xl max-h-full relative transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()" id="imageModalContent">
        <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img id="modalImage" src="" alt="Enlarged view" class="max-w-full max-h-screen rounded">
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[9999] p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()" id="deleteModalContent">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 rounded-t-2xl">
            <div class="flex items-center gap-3">
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">Delete Report</h2>
                    <p class="text-red-100 text-sm">Report #{{ $report->id }}</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <p class="text-gray-700 mb-4 text-lg font-medium">Are you sure you want to delete this report?</p>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800 mb-1">Warning: This action cannot be undone!</p>
                        <p class="text-sm text-red-700">All information associated with this report will be permanently deleted, including:</p>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                            <li>Report details and location</li>
                            <li>All uploaded images</li>
                            <li>Assignment history</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 p-6 border-t border-gray-200 flex gap-3 rounded-b-2xl">
            <button type="button"
                    onclick="closeDeleteModal()"
                    id="cancelDeleteBtn"
                    class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                No, Keep Report
            </button>

            <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="flex-1" id="deleteReportForm">
                @csrf
                @method('DELETE')
                <button type="submit"
                        id="confirmDeleteBtn"
                        class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-300 flex items-center gap-2 justify-center">
                    <svg class="w-5 h-5" id="deleteIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span id="confirmDeleteBtnText">Yes, Delete Report</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize the map
const map = L.map('map').setView([{{ $report->latitude }}, {{ $report->longitude }}], 15);

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Add a marker for the report location
const marker = L.marker([{{ $report->latitude }}, {{ $report->longitude }}]).addTo(map);

// Add popup with location info
marker.bindPopup(`
    <div class="p-2">
        <strong class="text-sm">Report Location</strong><br>
        <span class="text-xs">{{ $report->address }}</span><br>
        <span class="text-xs">{{ $report->city }}, {{ $report->state }}</span>
    </div>
`).openPopup();

// Add a circle to highlight the area
const circle = L.circle([{{ $report->latitude }}, {{ $report->longitude }}], {
    color: '#9333ea',
    fillColor: '#9333ea',
    fillOpacity: 0.15,
    radius: 100
}).addTo(map);

// Disable/Enable Map Interactions
function disableMapInteractions() {
    // Close any open popups
    map.closePopup();

    // Disable all interactions
    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
    map.boxZoom.disable();
    map.keyboard.disable();
    if (map.tap) map.tap.disable();

    // Make map completely non-interactive
    document.getElementById('map').style.pointerEvents = 'none';
    document.getElementById('map').style.zIndex = '1';

    // Disable marker interactions
    marker.closePopup();
    marker.off('click');
}

function enableMapInteractions() {
    // Re-enable all interactions
    map.dragging.enable();
    map.touchZoom.enable();
    map.doubleClickZoom.enable();
    map.scrollWheelZoom.enable();
    map.boxZoom.enable();
    map.keyboard.enable();
    if (map.tap) map.tap.enable();

    // Restore interactivity
    document.getElementById('map').style.pointerEvents = 'auto';
    document.getElementById('map').style.zIndex = '';

    // Re-enable marker click
    marker.on('click', function() {
        this.openPopup();
    });
}

// Image Modal Functions
function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalContent = document.getElementById('imageModalContent');

    document.getElementById('modalImage').src = imageSrc;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Disable map interactions
    disableMapInteractions();

    // Trigger animation
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    const modalContent = document.getElementById('imageModalContent');

    // Trigger close animation
    modal.classList.add('opacity-0');
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');

    // Re-enable map interactions
    enableMapInteractions();

    // Hide after animation completes
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('imageModal').classList.contains('hidden')) {
        closeImageModal();
    }
});

// Image Swiper Data
let currentImages = [
    @if($report->images && $report->images->count() > 0)
        @foreach($report->images as $image)
            { path: "{{ $image->url }}" },
        @endforeach
    @endif
];
let currentImageIndex = 0;

// Display current image
function displayCurrentImage() {
    const content = document.getElementById('imageSwiperContent');

    if (currentImages.length === 0) return;

    // Update main image
    content.innerHTML = `<img src="${currentImages[currentImageIndex].path}"
                              class="max-w-full max-h-full object-contain cursor-pointer"
                              onclick="openImageModal(this.src)">`;

    // Update counter
    if (document.getElementById('currentImageIndex')) {
        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;
    }

    // Update thumbnails
    currentImages.forEach((_, index) => {
        const thumb = document.getElementById(`thumbnail-${index}`);
        if (thumb) {
            thumb.className = `flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 ${
                index === currentImageIndex ? 'border-purple-500' : 'border-gray-200 hover:border-purple-300'
            }`;
        }
    });
}

// Go to image by index
function goToImage(index) {
    if (index >= 0 && index < currentImages.length) {
        currentImageIndex = index;
        displayCurrentImage();
    }
}

// Navigation buttons
function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % currentImages.length;
    displayCurrentImage();
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
    displayCurrentImage();
}

// Event listeners
@if($report->images && $report->images->count() > 1)
    document.getElementById('prevImageBtn').addEventListener('click', prevImage);
    document.getElementById('nextImageBtn').addEventListener('click', nextImage);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === 'ArrowRight') nextImage();
    });
@endif

// Initialize on page load
displayCurrentImage();

// ==================== FORM SUBMISSION LOADING STATES ====================

// Assign Caretaker Form
document.querySelector('form[action*="assign-caretaker"]').addEventListener('submit', function(e) {
    const assignBtn = document.getElementById('assignBtn');
    const assignBtnText = document.getElementById('assignBtnText');
    const caretakerSelect = document.getElementById('caretakerSelect');

    // Disable button and select
    assignBtn.disabled = true;
    caretakerSelect.disabled = true;

    // Add spinner and update text
    assignBtn.innerHTML = `
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Assigning...</span>
    `;

    // Allow form to submit
    return true;
});

// ==================== DELETE MODAL FUNCTIONS ====================

// Open delete confirmation modal
function openDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    const modalContent = document.getElementById('deleteModalContent');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Disable map interactions
    disableMapInteractions();

    // Trigger animation
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

// Close delete confirmation modal
function closeDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    const modalContent = document.getElementById('deleteModalContent');

    // Trigger close animation
    modal.classList.add('opacity-0');
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');

    // Re-enable map interactions
    enableMapInteractions();

    // Hide after animation completes
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

// Delete Report Form submission with loading state
document.getElementById('deleteReportForm').addEventListener('submit', function(e) {
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelBtn = document.getElementById('cancelDeleteBtn');
    const deleteIcon = document.getElementById('deleteIcon');
    const deleteText = document.getElementById('confirmDeleteBtnText');

    // Disable buttons
    deleteBtn.disabled = true;
    cancelBtn.disabled = true;

    // Replace content with spinner
    deleteBtn.innerHTML = `
        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Deleting...</span>
    `;

    // Allow form to submit
    return true;
});

// Close modal when clicking outside
document.getElementById('deleteConfirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('deleteConfirmModal')?.classList.contains('hidden')) {
        closeDeleteModal();
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
</style>
</body>
</html>
