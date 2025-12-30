{{-- Search and Filter Section - Slots View --}}
<div id="slotsFilters" class="search-filter-section bg-white rounded-lg shadow-md p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-3 items-center">
        <div class="flex-1 w-full md:w-auto">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text"
                       id="searchSlotsInput"
                       placeholder="Search by slot name..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                       onkeyup="filterSlots()">
            </div>
        </div>
        <div class="w-full md:w-48">
            <select id="statusFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    onchange="filterSlots()">
                <option value="">All Statuses</option>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        <div class="w-full md:w-48">
            <select id="sectionFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    onchange="filterSlots()">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->name }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        <button onclick="clearFilters()"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200 whitespace-nowrap">
            <i class="fas fa-times mr-1"></i>Clear
        </button>
    </div>
    <div id="slotsResultsCount" class="mt-3 text-sm text-gray-600"></div>
</div>
