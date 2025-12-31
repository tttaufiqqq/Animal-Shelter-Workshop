<!-- Left Column: Step Indicator (3 columns) -->
<div class="col-span-3 border-r border-gray-200 pr-6">
    <div id="progressIndicator" class="sticky top-0">
        <div class="flex flex-col gap-0">
            <div id="step1Indicator" class="flex items-center gap-3">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div id="step1Line" class="w-0.5 h-12 bg-gray-300"></div>
                </div>
                <span class="text-sm font-medium text-gray-900 -mt-8">Count</span>
            </div>
            <div id="step2Indicator" class="flex items-center gap-3">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">
                        2
                    </div>
                    <div id="step2Line" class="w-0.5 h-12 bg-gray-300"></div>
                </div>
                <span class="text-sm font-medium text-gray-400 -mt-8">Add Animals</span>
            </div>
            <div id="step3Indicator" class="flex items-center gap-3">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">
                        3
                    </div>
                </div>
                <span class="text-sm font-medium text-gray-400 -mt-8">Confirm</span>
            </div>
        </div>
    </div>
</div>
