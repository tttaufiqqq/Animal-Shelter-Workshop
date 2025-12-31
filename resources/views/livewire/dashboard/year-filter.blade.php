<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
    <select wire:model.live="selectedYear" class="w-80 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        @foreach($years as $year)
            <option value="{{ $year }}">{{ $year }}</option>
        @endforeach
    </select>
</div>
