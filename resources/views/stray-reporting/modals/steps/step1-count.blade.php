<!-- Step 1: Animal Count (Initial) -->
<div id="step1Content">
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-6 mb-6 border border-purple-200">
        <div class="flex items-start gap-4">
            <div class="bg-purple-600 text-white p-3 rounded-xl">
                <i class="fas fa-list-ol text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Animal Count</h3>
                <p class="text-sm text-gray-600">
                    Specify how many animals were successfully rescued in this operation.
                </p>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <label class="flex items-center gap-2 text-gray-800 font-semibold mb-3">
            <i class="fas fa-hashtag text-purple-600"></i>
            Number of Animals Rescued <span class="text-red-600">*</span>
        </label>
        <input type="number" id="animalCount" min="1" max="20" value="1" required
               class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition"
               placeholder="Enter number (1-20)">
        <div class="mt-3 flex items-start gap-2 bg-purple-50 p-3 rounded-lg border border-purple-200">
            <i class="fas fa-info-circle text-purple-600 mt-0.5"></i>
            <p class="text-xs text-gray-700">
                You'll be asked to provide details for each animal in the next step. Maximum 20 animals per rescue.
            </p>
        </div>
    </div>
</div>
