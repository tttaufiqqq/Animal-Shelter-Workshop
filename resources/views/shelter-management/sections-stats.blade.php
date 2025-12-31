{{-- Stats Overview - Sections View --}}
<div id="sectionsStats" class="stats-section hidden grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Sections</p>
                <p class="text-3xl font-bold text-gray-800">{{ $sections->count() }}</p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-full">
                <i class="fas fa-layer-group text-indigo-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Slots</p>
                <p class="text-3xl font-bold text-purple-600">{{ $totalSlots }}</p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-door-open text-purple-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Avg Slots/Section</p>
                <p class="text-3xl font-bold text-blue-600">{{ $sections->count() > 0 ? number_format($totalSlots / $sections->count(), 1) : 0 }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-chart-bar text-blue-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Categories</p>
                <p class="text-3xl font-bold text-pink-600">{{ $categories->count() }}</p>
            </div>
            <div class="bg-pink-100 p-3 rounded-full">
                <i class="fas fa-tags text-pink-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>
