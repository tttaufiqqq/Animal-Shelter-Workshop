<!-- Health Details Card -->
<div class="fade-in bg-gradient-to-br from-white to-purple-50/30 rounded-2xl shadow-xl p-4 border border-purple-100 hover-scale">
    <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-2 rounded-lg shadow-lg">
            <i class="fas fa-heartbeat text-white text-lg"></i>
        </div>
        <span>Health Details</span>
    </h2>
    <div class="bg-white rounded-xl p-4 shadow-inner border border-gray-100 max-h-32 overflow-y-auto">
        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ $animal->health_details }}</p>
    </div>
</div>
