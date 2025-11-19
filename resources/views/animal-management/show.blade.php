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
                @role('admin|caretaker')
                <div class="flex gap-3">
                    <button onclick="openEditModal({{ $animal->id }})" 
                            class="bg-white text-purple-700 px-6 py-2 rounded-lg font-medium hover:bg-purple-50 transition duration-300">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </button>
                    <form action="{{ route('animal-management.destroy', $animal->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this animal record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-red-700 transition duration-300">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                </div>

                {{-- Include the edit modal --}}
                @include('animal-management.edit-modal', ['animal' => $animal])
                @endrole
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-6">
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-lg mb-6">
                <p class="font-semibold">{{ session('error') }}</p>
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

                <!-- Medical Records Section -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-notes-medical text-blue-600 mr-3"></i>
                            Medical Records
                        </h2>
                        @role('caretaker')
                        <button onclick="openMedicalModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-300 text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Medical Record
                        </button>
                        @endrole
                    </div>

                    @if($animal->medicals && $animal->medicals->count() > 0)
                        <div class="space-y-4">
                            @foreach($animal->medicals->sortByDesc('created_at') as $medical)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                {{ $medical->treatment_type }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($medical->created_at)->format('M d, Y') }}</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Diagnosis: {{ $medical->diagnosis }}</h4>
                                    <p class="text-gray-600 text-sm mb-2"><strong>Action:</strong> {{ $medical->action }}</p>
                                    @if($medical->remarks)
                                        <p class="text-gray-600 text-sm mb-2"><strong>Remarks:</strong> {{ $medical->remarks }}</p>
                                    @endif
                                    @if($medical->vet)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-user-md mr-2"></i>
                                            <span>{{ $medical->vet->name }}</span>
                                        </div>
                                    @endif
                                    @if($medical->costs)
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <i class="fas fa-dollar-sign mr-2"></i>
                                            <span>RM {{ number_format($medical->costs, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center text-gray-600">
                            <i class="fas fa-notes-medical text-4xl mb-3 text-gray-400"></i>
                            <p class="font-medium">No medical records yet</p>
                            <p class="text-sm text-gray-500 mt-1">Medical treatment records will appear here</p>
                        </div>
                    @endif
                </div>

                <!-- Vaccination Records Section -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-syringe text-green-600 mr-3"></i>
                            Vaccination Records
                        </h2>
                        @role('caretaker')
                        <button onclick="openVaccinationModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition duration-300 text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Vaccination
                        </button>
                        @endrole
                    </div>

                    @if($animal->vaccinations && $animal->vaccinations->count() > 0)
                        <div class="space-y-4">
                            @foreach($animal->vaccinations->sortByDesc('created_at') as $vaccination)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-green-300 transition-colors">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                {{ $vaccination->type }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($vaccination->created_at)->format('M d, Y') }}</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-800 mb-1">{{ $vaccination->name }}</h4>
                                    @if($vaccination->remarks)
                                        <p class="text-gray-600 text-sm mb-2">{{ $vaccination->remarks }}</p>
                                    @endif
                                    @if($vaccination->vet)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-user-md mr-2"></i>
                                            <span>{{ $vaccination->vet->name }}</span>
                                        </div>
                                    @endif
                                    @if($vaccination->next_due_date)
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <i class="fas fa-calendar-alt mr-2"></i>
                                            <span>Next due: {{ \Carbon\Carbon::parse($vaccination->next_due_date)->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                    @if($vaccination->costs)
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <i class="fas fa-dollar-sign mr-2"></i>
                                            <span>RM {{ number_format($vaccination->costs, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center text-gray-600">
                            <i class="fas fa-syringe text-4xl mb-3 text-gray-400"></i>
                            <p class="font-medium">No vaccination records yet</p>
                            <p class="text-sm text-gray-500 mt-1">Vaccination records will appear here</p>
                        </div>
                    @endif
                </div>

                <!-- Modal for Add Medical Record -->
                <div id="medicalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold">Add Medical Record</h2>
                                <button onclick="closeMedicalModal()" class="text-white hover:text-gray-200">
                                    <i class="fas fa-times text-2xl"></i>
                                </button>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('medical-records.store') }}" class="p-6 space-y-4">
                            @csrf
                            <input type="hidden" name="animalID" value="{{ $animal->id }}">
                            
                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Treatment Type <span class="text-red-600">*</span></label>
                                <select name="treatment_type" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" required>
                                    <option value="">Select Treatment Type</option>
                                    <option value="Checkup">Checkup</option>
                                    <option value="Surgery">Surgery</option>
                                    <option value="Emergency">Emergency</option>
                                    <option value="Dental">Dental</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">
                                    Weight (kg) <span class="text-red-600">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="weight" 
                                    step="0.01" 
                                    min="0" 
                                    value="{{ old('weight', $animal->weight) }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                    placeholder="Enter animal weight" 
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Diagnosis <span class="text-red-600">*</span></label>
                                <textarea name="diagnosis" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Enter diagnosis" required></textarea>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Action Taken <span class="text-red-600">*</span></label>
                                <textarea name="action" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Enter action taken" required></textarea>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Remarks</label>
                                <textarea name="remarks" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Additional remarks (optional)"></textarea>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Veterinarian <span class="text-red-600">*</span></label>
                                <select name="vetID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" required>
                                    <option value="">Select Veterinarian</option>
                                    @foreach($vets as $vet)
                                        <option value="{{ $vet->id }}">{{ $vet->name }} - {{ $vet->specialization }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Cost (RM)</label>
                                <input type="number" name="costs" step="0.01" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="0.00">
                            </div>

                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" onclick="closeMedicalModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300">
                                    <i class="fas fa-plus mr-2"></i>Add Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal for Add Vaccination -->
                <div id="vaccinationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold">Add Vaccination Record</h2>
                                <button onclick="closeVaccinationModal()" class="text-white hover:text-gray-200">
                                    <i class="fas fa-times text-2xl"></i>
                                </button>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('vaccination-records.store') }}" class="p-6 space-y-4">
                            @csrf
                            <input type="hidden" name="animalID" value="{{ $animal->id }}">
                            
                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Vaccine Name <span class="text-red-600">*</span></label>
                                <input type="text" name="name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="e.g., Rabies Vaccine" required>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Vaccine Type <span class="text-red-600">*</span></label>
                                <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" required>
                                    <option value="">Select Type</option>
                                    <option value="Rabies">Rabies</option>
                                    <option value="DHPP">DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)</option>
                                    <option value="Bordetella">Bordetella</option>
                                    <option value="Leptospirosis">Leptospirosis</option>
                                    <option value="Feline Distemper">Feline Distemper</option>
                                    <option value="Feline Leukemia">Feline Leukemia</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">
                                    Weight (kg) <span class="text-red-600">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="weight" 
                                    step="0.01" 
                                    min="0" 
                                    value="{{ old('weight', $animal->weight) }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                    placeholder="Enter animal weight" 
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Next Due Date</label>
                                <input type="date" name="next_due_date" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition">
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Remarks</label>
                                <textarea name="remarks" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="Additional notes (optional)"></textarea>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Veterinarian <span class="text-red-600">*</span></label>
                                <select name="vetID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" required>
                                    <option value="">Select Veterinarian</option>
                                    @foreach($vets as $vet)
                                        <option value="{{ $vet->id }}">{{ $vet->name }} - {{ $vet->specialization }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-800 font-semibold mb-2">Cost (RM)</label>
                                <input type="number" name="costs" step="0.01" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="0.00">
                            </div>

                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" onclick="closeVaccinationModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300">
                                    <i class="fas fa-plus mr-2"></i>Add Vaccination
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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

                    <h2 class="text-xl font-bold text-gray-800 mb-4">Animal Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Species</span>
                            <span class="text-gray-800">{{ $animal->species }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-semibold">Age</span>
                            <span class="text-gray-800">{{ $animal->age }} old</span>
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
                        Assigned Slot
                    </h2>
                    @if($animal->slot)
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-semibold">Name</span>
                                <span class="text-purple-700 font-bold text-lg">
                                    {{ $animal->slot->name ?? $animal->slot->id }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-semibold">Section</span>
                                <span class="text-gray-800">{{ $animal->slot->section ?? 'Main Shelter' }}</span>
                            </div>

                            <div class="flex items-center justify-between mb-4">
                                <span class="text-gray-700 font-semibold">Status</span>

                                @php
                                    $status = $animal->slot->status;
                                    $colorClass = match($status) {
                                        'available' => 'bg-green-200 text-green-700',
                                        'occupied' => 'bg-red-200 text-red-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                @endphp

                                <span class="px-2 py-1 rounded text-xs font-medium {{ $colorClass }}">
                                     {{ ucfirst($status) }}
                                </span>
                            </div>
                            {{-- Reassign Slot Form --}}
                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('caretaker'))
                                <form action="{{ route('animals.assignSlot', $animal->id) }}" method="POST">
                                    @csrf
                                    
                                    <label class="text-gray-700 font-semibold">Reassign Slot</label>
                                    <select name="slot_id" class="w-full border rounded-lg p-2 mb-3">
                                        @foreach($slots as $slot)
                                            <option value="{{ $slot->id }}"
                                                {{ $animal->slotID == $slot->id ? 'selected' : '' }}>
                                                Slot {{ $slot->name ?? $slot->id }} ({{ $slot->section }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                                        Update Slot
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-600">
                            <i class="fas fa-exclamation-circle text-3xl mb-2 text-gray-400"></i>
                            <p class="mb-3">No slot assigned</p>

                            {{-- Assign Slot Form --}}
                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('caretaker'))
                                <form action="{{ route('animals.assignSlot', $animal->id) }}" method="POST">
                                    @csrf
                                    
                                    <select name="slot_id" class="w-full border rounded-lg p-2 mb-3">
                                        <option value="">Select Slot</option>
                                        @foreach($slots as $slot)
                                            <option value="{{ $slot->id }}">
                                                Slot {{ $slot->name ?? $slot->id }} ({{ $slot->section }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                                        Assign Slot
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                </div>

                <!-- Action Card -->
                @role('public user|caretaker')
                    @if($animal->adoption_status == 'Not Adopted')
                        <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg shadow-lg p-6 text-white">
                            <h3 class="text-xl font-bold mb-2">Interested in adopting?</h3>
                            <p class="text-purple-100 mb-4">Contact us to learn more about {{ $animal->name }} and start the adoption process.</p>
                            <button onclick="openBookAdoptionModal({{ $animal->id }}, '{{ $animal->name }}', '{{ $animal->species }}')" 
                                    class="w-full bg-white text-purple-700 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300">
                                <i class="fas fa-heart mr-2"></i>Book {{ $animal->name }} for Adoption
                            </button>
                        </div>
                    @endif
                @endrole
            </div>
        </div>
    </div>
    <!-- Include the booking modal -->
    @include('booking-adoption.book-animal')

    <script>
        function changeImage(imagePath) {
            document.getElementById('mainImage').src = imagePath;
        }
        function openMedicalModal() {
            document.getElementById('medicalModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMedicalModal() {
            document.getElementById('medicalModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openVaccinationModal() {
            document.getElementById('vaccinationModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeVaccinationModal() {
            document.getElementById('vaccinationModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modals when clicking outside
        document.getElementById('medicalModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMedicalModal();
            }
        });

        document.getElementById('vaccinationModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeVaccinationModal();
            }
        });
    </script>
</body>
</html>