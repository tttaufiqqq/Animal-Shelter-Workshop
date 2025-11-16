<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shelter Slots Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
   @include('navbar')
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
                        <p class="text-3xl font-bold text-gray-800">{{ $slots->count() }}</p>
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
                        <p class="text-3xl font-bold text-green-600">{{ $slots->where('status', 'available')->count() }}</p>
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
                        <p class="text-3xl font-bold text-orange-600">{{ $slots->where('status', 'occupied')->count() }}</p>
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
                        <p class="text-3xl font-bold text-red-600">{{ $slots->where('status', 'maintenance')->count() }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-tools text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slots Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Add Slot Card --> @role('admin')
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition duration-300 cursor-pointer transform hover:scale-105" onclick="openSlotModal()">
                <div class="p-8 text-white text-center h-full flex flex-col items-center justify-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-6 mb-4">
                        <i class="fas fa-plus text-5xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Add New Slot</h3>
                    <p class="text-indigo-100">Create a new shelter slot</p>
                </div>
            </div>@endrole

            <!-- Slot Cards -->
            @forelse($slots as $slot)
                @php
                    $statusColors = [
                        'available' => ['bg' => 'bg-green-500', 'text' => 'text-green-600', 'border' => 'border-green-500'],
                        'occupied' => ['bg' => 'bg-orange-500', 'text' => 'text-orange-600', 'border' => 'border-orange-500'],
                        'maintenance' => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'border' => 'border-red-500'],
                    ];
                    $colors = $statusColors[$slot->status] ?? $statusColors['available'];
                    $occupancy = $slot->animals->count();
                    $occupancyPercent = $slot->capacity > 0 ? ($occupancy / $slot->capacity) * 100 : 0;
                @endphp

                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition duration-300 border-t-4 {{ $colors['border'] }}">
                    <!-- Slot Header -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-2xl font-bold text-gray-800">{{ $slot->name }}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colors['bg'] }} text-white">
                                {{ ucfirst($slot->status) }}
                            </span>
                        </div>
                        <p class="text-gray-600 flex items-center">
                            <i class="fas fa-layer-group mr-2"></i>
                            <span class="font-semibold">{{ $slot->section }}</span>
                        </p>
                    </div>

                    <!-- Slot Body -->
                    <div class="p-6">
                        <!-- Capacity Info -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600 font-medium">Capacity</span>
                                <span class="text-sm font-bold {{ $colors['text'] }}">
                                    {{ $occupancy }} / {{ $slot->capacity }}
                                </span>
                            </div>
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div class="{{ $colors['bg'] }} h-3 rounded-full transition-all duration-500" 
                                     style="width: {{ min($occupancyPercent, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <i class="fas fa-paw {{ $colors['text'] }} text-xl mb-1"></i>
                                <p class="text-xs text-gray-600">Animals</p>
                                <p class="text-lg font-bold text-gray-800">{{ $occupancy }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <i class="fas fa-box {{ $colors['text'] }} text-xl mb-1"></i>
                                <p class="text-xs text-gray-600">Inventory</p>
                                <p class="text-lg font-bold text-gray-800">{{ $slot->inventories->count() }}</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button class="flex-1 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white py-2 rounded-lg font-semibold hover:from-indigo-600 hover:to-indigo-700 transition duration-300">
                                <i class="fas fa-info-circle mr-1"></i>Details
                            </button>
                            <button onclick="editSlot({{ $slot->id }})" class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-300">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteSlot({{ $slot->id }})" class="px-4 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition duration-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State (Only shows if no slots exist) -->
                <div class="col-span-full bg-white rounded-lg shadow p-12 text-center">
                    <div class="text-6xl mb-4">🏠</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No Slots Yet</h3>
                    <p class="text-gray-600 mb-6">Start by adding your first shelter slot.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Add/Edit Slot Modal -->
    <div id="slotModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold">
                        <i class="fas fa-door-open mr-2"></i>
                        <span id="modalTitle">Add New Slot</span>
                    </h2>
                    <button onclick="closeSlotModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('shelter-management.store-slot') }}" class="p-6 space-y-4" id="slotForm">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
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

                  <select name="section" id="slotSection" 
                     class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border
                           focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition"
                     required>
                     <option value="" disabled selected>-- Select Section --</option>
                     <option value="Cat Zone">Cat Zone</option>
                     <option value="Dog Zone">Dog Zone</option>
                     <option value="Medical Wing">Medical Wing</option>
                     <option value="Puppy Section">Puppy Section</option>
                     <option value="Exercise Yard">Exercise Yard</option>
                  </select>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Capacity <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="capacity" id="slotCapacity" min="1" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition" placeholder="Maximum number of animals" required>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeSlotModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition duration-300">
                        <i class="fas fa-save mr-2"></i>
                        <span id="submitButtonText">Add Slot</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openSlotModal() {
            document.getElementById('slotModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Reset form for adding new slot
            document.getElementById('slotForm').reset();
            document.getElementById('modalTitle').textContent = 'Add New Slot';
            document.getElementById('submitButtonText').textContent = 'Add Slot';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('slotId').value = '';
        }

        function closeSlotModal() {
            document.getElementById('slotModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function editSlot(slotId) {
            // You'll need to fetch slot data via AJAX or pass it from backend
            // For now, just open the modal
            alert('Edit functionality - Slot ID: ' + slotId);
            // openSlotModal();
            // document.getElementById('modalTitle').textContent = 'Edit Slot';
            // document.getElementById('submitButtonText').textContent = 'Update Slot';
            // document.getElementById('formMethod').value = 'PUT';
            // document.getElementById('slotId').value = slotId;
        }

        function deleteSlot(slotId) {
            if (confirm('Are you sure you want to delete this slot?')) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/shelter-management/slots/' + slotId;
                
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
    </script>
</body>
</html>