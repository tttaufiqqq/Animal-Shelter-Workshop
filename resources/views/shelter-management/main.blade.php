<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shelter Slots - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('navbar')

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold mb-2">Shelter Slots Management</h1>
            <p class="text-purple-100">Monitor available slots, capacity, and inventory across all facilities</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overall Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Slots</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">250</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <span class="text-2xl">📦</span>
                    </div>
                </div>
                <p class="text-gray-600 text-sm mt-4">Across all shelters</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Occupied</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">142</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <span class="text-2xl">🐾</span>
                    </div>
                </div>
                <p class="text-orange-600 text-sm mt-4">57% capacity</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Available</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">108</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <span class="text-2xl">✅</span>
                    </div>
                </div>
                <p class="text-green-600 text-sm mt-4">43% available</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Reserved</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">8</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <span class="text-2xl">🔒</span>
                    </div>
                </div>
                <p class="text-blue-600 text-sm mt-4">Pending intake</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shelter Location</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All Shelters</option>
                        <option>Main Shelter - Johor Bahru</option>
                        <option>North Branch - Pasir Gudang</option>
                        <option>West Center - Pontian</option>
                        <option>East Facility - Kota Tinggi</option>
                        <option>South Unit - Kulai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All Status</option>
                        <option>Available</option>
                        <option>Occupied</option>
                        <option>Reserved</option>
                        <option>Maintenance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slot Type</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All Types</option>
                        <option>Dog Kennel</option>
                        <option>Cat Room</option>
                        <option>Quarantine</option>
                        <option>Medical Ward</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full bg-purple-700 hover:bg-purple-800 text-white px-6 py-2 rounded-lg font-medium transition duration-300">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Shelter Slots Grid -->
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Main Shelter - Johor Bahru</h2>
        
        <!-- Shelter Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Capacity Overview</h3>
                    <p class="text-gray-600">📍 Jalan Skudai, Johor Bahru, Johor</p>
                </div>
                <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Operational</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Total Capacity</p>
                    <p class="text-3xl font-bold text-purple-700">80</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Occupied</p>
                    <p class="text-3xl font-bold text-orange-700">52</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Available</p>
                    <p class="text-3xl font-bold text-green-700">26</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Reserved</p>
                    <p class="text-3xl font-bold text-blue-700">2</p>
                </div>
            </div>

            <!-- Occupancy Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Occupancy Rate</span>
                    <span class="font-semibold">65%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full" style="width: 65%"></div>
                </div>
            </div>

            <!-- Inventory Summary -->
            <div class="border-t pt-4">
                <h4 class="font-semibold text-gray-800 mb-3">Inventory Status</h4>
                <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                    <div class="text-center">
                        <div class="text-2xl mb-1">🍖</div>
                        <p class="text-xs text-gray-600">Dog Food</p>
                        <p class="text-sm font-bold text-green-700">450kg</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">🐟</div>
                        <p class="text-xs text-gray-600">Cat Food</p>
                        <p class="text-sm font-bold text-green-700">320kg</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">🏖️</div>
                        <p class="text-xs text-gray-600">Litter Sand</p>
                        <p class="text-sm font-bold text-yellow-700">180kg</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">💊</div>
                        <p class="text-xs text-gray-600">Medicine</p>
                        <p class="text-sm font-bold text-yellow-700">Low</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">🧹</div>
                        <p class="text-xs text-gray-600">Cleaning</p>
                        <p class="text-sm font-bold text-green-700">Good</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">🛏️</div>
                        <p class="text-xs text-gray-600">Bedding</p>
                        <p class="text-sm font-bold text-green-700">Good</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slots Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Slot 1 - Occupied -->
            <div class="bg-white rounded-lg shadow border-l-4 border-orange-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot A-01</h3>
                        <p class="text-xs text-gray-500">Dog Kennel - Large</p>
                    </div>
                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-semibold">Occupied</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> Max (Dog)</p>
                    <p class="text-gray-700"><span class="font-semibold">Admitted:</span> Oct 15, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Days:</span> 8 days</p>
                </div>
                <button class="w-full text-sm bg-purple-600 hover:bg-purple-700 text-white py-2 rounded transition duration-300">
                    View Details
                </button>
            </div>

            <!-- Slot 2 - Available -->
            <div class="bg-white rounded-lg shadow border-l-4 border-green-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot A-02</h3>
                        <p class="text-xs text-gray-500">Dog Kennel - Large</p>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">Available</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> None</p>
                    <p class="text-gray-700"><span class="font-semibold">Last Cleaned:</span> Today</p>
                    <p class="text-gray-700"><span class="font-semibold">Status:</span> Ready</p>
                </div>
                <button class="w-full text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded transition duration-300">
                    Assign Animal
                </button>
            </div>

            <!-- Slot 3 - Occupied -->
            <div class="bg-white rounded-lg shadow border-l-4 border-orange-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot A-03</h3>
                        <p class="text-xs text-gray-500">Dog Kennel - Medium</p>
                    </div>
                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-semibold">Occupied</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> Charlie (Dog)</p>
                    <p class="text-gray-700"><span class="font-semibold">Admitted:</span> Oct 10, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Days:</span> 13 days</p>
                </div>
                <button class="w-full text-sm bg-purple-600 hover:bg-purple-700 text-white py-2 rounded transition duration-300">
                    View Details
                </button>
            </div>

            <!-- Slot 4 - Reserved -->
            <div class="bg-white rounded-lg shadow border-l-4 border-blue-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot A-04</h3>
                        <p class="text-xs text-gray-500">Dog Kennel - Medium</p>
                    </div>
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">Reserved</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Reserved For:</span> Report #156</p>
                    <p class="text-gray-700"><span class="font-semibold">Expected:</span> Oct 24, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Type:</span> Injured Dog</p>
                </div>
                <button class="w-full text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 rounded transition duration-300">
                    View Report
                </button>
            </div>

            <!-- Slot 5 - Occupied -->
            <div class="bg-white rounded-lg shadow border-l-4 border-orange-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot B-01</h3>
                        <p class="text-xs text-gray-500">Cat Room - Small</p>
                    </div>
                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-semibold">Occupied</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> Luna (Cat)</p>
                    <p class="text-gray-700"><span class="font-semibold">Admitted:</span> Oct 5, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Days:</span> 18 days</p>
                </div>
                <button class="w-full text-sm bg-purple-600 hover:bg-purple-700 text-white py-2 rounded transition duration-300">
                    View Details
                </button>
            </div>

            <!-- Slot 6 - Available -->
            <div class="bg-white rounded-lg shadow border-l-4 border-green-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot B-02</h3>
                        <p class="text-xs text-gray-500">Cat Room - Small</p>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">Available</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> None</p>
                    <p class="text-gray-700"><span class="font-semibold">Last Cleaned:</span> Today</p>
                    <p class="text-gray-700"><span class="font-semibold">Status:</span> Ready</p>
                </div>
                <button class="w-full text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded transition duration-300">
                    Assign Animal
                </button>
            </div>

            <!-- Slot 7 - Occupied -->
            <div class="bg-white rounded-lg shadow border-l-4 border-orange-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot B-03</h3>
                        <p class="text-xs text-gray-500">Cat Room - Small</p>
                    </div>
                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-semibold">Occupied</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> Shadow (Cat)</p>
                    <p class="text-gray-700"><span class="font-semibold">Admitted:</span> Oct 18, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Days:</span> 5 days</p>
                </div>
                <button class="w-full text-sm bg-purple-600 hover:bg-purple-700 text-white py-2 rounded transition duration-300">
                    View Details
                </button>
            </div>

            <!-- Slot 8 - Maintenance -->
            <div class="bg-white rounded-lg shadow border-l-4 border-red-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot B-04</h3>
                        <p class="text-xs text-gray-500">Cat Room - Small</p>
                    </div>
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-semibold">Maintenance</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Issue:</span> Cage repair needed</p>
                    <p class="text-gray-700"><span class="font-semibold">Started:</span> Oct 22, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Est. Complete:</span> Oct 25</p>
                </div>
                <button class="w-full text-sm bg-red-600 hover:bg-red-700 text-white py-2 rounded transition duration-300">
                    View Issue
                </button>
            </div>

            <!-- Slot 9 - Available -->
            <div class="bg-white rounded-lg shadow border-l-4 border-green-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot C-01</h3>
                        <p class="text-xs text-gray-500">Quarantine - Isolated</p>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">Available</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> None</p>
                    <p class="text-gray-700"><span class="font-semibold">Last Cleaned:</span> Today</p>
                    <p class="text-gray-700"><span class="font-semibold">Status:</span> Sanitized</p>
                </div>
                <button class="w-full text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded transition duration-300">
                    Assign Animal
                </button>
            </div>

            <!-- Slot 10 - Occupied -->
            <div class="bg-white rounded-lg shadow border-l-4 border-orange-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot D-01</h3>
                        <p class="text-xs text-gray-500">Medical Ward</p>
                    </div>
                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-semibold">Occupied</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> Buddy (Dog)</p>
                    <p class="text-gray-700"><span class="font-semibold">Admitted:</span> Oct 20, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Days:</span> 3 days</p>
                </div>
                <button class="w-full text-sm bg-purple-600 hover:bg-purple-700 text-white py-2 rounded transition duration-300">
                    View Details
                </button>
            </div>

            <!-- Slot 11 - Available -->
            <div class="bg-white rounded-lg shadow border-l-4 border-green-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot D-02</h3>
                        <p class="text-xs text-gray-500">Medical Ward</p>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">Available</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Animal:</span> None</p>
                    <p class="text-gray-700"><span class="font-semibold">Last Cleaned:</span> Today</p>
                    <p class="text-gray-700"><span class="font-semibold">Status:</span> Ready</p>
                </div>
                <button class="w-full text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded transition duration-300">
                    Assign Animal
                </button>
            </div>

            <!-- Slot 12 - Reserved -->
            <div class="bg-white rounded-lg shadow border-l-4 border-blue-500 p-4 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Slot A-05</h3>
                        <p class="text-xs text-gray-500">Dog Kennel - Large</p>
                    </div>
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">Reserved</span>
                </div>
                <div class="space-y-2 text-sm mb-3">
                    <p class="text-gray-700"><span class="font-semibold">Reserved For:</span> Report #155</p>
                    <p class="text-gray-700"><span class="font-semibold">Expected:</span> Oct 25, 2025</p>
                    <p class="text-gray-700"><span class="font-semibold">Type:</span> Stray Cat</p>
                </div>
                <button class="w-full text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 rounded transition duration-300">
                    View Report
                </button>
            </div>
        </div>

        <!-- Detailed Inventory Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Inventory Details - Main Shelter</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minimum Required</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Restocked</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-purple-600 hover:text-purple-900">Restock</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">🐟</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Cat Food (Adult)</div>
                                        <div class="text-sm text-gray-500">Dry food mix</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">320 kg</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">150 kg</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Good Stock
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Oct 12, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-purple-600 hover:text-purple-900">Restock</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">🏖️</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Cat Litter Sand</div>
                                        <div class="text-sm text-gray-500">Clumping clay</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">180 kg</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">150 kg</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Moderate
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Oct 18, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-purple-600 hover:text-purple-900">Restock</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">💊</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Medicine & Vitamins</div>
                                        <div class="text-sm text-gray-500">Various medications</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Low Stock</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">Adequate</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Reorder Soon
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Oct 1, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-orange-600 hover:text-orange-900 font-semibold">Order Now</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">🧹</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Cleaning Supplies</div>
                                        <div class="text-sm text-gray-500">Disinfectants, mops, etc.</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Adequate</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">Adequate</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Good Stock
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Oct 20, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-purple-600 hover:text-purple-900">Restock</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">🛏️</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Bedding & Blankets</div>
                                        <div class="text-sm text-gray-500">Pet beds, towels</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">85 units</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">60 units</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Good Stock
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Oct 10, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-purple-600 hover:text-purple-900">Restock</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">🍗</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Wet Food (Canned)</div>
                                        <div class="text-sm text-gray-500">Mixed varieties</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">240 cans</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">150 cans</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Good Stock
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Oct 17, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-purple-600 hover:text-purple-900">Restock</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">🦴</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Treats & Toys</div>
                                        <div class="text-sm text-gray-500">Various items</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Adequate</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">Adequate</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Good Stock
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Oct 14, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-purple-600 hover:text-purple-900">Restock</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-4 mb-8">
            <button class="bg-purple-700 hover:bg-purple-800 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                + Add New Slot
            </button>
            <button class="bg-white border-2 border-purple-700 text-purple-700 hover:bg-purple-50 px-6 py-3 rounded-lg font-medium transition duration-300">
                Generate Slot Report
            </button>
            <button class="bg-white border-2 border-purple-700 text-purple-700 hover:bg-purple-50 px-6 py-3 rounded-lg font-medium transition duration-300">
                Manage Inventory
            </button>
        </div>

        <!-- Alerts Section -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-yellow-800 mb-3">⚠️ Inventory Alerts</h3>
            <ul class="space-y-2">
                <li class="text-yellow-700">
                    <span class="font-semibold">Medicine & Vitamins:</span> Stock running low, reorder recommended within 7 days
                </li>
                <li class="text-yellow-700">
                    <span class="font-semibold">Cat Litter Sand:</span> Current stock at moderate levels, monitor closely
                </li>
                <li class="text-gray-700">
                    <span class="font-semibold">Slot B-04:</span> Under maintenance - repair expected to complete in 3 days
                </li>
            </ul>
        </div>
    </div>
</body>
</html> 