<!-- Step 3: Animal Information -->
<div class="bg-green-50 border border-green-200 rounded-lg p-4">
    <h3 class="text-lg font-semibold text-green-900 mb-3 flex items-center">
        <span class="bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">3</span>
        Animal Condition & Priority
    </h3>

    <!-- Priority Description Dropdown -->
    <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Situation / Urgency Level <span class="text-red-600">*</span>
        </label>
        <select name="description" id="descriptionSelect"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
            <option value="">-- Select situation --</option>
            <optgroup label="🚨 URGENT - Immediate Action Required">
                <option value="Injured animal - Critical condition" data-priority="critical">🚨 Injured animal - Critical condition</option>
                <option value="Trapped animal - Immediate rescue needed" data-priority="critical">🚨 Trapped animal - Immediate rescue needed</option>
                <option value="Aggressive animal - Public safety risk" data-priority="critical">🚨 Aggressive animal - Public safety risk</option>
            </optgroup>
            <optgroup label="⚠️ HIGH PRIORITY - Needs Attention Soon">
                <option value="Sick animal - Needs medical attention" data-priority="high">⚠️ Sick animal - Needs medical attention</option>
                <option value="Mother with puppies/kittens - Family rescue" data-priority="high">⚠️ Mother with puppies/kittens - Family rescue</option>
                <option value="Young animal (puppy/kitten) - Vulnerable" data-priority="high">⚠️ Young animal (puppy/kitten) - Vulnerable</option>
                <option value="Malnourished animal - Needs care" data-priority="high">⚠️ Malnourished animal - Needs care</option>
            </optgroup>
            <optgroup label="ℹ️ STANDARD - Non-urgent">
                <option value="Healthy stray - Needs rescue" data-priority="normal">ℹ️ Healthy stray - Needs rescue</option>
                <option value="Abandoned pet - Recent" data-priority="normal">ℹ️ Abandoned pet - Recent</option>
                <option value="Friendly stray - Approachable" data-priority="normal">ℹ️ Friendly stray - Approachable</option>
            </optgroup>
        </select>
        <p class="text-xs text-gray-600 mt-1">This helps caretakers prioritize rescues based on urgency</p>
    </div>
</div>
