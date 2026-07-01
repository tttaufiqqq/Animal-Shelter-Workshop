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
