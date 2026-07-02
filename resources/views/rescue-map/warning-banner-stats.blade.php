    {{-- Database Warning Banner --}}
    @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
        <div class="mb-4 flex items-center gap-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($dbDisconnected as $connection => $info)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $info['module'] }}
                        </span>
                    @endforeach
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    {{-- Page Content --}}
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Rescue Operations Map</h1>
            <p class="text-sm text-gray-600 mt-1">Real-time rescue operations tracking</p>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-admin.stat-card
                title="Total"
                :value="number_format($statistics['total'])"
                color="blue"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Success"
                :value="number_format($statistics['success'])"
                color="green"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Failed"
                :value="number_format($statistics['failed'])"
                color="red"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M6 18L18 6M6 6l12 12\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Scheduled"
                :value="number_format($statistics['scheduled'])"
                color="orange"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="In Progress"
                :value="number_format($statistics['in_progress'])"
                color="purple"
                :icon="'<svg class=\'w-6 h-6 animate-spin\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Pending"
                :value="number_format($statistics['pending'])"
                color="indigo"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\'/>
                </svg>'"
            />
        </div>

        {{-- Map Container --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" style="height: calc(100vh - 450px); min-height: 500px;">
            <div class="relative h-full">
                <div id="map" class="h-full"></div>

                {{-- Details Panel (Slide-in) --}}
                <div id="detailsPanel" class="hidden absolute top-4 right-4 bg-white rounded-xl shadow-xl border border-gray-200 p-6 w-96 max-h-[calc(100%-2rem)] overflow-y-auto z-[1000]">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 id="clusterCity" class="text-lg font-bold text-gray-900"></h3>
                            <p class="text-xs text-gray-500 mt-1">Cluster Overview</p>
                        </div>
                        <button onclick="closeDetails()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div id="clusterStats" class="space-y-2 mb-4"></div>

                    <div class="bg-green-50 rounded-lg p-3 border border-green-100 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-medium text-gray-700">Success Rate</span>
                            <span id="successRate" class="text-sm font-bold text-green-600"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div id="successBar" class="bg-green-500 h-2 rounded-full transition-all duration-500"></div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Recent Reports
                        </h4>
                        <div id="reportsList" class="space-y-2 max-h-48 overflow-y-auto"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-center gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-gradient-to-br from-green-500 to-green-600 rounded-full"></div>
                    <span class="text-gray-700">High Success <span class="text-gray-500">(≥70%)</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full"></div>
                    <span class="text-gray-700">Medium <span class="text-gray-500">(40-70%)</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-gradient-to-br from-red-500 to-red-600 rounded-full"></div>
                    <span class="text-gray-700">Low <span class="text-gray-500">(&lt;40%)</span></span>
                </div>
            </div>
        </div>
    </div>
