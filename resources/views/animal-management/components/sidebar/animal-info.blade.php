<!-- Status Card -->
<div class="bg-white rounded-lg shadow-lg p-4 flex justify-between">
    <div class="flex-1">
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
        <div class="flex justify-between items-center mb-4">
            <!-- Left: Title -->
            <h2 class="text-xl font-bold text-gray-800">Animal Information</h2>

            @role('caretaker')
            <!-- Right: Action Icons -->
            <div class="flex space-x-4">
                <a onclick="openEditModal({{ $animal->id }})"
                   class="text-purple-600 hover:text-purple-800 cursor-pointer transition duration-300"
                   title="Edit">
                    <i class="fas fa-edit text-xl"></i>
                </a>

                <form action="{{ route('animal-management.destroy', $animal->id) }}"
                      method="POST"
                      class="inline"
                      onsubmit="return confirm('Are you sure you want to delete this animal record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-red-600 hover:text-red-800 cursor-pointer transition duration-300 border-0 bg-transparent p-0"
                            title="Delete">
                        <i class="fas fa-trash text-xl"></i>
                    </button>
                </form>
            </div>
            @endrole
        </div>

        <!-- Animal Details -->
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600 font-semibold">Species</span>
                <span class="text-gray-800">{{ $animal->species }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600 font-semibold">Weight</span>
                <span class="text-gray-800">{{ $animal->weight }} kg</span>
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
</div>
