{{-- Modal for Add Clinic --}}
<div id="clinicModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">Add New Clinic</h2>
                <button onclick="closeModal('clinic')" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('animal-management.store-clinics') }}" class="p-6 space-y-4">
            @csrf
            @method('POST')
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Clinic Name <span class="text-red-600">*</span></label>
                <input type="text" name="clinic_name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Enter clinic name" required>
            </div>

            {{-- Address Search --}}
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Search Address</label>
                <div class="flex gap-2">
                    <input type="text" id="clinicAddressSearch" placeholder="Enter address to search..."
                           class="flex-1 border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                    <button type="button" id="clinicSearchBtn"
                            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300">
                        <i class="fas fa-search mr-1"></i>Search
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-2">Search for an address or click on the map to pin a location</p>
            </div>

            {{-- Map --}}
            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Select Location on Map <span class="text-red-600">*</span>
                </label>
                <div id="clinicMap" class="w-full h-64 rounded-lg border border-gray-300"></div>
                <p class="text-sm text-red-600 mt-2 hidden" id="clinicMapError">Please select a location on the map</p>
            </div>

            {{-- Latitude & Longitude --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Latitude <span class="text-red-600">*</span></label>
                    <input type="text" id="clinicLatitude" name="latitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Auto-filled" readonly required>
                </div>
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Longitude <span class="text-red-600">*</span></label>
                    <input type="text" id="clinicLongitude" name="longitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Auto-filled" readonly required>
                </div>
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Address <span class="text-red-600">*</span></label>
                <textarea id="clinicAddress" name="address" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" rows="3" placeholder="Full address will be auto-filled" required></textarea>
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                <input type="tel" name="phone" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="+60 3-1234 5678" required>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal('clinic')" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add Clinic
                </button>
            </div>
        </form>
    </div>
</div>
