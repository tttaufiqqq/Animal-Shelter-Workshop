{{-- Empty State Component --}}
<div class="bg-white rounded-lg shadow-sm p-12 md:p-16 text-center border border-gray-200">
    <div class="max-w-md mx-auto">
        <div class="bg-gray-100 w-24 h-24 rounded flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-search text-5xl text-gray-400"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-3">No Animals Found</h3>
        <p class="text-gray-600 mb-8">
            We couldn't find any animals matching your current filters. Try adjusting your search criteria or clear all filters to see all available animals.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('animal-management.index') }}"
               class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded font-medium">
                <i class="fas fa-redo"></i>
                Clear All Filters
            </a>
        </div>
    </div>
</div>
