<!-- Step 4: Upload Images -->
<div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
    <h3 class="text-lg font-semibold text-orange-900 mb-3 flex items-center">
        <span class="bg-orange-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">4</span>
        Upload Images
    </h3>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Photos of the Animal <span class="text-red-600">*</span>
        </label>
        <input type="file" name="images[]" multiple accept="image/*" id="imageInput"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        <p class="text-xs text-gray-600 mt-1">📷 Upload 1-5 images (max 5MB each). Clear photos help caretakers identify the animal.</p>
        <div id="imagePreview" class="mt-2 grid grid-cols-3 gap-2"></div>
    </div>
</div>
