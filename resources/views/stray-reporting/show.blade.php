<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="bg-gradient-to-br from-purple-600 to-purple-800 min-h-screen">
    
    <!-- Include Navbar -->
    @include('navbar')
    <div class="mb-8 bg-gradient-to-r from-purple-600 to-purple-800 shadow-lg p-8 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
               <h1 class="text-4xl font-bold text-white mb-2">
                  <span class="text-4xl md:text-5xl">📋</span>
                  Stray Animal Reports
               </h1>
               <p class="text-purple-100">View and manage all submitted reports</p>
            </div>
         </div>
    

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 md:p-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center">
                        <span class="text-4xl md:text-5xl mr-4">📋</span>
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold">Report Details</h1>
                            <p class="text-purple-100 text-sm md:text-base mt-1">Report ID: #{{ $report->id }}</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 font-semibold px-5 py-3 rounded-lg hover:bg-purple-50 transition duration-300 shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Reports</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="container mx-auto px-4 py-4 max-w-7xl">
                @if(session('success'))
                    <div class="mb-6 px-6 py-4 rounded-2xl bg-green-100 border-l-4 border-green-500 text-green-700 flex items-center gap-3 shadow-lg">
                        <i class="fas fa-check-circle text-lg"></i>
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-green-700 hover:text-green-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>
        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Report Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Report Information</h2>
                            
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                                @if($report->report_status === 'Pending') bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900
                                @elseif($report->report_status === 'Approved') bg-gradient-to-r from-green-500 to-green-600 text-white
                                @elseif($report->report_status === 'Rejected') bg-gradient-to-r from-red-500 to-red-600 text-white
                                @elseif($report->report_status === 'In Progress') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                @elseif($report->report_status === 'Resolved') bg-gradient-to-r from-purple-500 to-purple-600 text-white
                                @else bg-gradient-to-r from-gray-500 to-gray-600 text-white @endif">
                                <i class="fas 
                                    @if($report->report_status === 'Pending') fa-clock
                                    @elseif($report->report_status === 'Approved') fa-check-circle
                                    @elseif($report->report_status === 'Rejected') fa-times-circle
                                    @elseif($report->report_status === 'In Progress') fa-spinner fa-spin
                                    @elseif($report->report_status === 'Resolved') fa-flag-checkered
                                    @else fa-info-circle @endif 
                                mr-2"></i>
                                {{ $report->report_status }}
                            </span>
                        </div>

                        <!-- Report Details Grid -->
                        <div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Location Address</label>
                                    <p class="text-gray-900">{{ $report->address }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                                    <p class="text-gray-900">{{ $report->city }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                                    <p class="text-gray-900">{{ $report->state }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Coordinates</label>
                                    <p class="text-gray-900 text-sm">
                                        Lat: {{ $report->latitude }}, Lng: {{ $report->longitude }}
                                    </p>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                    <p class="text-gray-900">
                                        @if($report->description)
                                            {{ $report->description }}
                                        @else
                                            <span class="text-gray-500 italic">No description provided</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Created Date -->
                            <div class="mt-6 pt-6 border-t border-purple-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Report Date</label>
                                <p class="text-gray-900">{{ $report->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Location Map</h2>
                        <div id="map" class="h-96 rounded-xl shadow-lg bg-gray-200"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Images Section with Swiper -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Report Images</h2>
                        
                        <div class="relative w-full h-64 bg-gray-100 flex items-center justify-center">
                            <!-- Swiper Main Content -->
                            <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center">
                                @if($report->images && $report->images->count() > 0)
                                    <img src="{{ asset('storage/' . $report->images->first()->image_path) }}" 
                                        alt="Report Image 1" 
                                        class="max-w-full max-h-full object-contain">
                                @else
                                    <div class="flex items-center justify-center w-full h-full bg-purple-50 text-5xl text-purple-400">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Navigation Arrows -->
                            <button id="prevImageBtn" class="hidden absolute left-2 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-10 h-10 flex items-center justify-center transition duration-300 z-10">
                                <i class="fas fa-chevron-left text-lg"></i>
                            </button>
                            <button id="nextImageBtn" class="hidden absolute right-2 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-10 h-10 flex items-center justify-center transition duration-300 z-10">
                                <i class="fas fa-chevron-right text-lg"></i>
                            </button>

                            <!-- Image Counter -->
                            <div id="imageCounter" class="hidden absolute bottom-2 right-2 bg-black bg-opacity-70 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-images mr-1"></i>
                                <span id="currentImageIndex">1</span> / <span id="totalImages">{{ $report->images->count() ?: 1 }}</span>
                            </div>
                        </div>

                        <!-- Thumbnail Strip -->
                        <div id="thumbnailContainer" class="mt-4 overflow-x-auto">
                            <div id="thumbnailStrip" class="flex gap-2">
                                @if($report->images && $report->images->count() > 0)
                                    @foreach($report->images as $index => $image)
                                        <div onclick="goToImage({{ $index }})"
                                            class="flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 transition duration-300 {{ $index == 0 ? 'border-green-600' : 'border-gray-300 hover:border-green-400' }}"
                                            id="thumbnail-{{ $index }}">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                alt="Report Image {{ $loop->iteration }}" 
                                                class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 border-gray-300 flex items-center justify-center text-3xl text-purple-400">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Actions</h2>
                        <!-- Assign to Caretaker -->
                        <form action="{{ route('reports.assign-caretaker', $report->id) }}" method="POST" class="mb-6 border-t-2 border-gray-200 pt-6">
                            @csrf
                            @method('PATCH') <!-- This tells Laravel to treat the request as PATCH -->
                            
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Assign to Caretaker</label>
                            <div class="flex gap-2">
                                <select name="caretaker_id" required 
                                        class="flex-1 px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                    <option value="">Select a caretaker...</option>
                                    @foreach($caretakers as $caretaker)
                                        <option value="{{ $caretaker->id }}" 
                                                {{ $report->rescue && $report->rescue->caretakerID == $caretaker->id ? 'selected' : '' }}>
                                            {{ $caretaker->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" 
                                        class="bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold transition duration-200 flex items-center justify-center shadow-lg whitespace-nowrap">
                                    <i class="fas fa-user-plus mr-2"></i> Assign
                                </button>
                            </div>

                            @if($report->rescue && $report->rescue->caretaker)
                                <p class="mt-2 text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Currently assigned to: <strong>{{ $report->rescue->caretaker->name }}</strong>
                                </p>
                            @endif
                        </form>

                        <!-- Action Buttons -->
                        <div class="space-y-3 border-t-2 border-gray-200 pt-6">
                            <form action="{{ route('reports.destroy', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-3 rounded-lg font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-trash mr-2"></i>Delete Report
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 hidden z-50 flex items-center justify-center p-4">
        <div class="max-w-6xl max-h-full">
            <div class="relative">
                <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
                    <i class="fas fa-times text-3xl"></i>
                </button>
                <img id="modalImage" src="" alt="Enlarged view" class="max-w-full max-h-screen rounded-xl shadow-2xl">
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

        // Optional: Add a circle to highlight the area
        const circle = L.circle([{{ $report->latitude }}, {{ $report->longitude }}], {
            color: '#9333ea',
            fillColor: '#9333ea',
            fillOpacity: 0.1,
            radius: 100
        }).addTo(map);

        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target.id === 'imageModal') {
                closeImageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
        // Initialize swiper data
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
    const counter = document.getElementById('imageCounter');

    if (currentImages.length === 0) {
        content.innerHTML = `
            <div class="flex items-center justify-center w-full h-full bg-purple-50 text-5xl text-purple-400">
                <i class="fas fa-image"></i>
            </div>
        `;
        document.getElementById('prevImageBtn').classList.add('hidden');
        document.getElementById('nextImageBtn').classList.add('hidden');
        counter.classList.add('hidden');
        return;
    }

    // Main image
    content.innerHTML = `<img src="${currentImages[currentImageIndex].path}" class="max-w-full max-h-full object-contain">`;

    // Update counter
    document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;
    document.getElementById('totalImages').textContent = currentImages.length;
    counter.classList.remove('hidden');

    // Update thumbnails
    currentImages.forEach((_, index) => {
        const thumb = document.getElementById(`thumbnail-${index}`);
        if (thumb) {
            thumb.className = `flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 transition duration-300 ${
                index === currentImageIndex ? 'border-green-600' : 'border-gray-300 hover:border-green-400'
            }`;
        }
    });

    // Show arrows only if multiple images
    const prevBtn = document.getElementById('prevImageBtn');
    const nextBtn = document.getElementById('nextImageBtn');
    if (currentImages.length > 1) {
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
    } else {
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
    }
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
document.getElementById('prevImageBtn').addEventListener('click', prevImage);
document.getElementById('nextImageBtn').addEventListener('click', nextImage);
document.addEventListener('keydown', function(e) {
    if (currentImages.length === 0) return;
    if (e.key === 'ArrowLeft') prevImage();
    if (e.key === 'ArrowRight') nextImage();
});

// Initialize on page load
displayCurrentImage();

    </script>
</body>
</html>