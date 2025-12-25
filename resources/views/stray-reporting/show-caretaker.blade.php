<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescue #{{ $rescue->id }} - Stray Animals Shelter</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Navbar -->
@include('navbar')

<!-- Page Header -->
<div class="bg-purple-600 shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-white">Rescue #{{ $rescue->id }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        @if($rescue->status === 'Scheduled') bg-yellow-100 text-yellow-800
                        @elseif($rescue->status === 'In Progress') bg-blue-100 text-blue-800
                        @elseif($rescue->status === 'Success') bg-green-100 text-green-800
                        @elseif($rescue->status === 'Failed') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $rescue->status }}
                    </span>
                </div>
                <p class="text-purple-100 text-sm mt-1">Assigned on {{ $rescue->created_at->format('M j, Y') }}</p>
            </div>
            <a href="{{ route('rescues.index') }}"
               class="inline-flex items-center gap-2 bg-white text-purple-700 px-4 py-2 rounded-lg hover:bg-purple-50 transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Rescues
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

    @if($errors->any())
        <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border-l-4 border-red-500 rounded">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Report Information Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Report Information</h2>
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
                                    <p class="text-sm font-medium text-gray-900">{{ $rescue->report->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $rescue->report->user->email ?? 'No email available' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Location Address</label>
                            <p class="text-sm text-gray-900">{{ $rescue->report->address }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">City</label>
                            <p class="text-sm text-gray-900">{{ $rescue->report->city }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">State</label>
                            <p class="text-sm text-gray-900">{{ $rescue->report->state }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Coordinates</label>
                            <p class="text-sm text-gray-900">{{ number_format($rescue->report->latitude, 6) }}, {{ number_format($rescue->report->longitude, 6) }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Description</label>
                            <p class="text-sm text-gray-900">
                                @if($rescue->report->description)
                                    {{ $rescue->report->description }}
                                @else
                                    <span class="text-gray-400 italic">No description provided</span>
                                @endif
                            </p>
                        </div>

                        <div class="md:col-span-2 pt-4 border-t border-gray-200">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Report Submitted</label>
                            <p class="text-sm text-gray-900">{{ $rescue->report->created_at->format('M j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Location Map</h2>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $rescue->report->latitude }},{{ $rescue->report->longitude }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Open in Maps
                    </a>
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
                        <div id="rescueImageSwiperContent" class="w-full h-full flex items-center justify-center">
                            @if($rescue->report->images && $rescue->report->images->count() > 0)
                                <img src="{{ asset('storage/' . $rescue->report->images->first()->image_path) }}"
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
                        @if($rescue->report->images && $rescue->report->images->count() > 1)
                            <button id="rescuePrevImageBtn" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button id="rescueNextImageBtn" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <!-- Image Counter -->
                            <div id="rescueImageCounter" class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white px-2 py-1 rounded text-xs">
                                <span id="rescueCurrentImageIndex">1</span> / <span id="rescueTotalImages">{{ $rescue->report->images->count() }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Thumbnail Strip -->
                    @if($rescue->report->images && $rescue->report->images->count() > 1)
                        <div class="mt-4 overflow-x-auto">
                            <div class="flex gap-2">
                                @foreach($rescue->report->images as $index => $image)
                                    <div onclick="rescueGoToImage({{ $index }})"
                                        class="flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 {{ $index == 0 ? 'border-purple-500' : 'border-gray-200 hover:border-purple-300' }}"
                                        id="rescueThumbnail-{{ $index }}">
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

            <!-- Status Update Card -->
            @php
                $isFinal = in_array($rescue->status, ['Success', 'Failed']);
            @endphp

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Update Status</h2>
                </div>
                <div class="p-6">
                    @if($isFinal)
                        <div class="flex items-start gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <p class="text-sm text-gray-600">This rescue has been finalized and cannot be updated.</p>
                        </div>
                    @else
                        <form id="statusForm" action="{{ route('rescues.update-status', $rescue->id) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" onclick="updateStatus('Scheduled')"
                                        class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Scheduled
                                </button>

                                <button type="button" onclick="updateStatus('In Progress')"
                                        class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    In Progress
                                </button>

                                <button type="button" onclick="updateStatus('Success')"
                                        class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Success
                                </button>

                                <button type="button" onclick="updateStatus('Failed')"
                                        class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Failed
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 hidden z-[10000] flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="max-w-6xl max-h-full relative" onclick="event.stopPropagation()">
        <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img id="modalImage" src="" alt="Enlarged view" class="max-w-full max-h-screen rounded">
    </div>
</div>

<!-- Remarks Modal -->
<div id="remarksModal" class="fixed inset-0 bg-black bg-opacity-70 hidden z-[10000] flex items-center justify-center p-4" onclick="closeRemarksModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div id="modalIcon" class="text-2xl"></div>
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-900"></h3>
                </div>
                <button onclick="closeRemarksModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="remarksForm" onsubmit="submitStatusUpdate(event)">
                <div class="mb-6">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                        Remarks <span class="text-red-600">*</span>
                    </label>
                    <textarea id="remarks" name="remarks" rows="5" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none text-sm"
                              placeholder="Please provide details about this rescue status..."></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        Please provide a detailed explanation for this status update.
                    </p>
                </div>

                <!-- Modal Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeRemarksModal()"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 rounded-lg transition-colors text-sm">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            class="flex-1 font-medium py-2 rounded-lg transition-colors text-sm text-white">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize the map
const map = L.map('map').setView([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}], 15);

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

// Image Swiper
let rescueImages = [
    @if($rescue->report->images && $rescue->report->images->count() > 0)
        @foreach($rescue->report->images as $image)
            { path: "{{ asset('storage/' . $image->image_path) }}" },
        @endforeach
    @endif
];
let rescueImageIndex = 0;

function rescueDisplayImage() {
    const content = document.getElementById('rescueImageSwiperContent');

    if (rescueImages.length === 0) return;

    content.innerHTML = `<img src="${rescueImages[rescueImageIndex].path}"
                              class="max-w-full max-h-full object-contain cursor-pointer"
                              onclick="openImageModal(this.src)">`;

    if (document.getElementById('rescueCurrentImageIndex')) {
        document.getElementById('rescueCurrentImageIndex').textContent = rescueImageIndex + 1;
    }

    rescueImages.forEach((_, index) => {
        const thumb = document.getElementById(`rescueThumbnail-${index}`);
        if (thumb) {
            thumb.className = `flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 ${
                index === rescueImageIndex ? 'border-purple-500' : 'border-gray-200 hover:border-purple-300'
            }`;
        }
    });
}

function rescueGoToImage(index) {
    if (index >= 0 && index < rescueImages.length) {
        rescueImageIndex = index;
        rescueDisplayImage();
    }
}

function rescueNextImage() {
    rescueImageIndex = (rescueImageIndex + 1) % rescueImages.length;
    rescueDisplayImage();
}

function rescuePrevImage() {
    rescueImageIndex = (rescueImageIndex - 1 + rescueImages.length) % rescueImages.length;
    rescueDisplayImage();
}

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

// Initialize images
rescueDisplayImage();

// Status Update with Remarks Modal
let selectedStatus = '';

function updateStatus(status) {
    selectedStatus = status;

    if (status === 'Success' || status === 'Failed') {
        openRemarksModal(status);
    } else {
        submitStatusDirectly(status);
    }
}

function openRemarksModal(status) {
    const modal = document.getElementById('remarksModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalIcon = document.getElementById('modalIcon');
    const submitBtn = document.getElementById('submitBtn');
    const remarksTextarea = document.getElementById('remarks');

    // Close any open map popups to prevent overlap
    map.closePopup();

    remarksTextarea.value = '';

    if (status === 'Success') {
        modalTitle.textContent = 'Rescue Successful';
        modalIcon.innerHTML = '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        submitBtn.className = 'flex-1 bg-green-600 hover:bg-green-700 font-medium py-2 rounded-lg transition-colors text-sm text-white';
        remarksTextarea.placeholder = 'Describe how the rescue was completed successfully...';
    } else if (status === 'Failed') {
        modalTitle.textContent = 'Rescue Failed';
        modalIcon.innerHTML = '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        submitBtn.className = 'flex-1 bg-red-600 hover:bg-red-700 font-medium py-2 rounded-lg transition-colors text-sm text-white';
        remarksTextarea.placeholder = 'Explain why the rescue could not be completed...';
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => remarksTextarea.focus(), 100);
}

function closeRemarksModal() {
    document.getElementById('remarksModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    selectedStatus = '';
}

function submitStatusUpdate(event) {
    event.preventDefault();

    const remarks = document.getElementById('remarks').value.trim();

    if (!remarks) {
        alert('Please provide remarks before submitting.');
        return;
    }

    const form = document.getElementById('statusForm');

    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = selectedStatus;

    const remarksInput = document.createElement('input');
    remarksInput.type = 'hidden';
    remarksInput.name = 'remarks';
    remarksInput.value = remarks;

    form.appendChild(statusInput);
    form.appendChild(remarksInput);
    form.submit();
}

function submitStatusDirectly(status) {
    const form = document.getElementById('statusForm');

    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = status;

    form.appendChild(statusInput);
    form.submit();
}
</script>
</body>
</html>
