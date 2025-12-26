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
                                <img src="{{ asset('storage/' . $report->images->first()->image_path) }}"
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
                                        <img src="{{ asset('storage/' . $image->image_path) }}"
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
                            <select name="caretaker_id" required
                                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select caretaker...</option>
                                @foreach($caretakers as $caretaker)
                                    <option value="{{ $caretaker->id }}"
                                            {{ $report->rescue && $report->rescue->caretakerID == $caretaker->id ? 'selected' : '' }}>
                                        {{ $caretaker->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Assign
                            </button>
                        </div>

                        @if($report->rescue && $report->rescue->caretaker)
                            <p class="mt-2 text-xs text-gray-600">
                                Currently assigned to <span class="font-medium text-gray-900">{{ $report->rescue->caretaker->name }}</span>
                            </p>
                        @endif
                    </form>

                    <!-- Delete Button -->
                    <form action="{{ route('reports.destroy', $report->id) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this report? This action cannot be undone.');"
                          class="pt-4 border-t border-gray-200">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 hidden z-50 flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="max-w-6xl max-h-full relative" onclick="event.stopPropagation()">
        <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img id="modalImage" src="" alt="Enlarged view" class="max-w-full max-h-screen rounded">
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

// Image Modal Functions
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Image Swiper Data
let currentImages = [
    @if($report->images && $report->images->count() > 0)
        @foreach($report->images as $image)
            { path: "{{ asset('storage/' . $image->image_path) }}" },
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
</script>
</body>
</html>
