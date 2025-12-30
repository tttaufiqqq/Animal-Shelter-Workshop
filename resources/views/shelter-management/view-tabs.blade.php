{{-- View Switcher Tabs --}}
<div class="bg-white rounded-lg shadow-md mb-6 p-2">
    <div class="flex gap-2">
        <button onclick="switchView('sections')" id="sectionsTab" class="view-tab flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2">
            <i class="fas fa-layer-group"></i>
            <span>Sections</span>
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/20">{{ $sections->count() }}</span>
        </button>
        <button onclick="switchView('slots')" id="slotsTab" class="view-tab flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-md">
            <i class="fas fa-door-open"></i>
            <span>Slots</span>
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/20">{{ $totalSlots }}</span>
        </button>
        <button onclick="switchView('categories')" id="categoriesTab" class="view-tab flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/20">{{ $categories->count() }}</span>
        </button>
    </div>
</div>
