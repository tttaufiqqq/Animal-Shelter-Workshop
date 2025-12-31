{{-- Search and Filter Section - Sections View --}}
<div id="sectionsFilters" class="search-filter-section hidden bg-white rounded-lg shadow-md p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-3 items-center">
        <div class="flex-1 w-full md:w-auto">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text"
                       id="searchSectionsInput"
                       placeholder="Search by section name..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                       onkeyup="filterSections()">
            </div>
        </div>
        <button onclick="clearFilters()"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200 whitespace-nowrap">
            <i class="fas fa-times mr-1"></i>Clear
        </button>
    </div>
    <div id="sectionsResultsCount" class="mt-3 text-sm text-gray-600"></div>
</div>
