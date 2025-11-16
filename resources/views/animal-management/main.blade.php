<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-6">
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" action="{{ route('animal-management.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Species</label>
                        <select name="species" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All</option>
                            <option value="Dog" {{ request('species') == 'Dog' ? 'selected' : '' }}>Dog</option>
                            <option value="Cat" {{ request('species') == 'Cat' ? 'selected' : '' }}>Cat</option>
                            <option value="Other" {{ request('species') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adoption Status</label>
                        <select name="adoption_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All</option>
                            <option value="Not Adopted" {{ request('adoption_status') == 'Not Adopted' ? 'selected' : '' }}>Available</option>
                            <option value="Adopted" {{ request('adoption_status') == 'Adopted' ? 'selected' : '' }}>Adopted</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All</option>
                            <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Unknown" {{ request('gender') == 'Unknown' ? 'selected' : '' }}>Unknown</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-between items-center">
                    <div class="flex gap-2">
                        <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white px-6 py-2 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('animal-management.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                    <p class="text-gray-600">Showing <span class="font-semibold">{{ $animals->total() }}</span> animals</p>
                </div>
            </form>
        </div>

        <!-- Animals Grid -->
        @if($animals->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($animals as $animal)
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition duration-300">
                        <!-- Animal Image -->
                        <div class="h-48 bg-gradient-to-br from-purple-300 to-purple-400 flex items-center justify-center overflow-hidden">
                            @if($animal->images && $animal->images->count() > 0)
                                <img src="{{ asset('storage/' . $animal->images->first()->image_path) }}" 
                                     alt="{{ $animal->name }}" 
                                     class="w-full h-full object-cover">
                            @else
                                @if(strtolower($animal->species) == 'dog')
                                    <span class="text-8xl">🐕</span>
                                @elseif(strtolower($animal->species) == 'cat')
                                    <span class="text-8xl">🐈</span>
                                @else
                                    <span class="text-8xl">🐾</span>
                                @endif
                            @endif
                        </div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-gray-800">{{ $animal->name }}</h3>
                                @if($animal->adoption_status == 'Not Adopted')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Available</span>
                                @elseif($animal->adoption_status == 'Adopted')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Adopted</span>
                                @else
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ $animal->adoption_status }}</span>
                                @endif
                            </div>

                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <p><span class="font-semibold">Species:</span> {{ $animal->species }}</p>
                                <p><span class="font-semibold">Age:</span> {{ $animal->age }}</p>
                                <p><span class="font-semibold">Gender:</span> {{ $animal->gender }}</p>
                                <p><span class="font-semibold">Location:</span> 
                                    @if($animal->slot)
                                        Slot {{ $animal->slot->slot_number ?? $animal->slot->id }} - {{ $animal->slot->location ?? 'Main Shelter' }}
                                    @else
                                        Not Assigned
                                    @endif
                                </p>
                            </div>

                            <p class="text-gray-700 text-sm mb-4 line-clamp-2">{{ Str::limit($animal->health_details, 100) }}</p>

                            <div class="flex space-x-2">
                                <a href="{{ route('animal-management.show', $animal->id) }}" 
                                   class="flex-1 bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300 text-center">
                                    View Details
                                </a>
                                @role('caretaker')
                                <a href="{{ route('animal-management.edit', $animal->id) }}" 
                                   class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300 flex items-center justify-center">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endrole
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center">
                {{ $animals->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">🐾</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No Animals Found</h3>
                <p class="text-gray-600 mb-6">No animals match your current filters. Try adjusting your search criteria.</p>
                <a href="{{ route('animal-management.index') }}" class="inline-block bg-purple-700 hover:bg-purple-800 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                    Clear Filters
                </a>
            </div>
        @endif
    </div>
</body>
</html>