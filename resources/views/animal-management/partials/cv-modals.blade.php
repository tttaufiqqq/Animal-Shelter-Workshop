{{-- Modals for Clinics & Vets Management --}}

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

{{-- Edit Clinic Modal --}}
<div id="editClinicModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">Edit Clinic</h2>
                <button onclick="closeEditClinicModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form id="editClinicForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-800 font-semibold mb-2">Clinic Name <span class="text-red-600">*</span></label>
                <input type="text" id="edit_clinic_name" name="name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="Enter clinic name" required>
            </div>

            {{-- Address Search --}}
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Search Address</label>
                <div class="flex gap-2">
                    <input type="text" id="editClinicAddressSearch" placeholder="Enter address to search..."
                           class="flex-1 border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition">
                    <button type="button" id="editClinicSearchBtn"
                            class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-300">
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
                <div id="editClinicMap" class="w-full h-64 rounded-lg border border-gray-300"></div>
                <p class="text-sm text-red-600 mt-2 hidden" id="editClinicMapError">Please select a location on the map</p>
            </div>

            {{-- Latitude & Longitude --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Latitude <span class="text-red-600">*</span></label>
                    <input type="text" id="edit_clinicLatitude" name="latitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="Auto-filled" readonly required>
                </div>
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Longitude <span class="text-red-600">*</span></label>
                    <input type="text" id="edit_clinicLongitude" name="longitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50 focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="Auto-filled" readonly required>
                </div>
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Address <span class="text-red-600">*</span></label>
                <textarea id="edit_clinicAddress" name="address" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" rows="3" placeholder="Full address will be auto-filled" required></textarea>
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                <input type="tel" id="edit_phone" name="contactNum" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="+60 3-1234 5678" required>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEditClinicModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal for Add Vet --}}
<div id="vetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">Add New Vet</h2>
                <button onclick="closeModal('vet')" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('animal-management.store-vets') }}" class="p-6 space-y-4">
            @csrf
            @method('POST')
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Full Name <span class="text-red-600">*</span></label>
                <input type="text" name="full_name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="Dr. Name" required>
            </div>
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Specialization <span class="text-red-600">*</span></label>
                <input type="text" name="specialization" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="e.g., Small Animals, Surgery" required>
            </div>
            <div>
                <label class="block text-gray-800 font-semibold mb-2">License Number <span class="text-red-600">*</span></label>
                <input type="text" name="license_no" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="e.g., 408688" required>
            </div>
            <div>
                <label class="block text-gray-800 font-semibold mb-2">Clinic <span class="text-red-600">*</span></label>
                <select name="clinicID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white">
                    @if($clinics->count() > 0)
                        <option value="">Select Clinic</option>
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                        @endforeach
                    @else
                        <option disabled>No clinics available — please add one first.</option>
                    @endif
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                    <input type="tel" name="phone" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="+60 12-345 6789" required>
                </div>
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Email <span class="text-red-600">*</span></label>
                    <input type="email" name="email" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-green-500 focus:ring focus:ring-green-200 transition" placeholder="dr.name@example.com" required>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal('vet')" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add Veterinarian
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Vet Modal --}}
<div id="editVetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">Edit Veterinarian</h2>
                <button onclick="closeEditVetModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form id="editVetForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-800 font-semibold mb-2">Full Name <span class="text-red-600">*</span></label>
                <input type="text" id="edit_vet_name" name="name" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="Dr. Name" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">Specialization <span class="text-red-600">*</span></label>
                <input type="text" id="edit_vet_specialization" name="specialization" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="e.g., Small Animals, Surgery" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">License Number <span class="text-red-600">*</span></label>
                <input type="text" id="edit_vet_license_no" name="license_no" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="e.g., 408688" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">Clinic <span class="text-red-600">*</span></label>
                <select id="edit_vet_clinicID" name="clinicID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-orange-500 focus:ring focus:ring-orange-200 transition" required>
                    @if($clinics->count() > 0)
                        <option value="">Select Clinic</option>
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                        @endforeach
                    @else
                        <option disabled>No clinics available — please add one first.</option>
                    @endif
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Phone <span class="text-red-600">*</span></label>
                    <input type="tel" id="edit_vet_contactNum" name="contactNum" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="+60 12-345 6789" required>
                </div>
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Email <span class="text-red-600">*</span></label>
                    <input type="email" id="edit_vet_email" name="email" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-orange-500 focus:ring focus:ring-orange-200 transition" placeholder="dr.name@example.com" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEditVetModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-semibold rounded-lg hover:from-orange-600 hover:to-orange-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
