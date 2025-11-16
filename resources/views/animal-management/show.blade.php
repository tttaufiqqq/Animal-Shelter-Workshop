<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $animal->name }} - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    @include('navbar')

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <a href="{{ route('animal-management.index') }}" class="text-purple-100 hover:text-white mb-2 inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Animals
                    </a>
                    <h1 class="text-4xl font-bold">{{ $animal->name }}</h1>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('animal-management.edit', $animal->id) }}" 
                       class="bg-white text-purple-700 px-6 py-2 rounded-lg font-medium hover:bg-purple-50 transition duration-300">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <form action="{{ route('animal-management.destroy', $animal->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this animal record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-red-700 transition duration-300">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-6">
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Images -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    @if($animal->images && $animal->images->count() > 0)
                        <!-- Main Image -->
                        <div class="aspect-video bg-gray-100">
                            <img id="mainImage" 
                                 src="{{ asset('storage/' . $animal->images->first()->image_path) }}" 
                                 alt="{{ $animal->name }}" 
                                 class="w-full h-full object-cover">
                        </div>

                        <!-- Image Thumbnails -->
                        @if($animal->images->count() > 1)
                            <div class="p-4 bg-gray-50">
                                <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                                    @foreach($animal->images as $image)
                                        <div class="aspect-square cursor-pointer rounded-lg overflow-hidden border-2 border-transparent hover:border-purple-500 transition-all"
                                             onclick="changeImage('{{ asset('storage/' . $image->image_path) }}')">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                 alt="{{ $animal->name }}" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="aspect-video bg-gradient-to-br from-purple-300 to-purple-400 flex items-center justify-center">
                            <span class="text-9xl">
                                @if(strtolower($animal->species) == 'dog')
                                    🐕
                                @elseif(strtolower($animal->species) == 'cat')
                                    🐈
                                @else
                                    🐾
                                @endif
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Health Details Card -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-heartbeat text-purple-600 mr-3"></i>
                        Health Details
                    </h2>
                    <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $animal->health_details }}</p>
                </div>

                 <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-syringe text-purple-600 mr-3"></i>
                            Medical & Vaccination Records
                        </h2>
                        @role('admin|caretaker')
                        <a href="{{ route('medical-records.create', ['animal_id' => $animal->id]) }}" 
                           class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 transition duration-300 text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Record
                        </a>
                        @endrole
                    </div>

                    @if($animal->medicalRecords && $animal->medicalRecords->count() > 0)
                        <div class="space-y-4">
                            @foreach($animal->medicalRecords->sortByDesc('record_date') as $record)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition-colors">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                                @if($record->record_type == 'Vaccination') bg-green-100 text-green-700
                                                @elseif($record->record_type == 'Treatment') bg-blue-100 text-blue-700
                                                @elseif($record->record_type == 'Checkup') bg-purple-100 text-purple-700
                                                @else bg-gray-100 text-gray-700
                                                @endif">
                                                {{ $record->record_type }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($record->record_date)->format('M d, Y') }}</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-800 mb-1">{{ $record->title }}</h4>
                                    <p class="text-gray-600 text-sm mb-2">{{ $record->description }}</p>
                                    @if($record->veterinarian)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-user-md mr-2"></i>
                                            <span>{{ $record->veterinarian }}</span>
                                        </div>
                                    @endif
                                    @if($record->next_due_date)
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <i class="fas fa-calendar-alt mr-2"></i>
                                            <span>Next due: {{ \Carbon\Carbon::parse($record->next_due_date)->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center text-gray-600">
                            <i class="fas fa-clipboard-list text-4xl mb-3 text-gray-400"></i>
                            <p class="font-medium">No medical records yet</p>
                            <p class="text-sm text-gray-500 mt-1">Medical and vaccination records will appear here</p>
                        </div>
                    @endif
                </div>

                <!-- Rescue Information -->
                @if($animal->rescue)
                    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-hand-holding-heart text-purple-600 mr-3"></i>
                            Rescue Information
                        </h2>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="font-semibold text-gray-700 w-32">Rescue ID:</span>
                                <span class="text-gray-600">#{{ $animal->rescue->id }}</span>
                            </div>
                            @if($animal->rescue->report)
                                <div class="flex items-start">
                                    <span class="font-semibold text-gray-700 w-32">Location:</span>
                                    <span class="text-gray-600">{{ $animal->rescue->report->address }}</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-semibold text-gray-700 w-32">City:</span>
                                    <span class="text-gray-600">{{ $animal->rescue->report->city }}, {{ $animal->rescue->report->state }}</span>
                                </div>
                            @endif
                            <div class="flex items-start">
                                <span class="font-semibold text-gray-700 w-32">Rescue Date:</span>
                                <span class="text-gray-600">{{ $animal->rescue->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Details -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="mb-4">
                        @if($animal->adoption_status == 'Not Adopted')
                            <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i>Available for Adoption
                            </span>
                        @elseif($animal->adoption_status == 'Adopted')
                            <span class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                                <i class="fas fa-home mr-1"></i>Adopted
                            </span>
                        @else
                            <span class="inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">
                                {{ $animal->adoption_status }}
                            </span>
                        @endif
                    </div>

                    <h2 class="text-xl font-bold text-gray-800 mb-4">Basic Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Species</span>
                            <span class="text-gray-800">{{ $animal->species }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Age</span>
                            <span class="text-gray-800">{{ $animal->age }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Gender</span>
                            <span class="text-gray-800">{{ $animal->gender }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Arrival Date</span>
                            <span class="text-gray-800">{{ \Carbon\Carbon::parse($animal->arrival_date)->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-purple-600 mr-2"></i>
                        Current Location
                    </h2>
                    @if($animal->slot)
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-semibold">Slot Number</span>
                                <span class="text-purple-700 font-bold text-lg">{{ $animal->slot->slot_number ?? $animal->slot->id }}</span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-semibold">Location</span>
                                <span class="text-gray-800">{{ $animal->slot->location ?? 'Main Shelter' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">Status</span>
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">{{ $animal->slot->status }}</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-600">
                            <i class="fas fa-exclamation-circle text-3xl mb-2 text-gray-400"></i>
                            <p>No slot assigned</p>
                        </div>
                    @endif
                </div>

                <!-- Action Card -->
                 @role('public user')
                @if($animal->adoption_status == 'Not Adopted')
                    <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg shadow-lg p-6 text-white">
                        <h3 class="text-xl font-bold mb-2">Interested in adopting?</h3>
                        <p class="text-purple-100 mb-4">Contact us to learn more about {{ $animal->name }} and start the adoption process.</p>
                        <button class="w-full bg-white text-purple-700 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300">
                            <i class="fas fa-heart mr-2"></i>Adopt {{ $animal->name }}
                        </button>
                    </div>
                @endif
                @endrole
            </div>
        </div>
    </div>

    <script>
        function changeImage(imagePath) {
            document.getElementById('mainImage').src = imagePath;
        }
    </script>
</body>
</html>