{{-- Stats Overview - Categories View --}}
<div id="categoriesStats" class="stats-section hidden grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Categories</p>
                <p class="text-3xl font-bold text-gray-800">{{ $categories->count() }}</p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-full">
                <i class="fas fa-tags text-indigo-600 text-2xl"></i>
            </div>
        </div>
    </div>

    @php
        $mainCategories = $categories->pluck('main')->unique()->count();
    @endphp

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Main Categories</p>
                <p class="text-3xl font-bold text-purple-600">{{ $mainCategories }}</p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-folder text-purple-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Sub Categories</p>
                <p class="text-3xl font-bold text-pink-600">{{ $categories->count() }}</p>
            </div>
            <div class="bg-pink-100 p-3 rounded-full">
                <i class="fas fa-sitemap text-pink-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>
