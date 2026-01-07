<!-- Step 2: City & State (Auto-filled and Disabled) -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    <h3 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">2</span>
        Location Details (Auto-filled)
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                City <span class="text-red-600">*</span>
            </label>
            <input type="text" name="city" id="cityInput"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                   readonly required>
            <p class="text-xs text-gray-500 mt-1">⚠️ Auto-filled based on pinned location</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                State <span class="text-red-600">*</span>
            </label>
            <select name="state" id="stateInput"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed appearance-none"
                    disabled required>
                <option value="">Select state</option>
                @foreach(['Johor', 'Kedah', 'Kelantan', 'Malacca', 'Negeri Sembilan', 'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'] as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">⚠️ Auto-filled based on pinned location</p>
        </div>
    </div>
</div>
