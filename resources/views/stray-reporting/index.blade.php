<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stray Animal Reports - Stray Animals Shelter</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="bg-gradient-to-br from-purple-600 to-purple-800 min-h-screen">

    <!-- Include Navbar -->
    @include('navbar')

    <div class="max-w-7xl mx-auto mt-10 p-4 md:p-6 pb-10">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 md:p-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center">
                        <span class="text-4xl md:text-5xl mr-4">📋</span>
                        <div>
                            <h2 class="text-3xl md:text-4xl font-bold">Stray Animal Reports</h2>
                            <p class="text-purple-100 text-sm md:text-base mt-1">View and manage all submitted reports</p>
                        </div>
                    </div>
                    <!-- <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 font-semibold px-5 py-3 rounded-lg hover:bg-purple-50 transition duration-300 shadow-lg">
                        <span class="text-lg">➕</span>
                        <span>New Report</span>
                    </a> -->
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4">
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($reports->isEmpty())
            <div class="bg-white rounded-2xl shadow-2xl p-12 text-center">
                <div class="text-6xl mb-4">🐾</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No reports yet</h3>
                <p class="text-gray-600 mb-6 text-lg">Get started by creating a new report to help stray animals.</p>
                <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold px-6 py-3 rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                    <span class="text-lg">📝</span>
                    <span>Submit First Report</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach($reports as $report)
                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <div class="p-6 md:p-8">
                            <div class="flex justify-between items-start mb-6 flex-wrap gap-3">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-2xl">📍</span>
                                        <h3 class="text-2xl font-bold text-gray-800">Report #{{ $report->id }}</h3>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Submitted on {{ $report->created_at->format('M d, Y - h:i A') }}
                                    </p>
                                </div>
                                <span class="px-4 py-2 rounded-full text-sm font-semibold
                                    @if($report->report_status == 'Pending') bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900
                                    @elseif($report->report_status == 'In Progress') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                    @elseif($report->report_status == 'Resolved') bg-gradient-to-r from-green-500 to-green-600 text-white
                                    @endif">
                                    {{ $report->report_status }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                {{-- Location Details --}}
                                <div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded-lg">
                                    <h4 class="font-bold text-gray-800 mb-4 text-lg">Location Details</h4>
                                    <div class="space-y-3 text-sm">
                                        <div>
                                            <span class="font-semibold text-gray-700">Address:</span>
                                            <p class="text-gray-800 mt-1">{{ $report->address }}</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <span class="font-semibold text-gray-700">City:</span>
                                                <p class="text-gray-800 mt-1">{{ $report->city }}</p>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-700">State:</span>
                                                <p class="text-gray-800 mt-1">{{ $report->state }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <span class="font-semibold text-gray-700">Latitude:</span>
                                                <p class="text-gray-800 mt-1">{{ $report->latitude }}</p>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-700">Longitude:</span>
                                                <p class="text-gray-800 mt-1">{{ $report->longitude }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($report->description)
                                        <div class="mt-4 pt-4 border-t border-purple-200">
                                            <span class="font-semibold text-gray-700 text-sm">Description:</span>
                                            <p class="text-gray-800 mt-2">{{ $report->description }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Map --}}
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-3 text-lg">Map Location</h4>
                                    <div id="map-{{ $report->id }}" class="rounded-xl shadow-lg" style="height: 250px;"></div>
                                </div>
                            </div>

                            {{-- Images --}}
                            @if($report->images->count() > 0)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="font-bold text-gray-800 mb-4 text-lg">
                                        Attached Images ({{ $report->images->count() }})
                                    </h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                        @foreach($report->images as $image)
                                            <div class="relative group">
                                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                     alt="Report Image" 
                                                     class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-75 transition shadow-md"
                                                     onclick="openImageModal('{{ asset('storage/' . $image->image_path) }}')">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition rounded-lg flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end gap-3 flex-wrap">
                                <a href="{{ route('reports.show', $report->id) }}" 
                                   class="px-5 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                                    View Details
                                </a>
                                <!-- @if($report->report_status == 'Pending')
                                    <a href="{{ route('reports.edit', $report->id) }}" 
                                       class="px-5 py-3 bg-white border-2 border-purple-600 text-purple-600 font-semibold rounded-lg hover:bg-purple-50 transition duration-300">
                                        Edit Report
                                    </a>
                                @endif -->
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6 bg-white rounded-xl shadow-lg p-4">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    {{-- Image Modal --}}
    <div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" onclick="closeImageModal()">
        <div class="relative max-w-6xl max-h-full">
            <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-screen rounded-xl shadow-2xl">
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize maps for each report
            @foreach($reports as $report)
                const map{{ $report->id }} = L.map('map-{{ $report->id }}').setView([{{ $report->latitude }}, {{ $report->longitude }}], 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map{{ $report->id }});

                L.marker([{{ $report->latitude }}, {{ $report->longitude }}]).addTo(map{{ $report->id }});
            @endforeach
        });

        // Image modal functions
        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }
    </script>
</body>
</html>