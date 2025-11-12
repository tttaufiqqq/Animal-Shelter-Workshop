<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('navbar')

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold mb-2">Our Animals</h1>
            <p class="text-purple-100">Browse all animals currently in our care</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" placeholder="Search by name..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Species</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All</option>
                        <option>Dog</option>
                        <option>Cat</option>
                        <option>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All</option>
                        <option>Available</option>
                        <option>Adopted</option>
                        <option>In Treatment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All</option>
                        <option>Puppy/Kitten</option>
                        <option>Young</option>
                        <option>Adult</option>
                        <option>Senior</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <p class="text-gray-600">Showing <span class="font-semibold">24</span> animals</p>
                <button class="bg-purple-700 hover:bg-purple-800 text-white px-6 py-2 rounded-lg font-medium transition duration-300">
                    + Add New Animal
                </button>
            </div>
        </div>

        <!-- Animals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Animal Card 1 -->
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-br from-orange-300 to-orange-400 flex items-center justify-center">
                    <span class="text-8xl">🐕</span>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-gray-800">Max</h3>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Available</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-semibold">Species:</span> Dog (Golden Retriever)</p>
                        <p><span class="font-semibold">Age:</span> 2 years</p>
                        <p><span class="font-semibold">Gender:</span> Male</p>
                        <p><span class="font-semibold">Location:</span> Main Shelter</p>
                    </div>
                    <p class="text-gray-700 text-sm mb-4">Friendly and energetic dog, great with kids. Loves to play fetch and go for walks.</p>
                    <div class="flex space-x-2">
                        <button class="flex-1 bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300">
                            View Details
                        </button>
                        <button class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                            ✏️
                        </button>
                    </div>
                </div>
            </div>

            <!-- Animal Card 2 -->
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-br from-gray-300 to-gray-400 flex items-center justify-center">
                    <span class="text-8xl">🐈</span>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-gray-800">Luna</h3>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Available</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-semibold">Species:</span> Cat (Persian)</p>
                        <p><span class="font-semibold">Age:</span> 1 year</p>
                        <p><span class="font-semibold">Gender:</span> Female</p>
                        <p><span class="font-semibold">Location:</span> Main Shelter</p>
                    </div>
                    <p class="text-gray-700 text-sm mb-4">Gentle and affectionate cat. Loves to cuddle and enjoys quiet environments.</p>
                    <div class="flex space-x-2">
                        <button class="flex-1 bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300">
                            View Details
                        </button>
                        <button class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                            ✏️
                        </button>
                    </div>
                </div>
            </div>

            <!-- Animal Card 3 -->
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-br from-brown-300 to-brown-400 flex items-center justify-center" style="background: linear-gradient(to bottom right, #d4a574, #b8860b);">
                    <span class="text-8xl">🐕</span>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-gray-800">Buddy</h3>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">In Treatment</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-semibold">Species:</span> Dog (Beagle)</p>
                        <p><span class="font-semibold">Age:</span> 3 years</p>
                        <p><span class="font-semibold">Gender:</span> Male</p>
                        <p><span class="font-semibold">Location:</span> Vet Clinic</p>
                    </div>
                    <p class="text-gray-700 text-sm mb-4">Recovering from minor injury. Playful and curious, loves outdoor adventures.</p>
                    <div class="flex space-x-2">
                        <button class="flex-1 bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300">
                            View Details
                        </button>
                        <button class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                            ✏️
                        </button>
                    </div>
                </div>
            </div>

            <!-- Animal Card 4 -->
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-br from-orange-200 to-orange-300 flex items-center justify-center">
                    <span class="text-8xl">🐈</span>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-gray-800">Whiskers</h3>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Available</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-semibold">Species:</span> Cat (Tabby)</p>
                        <p><span class="font-semibold">Age:</span> 4 years</p>
                        <p><span class="font-semibold">Gender:</span> Male</p>
                        <p><span class="font-semibold">Location:</span> Foster Home</p>
                    </div>
                    <p class="text-gray-700 text-sm mb-4">Independent and calm cat. Good with other pets, prefers adults.</p>
                    <div class="flex space-x-2">
                        <button class="flex-1 bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300">
                            View Details
                        </button>
                        <button class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                            ✏️
                        </button>
                    </div>
                </div>
            </div>

            <!-- Animal Card 5 -->
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center">
                    <span class="text-8xl">🐕</span>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-gray-800">Bella</h3>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Adopted</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-semibold">Species:</span> Dogs (Husky)</p>
                        <p><span class="font-semibold">Age:</span> 1 year</p>
                        <p><span class="font-semibold">Gender:</span> Female</p>
                        <p><span class="font-semibold">Location:</span> Adopted Home</p>
                    </div>
                    <p class="text-gray-700 text-sm mb-4">Recently adopted! Energetic and playful, found her forever home.</p>
                    <div class="flex space-x-2">
                        <button class="flex-1 bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300">
                            View Details
                        </button>
                        <button class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                            ✏️
                        </button>
                    </div>
                </div>
            </div>

            <!-- Animal Card 6 -->
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-br from-black to-gray-700 flex items-center justify-center">
                    <span class="text-8xl">🐈</span>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-gray-800">Shadow</h3>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Available</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-semibold">Species:</span> Cat (Black Cat)</p>
                        <p><span class="font-semibold">Age:</span> 6 months</p>
                        <p><span class="font-semibold">Gender:</span> Male</p>
                        <p><span class="font-semibold">Location:</span> Main Shelter</p>
                    </div>
                    <p class="text-gray-700 text-sm mb-4">Young and playful kitten. Very curious and loves to explore.</p>
                    <div class="flex space-x-2">
                        <button class="flex-1 bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300">
                            View Details
                        </button>
                        <button class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                            ✏️
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
            <nav class="flex space-x-2">
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">
                    Previous
                </button>
                <button class="px-4 py-2 bg-purple-700 text-white rounded-lg">1</button>
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">2</button>
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">3</button>
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">
                    Next
                </button>
            </nav>
        </div>
    </div>
</body>
</html>