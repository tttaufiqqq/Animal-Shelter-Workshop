<!-- Modal for Add Medical Record -->
<div id="medicalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-[9999] p-4">
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
                    Weight during treatment (kg) <span class="text-red-600">*</span>
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
