<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shelters - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('navbar')

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold mb-2">Our Shelters</h1>
            <p class="text-purple-100">Managing facilities, inventory, and available space</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overall Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Shelters</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">5</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <span class="text-2xl">🏠</span>
                    </div>
                </div>
                <p class="text-green-600 text-sm mt-4">All operational</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Capacity</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">250</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <span class="text-2xl">📊</span>
                    </div>
                </div>
                <p class="text-gray-600 text-sm mt-4">Animals max</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Current Occupancy</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">142</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <span class="text-2xl">🐾</span>
                    </div>
                </div>
                <p class="text-blue-600 text-sm mt-4">57% capacity</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Available Space</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">108</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <span class="text-2xl">✅</span>
                    </div>
                </div>
                <p class="text-green-600 text-sm mt-4">43% available</p>
            </div>
        </div>

        <!-- Shelter Locations -->
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Shelter Locations</h2>
        <div class="space-y-6 mb-12">
            <!-- Shelter 1 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Main Shelter - Johor Bahru</h3>
                            <p class="text-gray-600">📍 Jalan Skudai, Johor Bahru, Johor</p>
                        </div>
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Operational</span>
                    </div>

                    <!-- Capacity Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Total Capacity</p>
                            <p class="text-2xl font-bold text-purple-700">80</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Current Animals</p>
                            <p class="text-2xl font-bold text-orange-700">52</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Available Space</p>
                            <p class="text-2xl font-bold text-green-700">28</p>
                        </div>
                    </div>

                    <!-- Occupancy Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Occupancy Rate</span>
                            <span class="font-semibold">65%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full" style="width: 65%"></div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Inventory Status</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🍖</div>
                            <p class="text-xs text-gray-600 mb-1">Dog Food</p>
                            <p class="text-lg font-bold text-green-700">450 kg</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🐟</div>
                            <p class="text-xs text-gray-600 mb-1">Cat Food</p>
                            <p class="text-lg font-bold text-green-700">320 kg</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">💊</div>
                            <p class="text-xs text-gray-600 mb-1">Medicine</p>
                            <p class="text-lg font-bold text-yellow-700">Low</p>
                            <p class="text-xs text-yellow-600">Reorder Soon</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🧹</div>
                            <p class="text-xs text-gray-600 mb-1">Cleaning Supplies</p>
                            <p class="text-lg font-bold text-green-700">Adequate</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                    </div>
                    <button class="text-purple-700 hover:text-purple-900 font-medium text-sm">View Full Inventory →</button>
                </div>
            </div>

            <!-- Shelter 2 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">North Branch - Pasir Gudang</h3>
                            <p class="text-gray-600">📍 Pasir Gudang, Johor</p>
                        </div>
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Operational</span>
                    </div>

                    <!-- Capacity Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Total Capacity</p>
                            <p class="text-2xl font-bold text-purple-700">60</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Current Animals</p>
                            <p class="text-2xl font-bold text-orange-700">35</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Available Space</p>
                            <p class="text-2xl font-bold text-green-700">25</p>
                        </div>
                    </div>

                    <!-- Occupancy Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Occupancy Rate</span>
                            <span class="font-semibold">58%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full" style="width: 58%"></div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Inventory Status</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🍖</div>
                            <p class="text-xs text-gray-600 mb-1">Dog Food</p>
                            <p class="text-lg font-bold text-yellow-700">180 kg</p>
                            <p class="text-xs text-yellow-600">Moderate</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🐟</div>
                            <p class="text-xs text-gray-600 mb-1">Cat Food</p>
                            <p class="text-lg font-bold text-green-700">220 kg</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">💊</div>
                            <p class="text-xs text-gray-600 mb-1">Medicine</p>
                            <p class="text-lg font-bold text-green-700">Adequate</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🧹</div>
                            <p class="text-xs text-gray-600 mb-1">Cleaning Supplies</p>
                            <p class="text-lg font-bold text-red-700">Critical</p>
                            <p class="text-xs text-red-600">Reorder Now</p>
                        </div>
                    </div>
                    <button class="text-purple-700 hover:text-purple-900 font-medium text-sm">View Full Inventory →</button>
                </div>
            </div>

            <!-- Shelter 3 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">West Center - Pontian</h3>
                            <p class="text-gray-600">📍 Pontian, Johor</p>
                        </div>
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Operational</span>
                    </div>

                    <!-- Capacity Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Total Capacity</p>
                            <p class="text-2xl font-bold text-purple-700">50</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Current Animals</p>
                            <p class="text-2xl font-bold text-orange-700">28</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Available Space</p>
                            <p class="text-2xl font-bold text-green-700">22</p>
                        </div>
                    </div>

                    <!-- Occupancy Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Occupancy Rate</span>
                            <span class="font-semibold">56%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full" style="width: 56%"></div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Inventory Status</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🍖</div>
                            <p class="text-xs text-gray-600 mb-1">Dog Food</p>
                            <p class="text-lg font-bold text-green-700">380 kg</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🐟</div>
                            <p class="text-xs text-gray-600 mb-1">Cat Food</p>
                            <p class="text-lg font-bold text-green-700">290 kg</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">💊</div>
                            <p class="text-xs text-gray-600 mb-1">Medicine</p>
                            <p class="text-lg font-bold text-green-700">Adequate</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🧹</div>
                            <p class="text-xs text-gray-600 mb-1">Cleaning Supplies</p>
                            <p class="text-lg font-bold text-green-700">Adequate</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                    </div>
                    <button class="text-purple-700 hover:text-purple-900 font-medium text-sm">View Full Inventory →</button>
                </div>
            </div>

            <!-- Shelter 4 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">East Facility - Kota Tinggi</h3>
                            <p class="text-gray-600">📍 Kota Tinggi, Johor</p>
                        </div>
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Operational</span>
                    </div>

                    <!-- Capacity Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Total Capacity</p>
                            <p class="text-2xl font-bold text-purple-700">40</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Current Animals</p>
                            <p class="text-2xl font-bold text-orange-700">18</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Available Space</p>
                            <p class="text-2xl font-bold text-green-700">22</p>
                        </div>
                    </div>

                    <!-- Occupancy Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Occupancy Rate</span>
                            <span class="font-semibold">45%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Inventory Status</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🍖</div>
                            <p class="text-xs text-gray-600 mb-1">Dog Food</p>
                            <p class="text-lg font-bold text-red-700">85 kg</p>
                            <p class="text-xs text-red-600">Critical Low</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🐟</div>
                            <p class="text-xs text-gray-600 mb-1">Cat Food</p>
                            <p class="text-lg font-bold text-yellow-700">140 kg</p>
                            <p class="text-xs text-yellow-600">Moderate</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">💊</div>
                            <p class="text-xs text-gray-600 mb-1">Medicine</p>
                            <p class="text-lg font-bold text-green-700">Adequate</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🧹</div>
                            <p class="text-xs text-gray-600 mb-1">Cleaning Supplies</p>
                            <p class="text-lg font-bold text-yellow-700">Moderate</p>
                            <p class="text-xs text-yellow-600">Monitor Stock</p>
                        </div>
                    </div>
                    <button class="text-purple-700 hover:text-purple-900 font-medium text-sm">View Full Inventory →</button>
                </div>
            </div>

            <!-- Shelter 5 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">South Unit - Kulai</h3>
                            <p class="text-gray-600">📍 Kulai, Johor</p>
                        </div>
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Operational</span>
                    </div>

                    <!-- Capacity Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Total Capacity</p>
                            <p class="text-2xl font-bold text-purple-700">20</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Current Animals</p>
                            <p class="text-2xl font-bold text-orange-700">9</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">Available Space</p>
                            <p class="text-2xl font-bold text-green-700">11</p>
                        </div>
                    </div>

                    <!-- Occupancy Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Occupancy Rate</span>
                            <span class="font-semibold">45%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Inventory Status</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🍖</div>
                            <p class="text-xs text-gray-600 mb-1">Dog Food</p>
                            <p class="text-lg font-bold text-green-700">210 kg</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🐟</div>
                            <p class="text-xs text-gray-600 mb-1">Cat Food</p>
                            <p class="text-lg font-bold text-green-700">165 kg</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">💊</div>
                            <p class="text-xs text-gray-600 mb-1">Medicine</p>
                            <p class="text-lg font-bold text-yellow-700">Low</p>
                            <p class="text-xs text-yellow-600">Reorder Soon</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="text-2xl mb-2">🧹</div>
                            <p class="text-xs text-gray-600 mb-1">Cleaning Supplies</p>
                            <p class="text-lg font-bold text-green-700">Adequate</p>
                            <p class="text-xs text-green-600">Good Stock</p>
                        </div>
                    </div>
                    <button class="text-purple-700 hover:text-purple-900 font-medium text-sm">View Full Inventory →</button>
                </div>
            </div>
        </div>

        <!-- Inventory Summary -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Overall Inventory Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="border-l-4 border-green-500 pl-4">
                    <p class="text-gray-600 text-sm mb-1">Total Dog Food</p>
                    <p class="text-3xl font-bold text-gray-800 mb-1">1,305 kg</p>
                    <p class="text-sm text-green-600">Across all shelters</p>
                </div>
                <div class="border-l-4 border-blue-500 pl-4">
                    <p class="text-gray-600 text-sm mb-1">Total Cat Food</p>
                    <p class="text-3xl font-bold text-gray-800 mb-1">1,135 kg</p>
                    <p class="text-sm text-blue-600">Across all shelters</p>
                </div>
                <div class="border-l-4 border-purple-500 pl-4">
                    <p class="text-gray-600 text-sm mb-1">Medical Supplies</p>
                    <p class="text-3xl font-bold text-gray-800 mb-1">Adequate</p>
                    <p class="text-sm text-yellow-600">2 shelters need restock</p>
                </div>
                <div class="border-l-4 border-orange-500 pl-4">
                    <p class="text-gray-600 text-sm mb-1">Cleaning Supplies</p>
                    <p class="text-3xl font-bold text-gray-800 mb-1">Moderate</p>
                    <p class="text-sm text-red-600">1 shelter critical</p>
                </div>
            </div>
            <div class="mt-6 flex space-x-4">
                <button class="bg-purple-700 hover:bg-purple-800 text-white px-6 py-2 rounded-lg font-medium transition duration-300">
                    Generate Inventory Report
                </button>
                <button class="bg-white border-2 border-purple-700 text-purple-700 hover:bg-purple-50 px-6 py-2 rounded-lg font-medium transition duration-300">
                    Manage Supplies
                </button>
            </div>
        </div>

        <!-- Alerts Section -->
        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-red-800 mb-3">⚠️ Inventory Alerts</h3>
            <ul class="space-y-2">
                <li class="text-red-700">
                    <span class="font-semibold">East Facility - Kota Tinggi:</span> Dog food critically low (85 kg remaining)
                </li>
                <li class="text-red-700">
                    <span class="font-semibold">North Branch - Pasir Gudang:</span> Cleaning supplies need immediate restock
                </li>
                <li class="text-yellow-700">
                    <span class="font-semibold">Main Shelter - Johor Bahru:</span> Medicine supplies should be reordered soon
                </li>
            </ul>
        </div>
    </div>
</body>
</html>