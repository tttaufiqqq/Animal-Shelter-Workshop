<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescue Details - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="bg-white min-h-screen">

    <!-- Navbar -->
    @include('navbar')

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 md:p-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center">
                        <span class="text-4xl md:text-5xl mr-4">🚑</span>
                        <div>
                           <h1 class="text-3xl md:text-4xl font-bold">Rescue Details</h1>
                           <div class="flex items-center gap-2 mt-1">
                              <p class="text-purple-100 text-sm md:text-base">Rescue ID: #{{ $rescue->id }}</p>
                              
                              <!-- Status Badge -->
                              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                    @if($rescue->status === 'Scheduled') bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900
                                    @elseif($rescue->status === 'In Progress') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                    @elseif($rescue->status === 'Success') bg-gradient-to-r from-green-500 to-green-600 text-white
                                    @elseif($rescue->status === 'Failed') bg-gradient-to-r from-red-500 to-red-600 text-white
                                    @else bg-gray-500 text-white @endif">
                                    <i class="fas 
                                       @if($rescue->status === 'Scheduled') fa-calendar-alt
                                       @elseif($rescue->status === 'In Progress') fa-rocket
                                       @elseif($rescue->status === 'Success') fa-check-circle
                                       @elseif($rescue->status === 'Failed') fa-times-circle
                                       @else fa-info-circle @endif
                                    mr-2"></i>
                                    {{ $rescue->status }}
                              </span>
                           </div>
                        </div>

                    </div>
                    <a href="{{ route('rescues.index') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 font-semibold px-5 py-3 rounded-lg hover:bg-purple-50 transition duration-300 shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Rescues</span>
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
                        
                        @if($errors->any())
                           <div class="mb-6 px-6 py-4 rounded-2xl bg-red-100 border-l-4 border-red-500 text-red-700 shadow-lg">
                              <div class="flex items-start gap-3">
                                 <i class="fas fa-exclamation-circle text-lg mt-0.5"></i>
                                 <div class="flex-1">
                                    <p class="font-semibold mb-2">Please fix the following errors:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                       @foreach($errors->all() as $error)
                                          <li>{{ $error }}</li>
                                       @endforeach
                                    </ul>
                                 </div>
                                 <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">
                                    <i class="fas fa-times"></i>
                                 </button>
                              </div>
                           </div>
                        @endif
                     </div>
        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Rescue & Report Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Report Information</h2>
                        </div>

                        <!-- Report Details Grid -->
                        <div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Location Address</label>
                                    <p class="text-gray-900">{{ $rescue->report->address }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                                    <p class="text-gray-900">{{ $rescue->report->city }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                                    <p class="text-gray-900">{{ $rescue->report->state }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Coordinates</label>
                                    <p class="text-gray-900 text-sm">
                                        Lat: {{ $rescue->report->latitude }}, Lng: {{ $rescue->report->longitude }}
                                    </p>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                    <p class="text-gray-900">
                                        @if($rescue->report->description)
                                            {{ $rescue->report->description }}
                                        @else
                                            <span class="text-gray-500 italic">No description provided</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Created Date -->
                            <div class="mt-6 pt-6 border-t border-purple-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Report Date</label>
                                <p class="text-gray-900">{{ $rescue->report->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                  <div class="p-6 md:p-8">
                     <div class="flex items-center justify-between mb-4">
                           <h2 class="text-2xl font-bold text-gray-800">Location Map</h2>
                           <!-- Go to Location Button -->
                           <a href="https://www.google.com/maps/search/?api=1&query={{ $rescue->report->latitude }},{{ $rescue->report->longitude }}" 
                              target="_blank"
                              class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold shadow transition duration-200">
                              <i class="fas fa-location-arrow"></i> Go to Location
                           </a>
                     </div>
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
                            <!-- Main Image Display -->
                            <div id="rescueImageSwiperContent" class="w-full h-full flex items-center justify-center">
                                @if($rescue->report->images && $rescue->report->images->count() > 0)
                                    <img src="{{ asset('storage/' . $rescue->report->images->first()->image_path) }}" 
                                        alt="Report Image 1" 
                                        class="max-w-full max-h-full object-contain">
                                @else
                                    <div class="flex items-center justify-center w-full h-full bg-purple-50 text-5xl text-purple-400">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Navigation Arrows -->
                            <button id="rescuePrevImageBtn" class="hidden absolute left-2 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-10 h-10 flex items-center justify-center transition duration-300 z-10">
                                <i class="fas fa-chevron-left text-lg"></i>
                            </button>
                            <button id="rescueNextImageBtn" class="hidden absolute right-2 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-10 h-10 flex items-center justify-center transition duration-300 z-10">
                                <i class="fas fa-chevron-right text-lg"></i>
                            </button>

                            <!-- Image Counter -->
                            <div id="rescueImageCounter" class="hidden absolute bottom-2 right-2 bg-black bg-opacity-70 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-images mr-1"></i>
                                <span id="rescueCurrentImageIndex">1</span> / <span id="rescueTotalImages">{{ $rescue->report->images->count() ?: 1 }}</span>
                            </div>
                        </div>

                        <!-- Thumbnail Strip -->
                        <div id="rescueThumbnailContainer" class="mt-4 overflow-x-auto">
                            <div id="rescueThumbnailStrip" class="flex gap-2">
                                @if($rescue->report->images && $rescue->report->images->count() > 0)
                                    @foreach($rescue->report->images as $index => $image)
                                        <div onclick="rescueGoToImage({{ $index }})"
                                            class="flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 transition duration-300 {{ $index == 0 ? 'border-green-600' : 'border-gray-300 hover:border-green-400' }}"
                                            id="rescueThumbnail-{{ $index }}">
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


                <!-- Status Info -->
               @php
                $isFinal = in_array($rescue->status, ['Success', 'Failed']);
                @endphp

                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden p-6 md:p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Update Rescue Status</h2>

                <form id="statusForm" action="{{ route('rescues.update-status', $rescue->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-2 gap-2 {{ $isFinal ? 'pointer-events-none opacity-50' : '' }}">
                        
                        <button type="button" onclick="updateStatus('Scheduled')" 
                            class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900 px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-alt mr-1"></i> Scheduled
                        </button>

                        <button type="button" onclick="updateStatus('In Progress')" 
                            class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                            <i class="fas fa-rocket mr-1"></i> In Progress
                        </button>

                        <button type="button" onclick="updateStatus('Success')" 
                            class="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                            <i class="fas fa-check-circle mr-1"></i> Success
                        </button>

                        <button type="button" onclick="updateStatus('Failed')" 
                            class="bg-gradient-to-r from-red-500 to-red-600 text-white px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                            <i class="fas fa-times-circle mr-1"></i> Failed
                        </button>

                    </div>
                </form>
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

    <!-- Remarks Modal -->
    <div id="remarksModal" class="fixed inset-0 bg-black bg-opacity-70 hidden z-[9999] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all">
            <div class="p-6 md:p-8">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div id="modalIcon" class="text-3xl"></div>
                        <h3 id="modalTitle" class="text-2xl font-bold text-gray-800"></h3>
                    </div>
                    <button onclick="closeRemarksModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Form -->
                <form id="remarksForm" onsubmit="submitStatusUpdate(event)">
                    <div class="mb-6">
                        <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                            Remarks <span class="text-red-500">*</span>
                        </label>
                        <textarea id="remarks" name="remarks" rows="5" required
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none transition resize-none"
                                  placeholder="Please provide details about this rescue status..."></textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Please provide a detailed explanation for this status update.
                        </p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex gap-3">
                        <button type="button" onclick="closeRemarksModal()"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition duration-200">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" id="submitBtn"
                                class="flex-1 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-lg">
                            <i class="fas fa-check mr-2"></i>Confirm
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

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add a marker for the report location
        const marker = L.marker([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}]).addTo(map);
        marker.bindPopup(`
            <div class="p-2">
                <strong class="text-sm">Report Location</strong><br>
                <span class="text-xs">{{ $rescue->report->address }}</span><br>
                <span class="text-xs">{{ $rescue->report->city }}, {{ $rescue->report->state }}</span>
            </div>
        `).openPopup();

        const circle = L.circle([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}], {
            color: '#9333ea',
            fillColor: '#9333ea',
            fillOpacity: 0.1,
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

        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target.id === 'imageModal') closeImageModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
                closeRemarksModal();
            }
        });

        // Status Update with Remarks Modal
        let selectedStatus = '';

        function updateStatus(status) {
            selectedStatus = status;
            
            // Check if status requires remarks (Success or Failed)
            if (status === 'Success' || status === 'Failed') {
                openRemarksModal(status);
            } else {
                // Submit directly for Scheduled and In Progress
                submitStatusDirectly(status);
            }
        }

        function openRemarksModal(status) {
            const modal = document.getElementById('remarksModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalIcon = document.getElementById('modalIcon');
            const submitBtn = document.getElementById('submitBtn');
            const remarksTextarea = document.getElementById('remarks');
            
            // Clear previous remarks
            remarksTextarea.value = '';
            
            // Configure modal based on status
            if (status === 'Success') {
                modalTitle.textContent = 'Rescue Successful';
                modalIcon.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                submitBtn.className = 'flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-lg';
                remarksTextarea.placeholder = 'Describe how the rescue was completed successfully...';
            } else if (status === 'Failed') {
                modalTitle.textContent = 'Rescue Failed';
                modalIcon.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                submitBtn.className = 'flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-lg';
                remarksTextarea.placeholder = 'Explain why the rescue could not be completed...';
            }
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Focus on textarea
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
            
            // Create a form and submit
            const form = document.getElementById('statusForm');
            
            // Add status input
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = selectedStatus;
            
            // Add remarks input
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
            
            // Add status input
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            
            form.appendChild(statusInput);
            form.submit();
        }

        // Close modal when clicking outside
        document.getElementById('remarksModal').addEventListener('click', function(e) {
            if (e.target.id === 'remarksModal') {
                closeRemarksModal();
            }
        });
        // Rescue report images swiper
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
    const counter = document.getElementById('rescueImageCounter');

    if (rescueImages.length === 0) {
        content.innerHTML = `<div class="flex items-center justify-center w-full h-full bg-purple-50 text-5xl text-purple-400">
                                <i class="fas fa-image"></i>
                             </div>`;
        document.getElementById('rescuePrevImageBtn').classList.add('hidden');
        document.getElementById('rescueNextImageBtn').classList.add('hidden');
        counter.classList.add('hidden');
        return;
    }

    content.innerHTML = `<img src="${rescueImages[rescueImageIndex].path}" class="max-w-full max-h-full object-contain">`;

    document.getElementById('rescueCurrentImageIndex').textContent = rescueImageIndex + 1;
    document.getElementById('rescueTotalImages').textContent = rescueImages.length;
    counter.classList.remove('hidden');

    // Update thumbnails
    rescueImages.forEach((_, index) => {
        const thumb = document.getElementById(`rescueThumbnail-${index}`);
        if (thumb) {
            thumb.className = `flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 transition duration-300 ${
                index === rescueImageIndex ? 'border-green-600' : 'border-gray-300 hover:border-green-400'
            }`;
        }
    });

    const prevBtn = document.getElementById('rescuePrevImageBtn');
    const nextBtn = document.getElementById('rescueNextImageBtn');
    if (rescueImages.length > 1) {
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
    } else {
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
    }
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

document.getElementById('rescuePrevImageBtn').addEventListener('click', rescuePrevImage);
document.getElementById('rescueNextImageBtn').addEventListener('click', rescueNextImage);
document.addEventListener('keydown', function(e) {
    if (rescueImages.length === 0) return;
    if (e.key === 'ArrowLeft') rescuePrevImage();
    if (e.key === 'ArrowRight') rescueNextImage();
});

// Initialize on page load
rescueDisplayImage();

    </script>
</body>
</html>