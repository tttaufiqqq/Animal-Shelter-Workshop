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
                <!-- Images Section -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Report Images</h2>
                        
                        @if($report->images && $report->images->count() > 0)
                            <div class="space-y-4">
                                @foreach($report->images as $image)
                                    <div class="border-2 border-purple-100 rounded-xl overflow-hidden hover:border-purple-300 transition">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                                             alt="Report Image {{ $loop->iteration }}"
                                             class="w-full h-48 object-cover cursor-pointer hover:opacity-90 transition duration-200"
                                             onclick="openImageModal('{{ asset('storage/' . $image->image_path) }}')">
                                        <div class="p-3 bg-purple-50">
                                            <p class="text-sm text-gray-700 text-center font-semibold">Image {{ $loop->iteration }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-purple-50 rounded-xl">
                                <i class="fas fa-image text-purple-300 text-5xl mb-3"></i>
                                <p class="text-gray-600">No images available for this report</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Report Status & Actions</h2>
                        
                        <!-- Status Progress -->
                        <div class="mb-6">
                            <!-- <label class="block text-sm font-semibold text-gray-700 mb-3">Progress</label>
                            
                            Progress Bar
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                                @php
                                    $statusProgress = [
                                        'Pending' => 20,
                                        'Approved' => 40,
                                        'In Progress' => 60,
                                        'Resolved' => 80,
                                        'Completed' => 100
                                    ];
                                    $progress = $statusProgress[$report->report_status] ?? 20;
                                @endphp
                                <div class="bg-gradient-to-r from-purple-600 to-purple-700 h-3 rounded-full transition-all duration-500 shadow-lg" 
                                    style="width: {{ $progress }}%"></div>
                            </div> -->
                            
                            <!-- Current Status -->
                            <!-- <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-gray-700">{{ $report->report_status }}</span>
                                <span class="text-sm font-bold text-purple-700">{{ $progress }}%</span>
                            </div> -->
                        </div>

                        <!-- Quick Status Update -->
                        <form action="{{ route('reports.update-status', $report->id) }}" method="POST" class="mb-6">
                            @csrf
                            @method('PATCH')
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Change Status</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="submit" name="report_status" value="Pending" 
                                        class="bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-yellow-900 px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </button>
                                <button type="submit" name="report_status" value="Approved" 
                                        class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-check mr-1"></i> Approve
                                </button>
                                <button type="submit" name="report_status" value="In Progress" 
                                        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-spinner mr-1"></i> In Progress
                                </button>
                                <button type="submit" name="report_status" value="Resolved" 
                                        class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-3 py-2.5 rounded-lg text-sm font-semibold transition duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-flag-checkered mr-1"></i> Resolve
                                </button>
                            </div>
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
    </script>
</body>
</html>