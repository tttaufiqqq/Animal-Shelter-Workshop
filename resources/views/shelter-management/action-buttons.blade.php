{{-- Action Buttons --}}
@role('admin')
<div class="mb-6">
    <button id="addSectionBtn" onclick="openSectionModal()" class="action-btn hidden px-6 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition duration-300 shadow-md">
        <i class="fas fa-plus mr-2"></i>Add Section
    </button>
    <button id="addSlotBtn" onclick="openSlotModal()" class="action-btn px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-300 shadow-md">
        <i class="fas fa-plus mr-2"></i>Add Slot
    </button>
    <button id="addCategoryBtn" onclick="openCategoryModal()" class="action-btn hidden px-6 py-3 bg-gradient-to-r from-pink-500 to-pink-600 text-white font-semibold rounded-lg hover:from-pink-600 hover:to-pink-700 transition duration-300 shadow-md">
        <i class="fas fa-plus mr-2"></i>Add Category
    </button>
</div>
@endrole
