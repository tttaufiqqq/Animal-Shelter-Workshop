<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shelter Slots Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
@include('navbar')

<!-- Limited Connectivity Warning Banner -->
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
            <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. You may experience limited functionality or missing data.</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($dbDisconnected as $connection => $info)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ $info['module'] }}
                </span>
                @endforeach
            </div>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto flex-shrink-0 text-yellow-400 hover:text-yellow-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
</div>
@endif

<!-- Header -->
<div class="mb-8 bg-gradient-to-r from-purple-600 to-purple-800 shadow-lg p-8 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-white mb-2">
            <i class="fas fa-door-open text-purple-900 mr-3"></i>
            Shelter Slots Management
        </h1>
        <p class="text-purple-100">Manage all shelter slots and their capacities</p>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-6">
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Slots</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalSlots }}</p>
                </div>
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-door-open text-indigo-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Available</p>
                    <p class="text-3xl font-bold text-green-600">{{ $availableSlots }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Occupied</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $occupiedSlots }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-circle text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Under Maintenance</p>
                    <p class="text-3xl font-bold text-red-600">{{ $maintenanceSlots }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-tools text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @role('admin')
    <div class="mb-6 flex gap-3 flex-wrap">
        <button onclick="openSectionModal()" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition duration-300 shadow-md">
            <i class="fas fa-plus mr-2"></i>Add Section
        </button>
        <button onclick="openSlotModal()" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-300 shadow-md">
            <i class="fas fa-plus mr-2"></i>Add Slot
        </button>
        <button onclick="openCategoryModal()" class="px-6 py-3 bg-gradient-to-r from-pink-500 to-pink-600 text-white font-semibold rounded-lg hover:from-pink-600 hover:to-pink-700 transition duration-300 shadow-md">
            <i class="fas fa-plus mr-2"></i>Add Category
        </button>
    </div>
    @endrole

    <!-- Slots Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        @if($slots->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Slot Name
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Section
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Capacity
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Occupancy
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Animals
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Inventory
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($slots as $slot)
                        @php
                            $statusColors = [
                                'available' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-500', 'progress' => 'bg-green-500'],
                                'occupied' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-500', 'progress' => 'bg-orange-500'],
                                'maintenance' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-500', 'progress' => 'bg-red-500'],
                            ];
                            $colors = $statusColors[$slot->status] ?? $statusColors['available'];
                            $occupancy = $slot->animals->count();
                            $occupancyPercent = $slot->capacity > 0 ? ($occupancy / $slot->capacity) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-bold text-gray-900">{{ $slot->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-layer-group text-gray-400 mr-2"></i>
                                    <span class="text-sm text-gray-700">{{ $slot->section->name ?? 'No Section' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                       <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors['bg'] }} {{ $colors['text'] }}">
                                           {{ ucfirst($slot->status) }}
                                       </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">
                                {{ $slot->capacity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-full max-w-[120px]">
                                        <div class="flex items-center justify-between mb-1">
                                                   <span class="text-xs font-medium {{ $colors['text'] }}">
                                                       {{ $occupancy }} / {{ $slot->capacity }}
                                                   </span>
                                            <span class="text-xs text-gray-500">
                                                       {{ number_format($occupancyPercent, 0) }}%
                                                   </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="{{ $colors['progress'] }} h-2 rounded-full transition-all duration-500"
                                                 style="width: {{ min($occupancyPercent, 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-paw text-gray-400 mr-2"></i>
                                    <span class="text-sm font-semibold text-gray-700">{{ $occupancy }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-box text-gray-400 mr-2"></i>
                                    <span class="text-sm font-semibold text-gray-700">{{ $slot->inventories->count() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="viewSlotDetails({{ $slot->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 transition duration-150"
                                            title="View Details">
                                        <i class="fas fa-info-circle text-lg"></i>
                                    </button>
                                    @role('admin')
                                    <button onclick="editSlot({{ $slot->id }})"
                                            class="text-gray-600 hover:text-gray-900 transition duration-150"
                                            title="Edit Slot">
                                        <i class="fas fa-edit text-lg"></i>
                                    </button>
                                    <button onclick="deleteSlot({{ $slot->id }})"
                                            class="text-red-600 hover:text-red-900 transition duration-150"
                                            title="Delete Slot">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <div class="text-6xl mb-4">🏠</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No Slots Yet</h3>
                <p class="text-gray-600 mb-6">Start by adding your first shelter slot.</p>
                @role('admin')
                <button onclick="openSlotModal()" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add Your First Slot
                </button>
                @endrole
            </div>
        @endif
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $slots->links() }}
    </div>
</div>

<!-- Add/Edit Section Modal -->
<div id="sectionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-layer-group mr-2"></i>
                    <span id="sectionModalTitle">Add New Section</span>
                </h2>
                <button onclick="closeSectionModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('shelter-management.store-section') }}" class="p-6 space-y-4" id="sectionForm">
            @csrf
            <input type="hidden" name="_method" value="POST" id="sectionFormMethod">
            <input type="hidden" name="section_id" id="sectionId">

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Section Name <span class="text-red-600">*</span>
                </label>
                <input type="text" name="name" id="sectionName" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition" placeholder="e.g., Building A, Wing B" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Description <span class="text-red-600">*</span>
                </label>
                <textarea name="description" id="sectionDescription"
                          class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition"
                          rows="3"
                          placeholder="e.g., Located in Building A, used for recovering animals."
                          required></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeSectionModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <span id="sectionSubmitButtonText">Add Section</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add/Edit Slot Modal -->
<div id="slotModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-door-open mr-2"></i>
                    <span id="slotModalTitle">Add New Slot</span>
                </h2>
                <button onclick="closeSlotModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('shelter-management.store-slot') }}" class="p-6 space-y-4" id="slotForm">
            @csrf
            <input type="hidden" name="_method" value="POST" id="slotFormMethod">
            <input type="hidden" name="slot_id" id="slotId">

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Slot Name <span class="text-red-600">*</span>
                </label>
                <input type="text" name="name" id="slotName" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition" placeholder="e.g., Slot A1, Kennel 1" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Section <span class="text-red-600">*</span>
                </label>
                <select name="sectionID" id="slotSection" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition" required>
                    <option value="">Select a section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    If section doesn't exist, <a href="javascript:void(0)" onclick="closeSlotModal(); openSectionModal();" class="text-indigo-600 hover:text-indigo-800 font-semibold">create one first</a>
                </p>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Capacity <span class="text-red-600">*</span>
                </label>
                <input type="number" name="capacity" id="slotCapacity" min="1" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition" placeholder="Maximum number of animals" required>
            </div>

            <div id="slotStatusField" class="hidden">
                <label class="block text-gray-800 font-semibold mb-2">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" id="slotStatus" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition">
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Under Maintenance</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeSlotModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <span id="slotSubmitButtonText">Add Slot</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-tags mr-2"></i>
                    <span id="categoryModalTitle">Add New Category</span>
                </h2>
                <button onclick="closeCategoryModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('shelter-management.store-category') }}" class="p-6 space-y-4" id="categoryForm">
            @csrf
            <input type="hidden" name="_method" value="POST" id="categoryFormMethod">
            <input type="hidden" name="category_id" id="categoryId">

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Main Category <span class="text-red-600">*</span>
                </label>
                <input type="text" name="main" id="categoryMain" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition" placeholder="e.g., Food, Medical Supplies" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Sub Category <span class="text-red-600">*</span>
                </label>
                <input type="text" name="sub" id="categorySub" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition" placeholder="e.g., Dry Food, Wet Food" required>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeCategoryModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <span id="categorySubmitButtonText">Add Category</span>
                </button>
            </div>
        </form>
    </div>
</div>

@include('shelter-management.slot-detail-modal')
@include('shelter-management.inventory-create-modal', ['categories' => $categories])
@include('shelter-management.inventory-detail-modal', ['categories' => $categories])
@include('shelter-management.animal-detail-modal')

    <script>
        // Section Modal Functions
        function openSectionModal() {
            document.getElementById('sectionModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('sectionForm').reset();
            document.getElementById('sectionModalTitle').textContent = 'Add New Section';
            document.getElementById('sectionSubmitButtonText').textContent = 'Add Section';
            document.getElementById('sectionFormMethod').value = 'POST';
            document.getElementById('sectionId').value = '';
            document.getElementById('sectionForm').action = '{{ route("shelter-management.store-section") }}';
        }

        function closeSectionModal() {
            document.getElementById('sectionModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function editSection(sectionId) {
            document.getElementById('sectionModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('sectionModalTitle').textContent = 'Edit Section';
            document.getElementById('sectionSubmitButtonText').textContent = 'Update Section';
            document.getElementById('sectionFormMethod').value = 'PUT';
            document.getElementById('sectionId').value = sectionId;
            document.getElementById('sectionForm').action = '/shelter-management/sections/' + sectionId;

            fetch(`/shelter-management/sections/${sectionId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('sectionName').value = data.name;
                    document.getElementById('sectionDescription').value = data.description;
                })
                .catch(error => {
                    console.error('Error fetching section data:', error);
                    alert('Failed to load section data. Please try again.');
                    closeSectionModal();
                });
        }

        function deleteSection(sectionId) {
            if (confirm('Are you sure you want to delete this section?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/shelter-management/sections/' + sectionId;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.content;
                    form.appendChild(csrfInput);
                }

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // ==================== SLOT MODAL FUNCTIONS ====================
        function openSlotModal() {
            document.getElementById('slotModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Reset form for adding new slot
            document.getElementById('slotForm').reset();
            document.getElementById('slotModalTitle').textContent = 'Add New Slot';
            document.getElementById('slotSubmitButtonText').textContent = 'Add Slot';
            document.getElementById('slotFormMethod').value = 'POST';
            document.getElementById('slotId').value = '';

            // Reset form action to store route
            document.getElementById('slotForm').action = '{{ route("shelter-management.store-slot") }}';

            // Hide status field for add mode
            document.getElementById('slotStatusField').classList.add('hidden');
            document.getElementById('slotStatus').removeAttribute('required');
        }

        function editSlot(slotId) {
            // Open modal in edit mode
            document.getElementById('slotModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            document.getElementById('slotModalTitle').textContent = 'Edit Slot';
            document.getElementById('slotSubmitButtonText').textContent = 'Update Slot';
            document.getElementById('slotFormMethod').value = 'PUT';
            document.getElementById('slotId').value = slotId;

            // Show status field for edit mode
            document.getElementById('slotStatusField').classList.remove('hidden');
            document.getElementById('slotStatus').setAttribute('required', 'required');

            // Update form action - changed to match new route
            document.getElementById('slotForm').action = '/slots/' + slotId;

            // Fetch slot data via AJAX - changed to match new route
            fetch(`/slots/${slotId}/edit`)
                .then(response => response.json())
                .then(data => {
                    // Populate form fields
                    document.getElementById('slotName').value = data.name;
                    document.getElementById('slotSection').value = data.sectionID;
                    document.getElementById('slotCapacity').value = data.capacity;
                    document.getElementById('slotStatus').value = data.status;
                })
                .catch(error => {
                    console.error('Error fetching slot data:', error);
                    alert('Failed to load slot data. Please try again.');
                    closeSlotModal();
                });
        }

        function deleteSlot(slotId) {
            if (confirm('Are you sure you want to delete this slot? This action cannot be undone.')) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/slots/' + slotId;

                // Add CSRF token - Get fresh token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content'); // Use getAttribute
                    form.appendChild(csrfInput);
                } else {
                    // Fallback: try to get from any existing form
                    const existingToken = document.querySelector('input[name="_token"]');
                    if (existingToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = existingToken.value;
                        form.appendChild(csrfInput);
                    } else {
                        alert('Security token not found. Please refresh the page and try again.');
                        return;
                    }
                }

                // Add DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Category Modal Functions
        function openCategoryModal() {
            document.getElementById('categoryModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModalTitle').textContent = 'Add New Category';
            document.getElementById('categorySubmitButtonText').textContent = 'Add Category';
            document.getElementById('categoryFormMethod').value = 'POST';
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryForm').action = '{{ route("shelter-management.store-category") }}';
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function editCategory(categoryId) {
            document.getElementById('categoryModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            document.getElementById('categorySubmitButtonText').textContent = 'Update Category';
            document.getElementById('categoryFormMethod').value = 'PUT';
            document.getElementById('categoryId').value = categoryId;
            document.getElementById('categoryForm').action = '/shelter-management/categories/' + categoryId;

            fetch(`/shelter-management/categories/${categoryId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('categoryMain').value = data.main;
                    document.getElementById('categorySub').value = data.sub;
                })
                .catch(error => {
                    console.error('Error fetching category data:', error);
                    alert('Failed to load category data. Please try again.');
                    closeCategoryModal();
                });
        }

        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/shelter-management/categories/' + categoryId;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.content;
                    form.appendChild(csrfInput);
                }

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modals when clicking outside
        document.getElementById('sectionModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeSectionModal();
        });

        document.getElementById('categoryModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCategoryModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSectionModal();
                closeCategoryModal();
                closeSlotModal();
            }
        });

        // Close modal when clicking outside
        document.getElementById('slotModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSlotModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSlotModal();
            }
        });

        function closeSlotModal() {
            document.getElementById('slotModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>
