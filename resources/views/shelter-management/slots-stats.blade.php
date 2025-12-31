{{-- Stats Overview - Slots View --}}
<div id="slotsStats" class="stats-section grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
