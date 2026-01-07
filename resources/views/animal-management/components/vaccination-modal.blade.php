<!-- Modal for Add Vaccination -->
<div id="vaccinationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">Add Vaccination Record</h2>
                <button onclick="closeVaccinationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('vaccination-records.store') }}" class="p-6 space-y-4" id="vaccinationRecordForm" onsubmit="handleVaccinationSubmit(event)">
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
                    Weight during taking the vaccine (kg) <span class="text-red-600">*</span>
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
                <button type="submit" id="vaccinationSubmitBtn" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="vaccinationBtnText">
                        <i class="fas fa-plus mr-2"></i>Add Vaccination
                    </span>
                    <span id="vaccinationBtnLoading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Adding...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleVaccinationSubmit(event) {
    const submitBtn = document.getElementById('vaccinationSubmitBtn');
    const btnText = document.getElementById('vaccinationBtnText');
    const btnLoading = document.getElementById('vaccinationBtnLoading');

    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    // Form will submit normally, button stays disabled
}
</script>
