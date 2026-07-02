        {{-- Two Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Recent Activity (2/3 width) --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                    <a href="#" class="text-sm text-purple-600 hover:text-purple-700 font-medium">View All</a>
                </div>
                <div class="divide-y divide-gray-200">
                    <!-- Activity Item 1 -->
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    Rescue #456 completed successfully
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Golden Retriever rescued from Kampung Sungai Manis • 2 hours ago
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Item 2 -->
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    New stray report submitted
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Cat spotted near Jalan Bunga Raya • 3 hours ago
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Item 3 -->
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    Adoption booking confirmed
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Booking #789 for Max (Labrador) • 5 hours ago
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions (1/3 width) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-50 transition group">
                        <div class="w-10 h-10 bg-purple-100 group-hover:bg-purple-200 rounded-lg flex items-center justify-center transition">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">View Reports</p>
                            <p class="text-xs text-gray-500">23 pending</p>
                        </div>
                    </a>

                    <a href="{{ route('rescue.map') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-50 transition group">
                        <div class="w-10 h-10 bg-blue-100 group-hover:bg-blue-200 rounded-lg flex items-center justify-center transition">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Rescue Map</p>
                            <p class="text-xs text-gray-500">8 active rescues</p>
                        </div>
                    </a>

                    <a href="{{ route('animal:main') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 transition group">
                        <div class="w-10 h-10 bg-green-100 group-hover:bg-green-200 rounded-lg flex items-center justify-center transition">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Manage Animals</p>
                            <p class="text-xs text-gray-500">127 total</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
