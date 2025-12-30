{{-- Add/Edit Slot Modal --}}
<div id="slotModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
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
        <form method="POST" action="{{ route($routePrefix . '.store-slot') }}" class="p-6 space-y-4" id="slotForm">
            @csrf
            <input type="hidden" name="_method" value="POST" id="slotFormMethod">
            <input type="hidden" name="slot_id" id="slotId">

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Slot Name <span class="text-red-600">*</span>
                </label>
                <input type="text" name="name" id="slotName" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200" placeholder="e.g., Slot A1, Kennel 1" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Section <span class="text-red-600">*</span>
                </label>
                <select name="sectionID" id="slotSection" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
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
                <input type="number" name="capacity" id="slotCapacity" min="1" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200" placeholder="Maximum number of animals" required>
            </div>

            <div id="slotStatusField" class="hidden">
                <label class="block text-gray-800 font-semibold mb-2">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" id="slotStatus" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Under Maintenance</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeSlotModal()" id="slotCancelBtn" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" id="slotSubmitBtn" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 flex items-center gap-2">
                    <i class="fas fa-save" id="slotSubmitIcon"></i>
                    <span id="slotSubmitButtonText">Add Slot</span>
                </button>
            </div>
        </form>
    </div>
</div>
