<!-- Report Detail Modal (Expanded View) -->
<div id="reportDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-[1000px] max-w-full max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Detail Header (Fixed at top) -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 flex-shrink-0 rounded-t-2xl z-20 relative">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📄</span>
                    <div>
                        <h2 class="text-2xl font-bold" id="detailReportTitle">Report Details</h2>
                        <p class="text-purple-100 text-sm">Complete information about this report</p>
                    </div>
                </div>
                <button onclick="closeReportDetailModal()" class="text-white hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Detail Body (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-6 relative z-10" id="reportDetailContent">
            <!-- Content will be populated dynamically -->
        </div>
    </div>
</div>
