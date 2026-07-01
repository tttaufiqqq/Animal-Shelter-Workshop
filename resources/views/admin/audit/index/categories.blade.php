
    <!-- Audit Categories -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Audit Categories</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Authentication Logs -->
            <a href="{{ route('admin.audit.authentication') }}"
               class="bg-white border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-lg transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-indigo-100 p-3 rounded-lg group-hover:bg-indigo-200 transition-colors">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-indigo-600">{{ number_format($stats['authentication']) }}</span>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Authentication</h4>
                <p class="text-sm text-gray-600 mb-3">Login & logout tracking, session management</p>
                <div class="flex items-center text-indigo-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                    View logs
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- Payment & Adoption -->
            <a href="{{ route('admin.audit.payments') }}"
               class="bg-white border border-gray-200 rounded-lg p-6 hover:border-green-300 hover:shadow-lg transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-green-100 p-3 rounded-lg group-hover:bg-green-200 transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-green-600">{{ number_format($stats['payment']) }}</span>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Payment & Adoption</h4>
                <p class="text-sm text-gray-600 mb-3">Transaction records, adoption payments</p>
                <div class="flex items-center text-green-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                    View logs
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- Animal Welfare -->
            <a href="{{ route('admin.audit.animals') }}"
               class="bg-white border border-gray-200 rounded-lg p-6 hover:border-purple-300 hover:shadow-lg transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-purple-100 p-3 rounded-lg group-hover:bg-purple-200 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-purple-600">{{ number_format($stats['animal']) }}</span>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Animal Welfare</h4>
                <p class="text-sm text-gray-600 mb-3">Medical records, vaccination tracking</p>
                <div class="flex items-center text-purple-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                    View logs
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- Rescue Operations -->
            <a href="{{ route('admin.audit.rescues') }}"
               class="bg-white border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-lg transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-lg group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($stats['rescue']) }}</span>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Rescue Operations</h4>
                <p class="text-sm text-gray-600 mb-3">Caretaker assignments, rescue tracking</p>
                <div class="flex items-center text-blue-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                    View logs
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>
    </div>
