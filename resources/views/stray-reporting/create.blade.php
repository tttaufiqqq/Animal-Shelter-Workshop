<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Report</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="bg-gray-100">

    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Submit a New Report</h2>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Map --}}
            <div>
                <label class="block text-gray-700 font-medium mb-2">Select Location on Map</label>
                <div id="map" style="height: 300px; border-radius: 10px;"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Latitude</label>
                    <input type="text" name="latitude" class="w-full border-gray-300 rounded-lg shadow-sm" required readonly>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium">Longitude</label>
                    <input type="text" name="longitude" class="w-full border-gray-300 rounded-lg shadow-sm" required readonly>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Address</label>
                <input type="text" name="address" class="w-full border-gray-300 rounded-lg shadow-sm" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">City</label>
                    <input type="text" name="city" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium">State</label>
                    <input type="text" name="state" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select name="report_status" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Resolved">Resolved</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Description</label>
                <textarea name="description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Submit Report
                </button>
            </div>
        </form>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const defaultLat = 3.139;  
            const defaultLng = 101.6869;

            const map = L.map('map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let marker;

            map.on('click', function (e) {
                const { lat, lng } = e.latlng;

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }

                document.querySelector('input[name="latitude"]').value = lat.toFixed(6);
                document.querySelector('input[name="longitude"]').value = lng.toFixed(6);
            });
        });
    </script>
</body>
</html>
