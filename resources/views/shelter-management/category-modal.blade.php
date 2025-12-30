{{-- Add/Edit Category Modal --}}
<div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-tags mr-2"></i>
                    <span id="categoryModalTitle">Add New Category</span>
                </h2>
                <button onclick="closeCategoryModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route($routePrefix . '.store-category') }}" class="p-6 space-y-4" id="categoryForm">
            @csrf
            <input type="hidden" name="_method" value="POST" id="categoryFormMethod">
            <input type="hidden" name="category_id" id="categoryId">

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Main Category <span class="text-red-600">*</span>
                </label>
                <input type="text" name="main" id="categoryMain" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200" placeholder="e.g., Food, Medical Supplies" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Sub Category <span class="text-red-600">*</span>
                </label>
                <input type="text" name="sub" id="categorySub" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-indigo-500 focus:ring focus:ring-indigo-200" placeholder="e.g., Dry Food, Wet Food" required>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeCategoryModal()" id="categoryCancelBtn" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" id="categorySubmitBtn" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 flex items-center gap-2">
                    <i class="fas fa-save" id="categorySubmitIcon"></i>
                    <span id="categorySubmitButtonText">Add Category</span>
                </button>
            </div>
        </form>
    </div>
</div>
