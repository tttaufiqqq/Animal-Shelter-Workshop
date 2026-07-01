<div class="mb-6 bg-purple-600 shadow p-6">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">My Assigned Rescues</h1>
        <p class="text-purple-100 text-sm mt-1">Manage your assigned animal rescue missions</p>
    </div>
</div>

<div class="w-full mt-10 p-4 md:p-6 pb-10">
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border-l-4 border-green-500 rounded">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Status Filter Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <!-- All Rescues -->
        <a href="{{ route('rescues.index') }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ !request('status') && !request('priority') ? 'ring-2 ring-purple-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-purple-700 mb-1">{{ $statusCounts->sum() }}</p>
            <p class="text-gray-600 text-sm">All Rescues</p>
        </a>

        <!-- Scheduled -->
        <a href="{{ route('rescues.index', ['status' => 'Scheduled']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Scheduled' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-yellow-600 mb-1">{{ $statusCounts['Scheduled'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Scheduled</p>
        </a>

        <!-- In Progress -->
        <a href="{{ route('rescues.index', ['status' => 'In Progress']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'In Progress' ? 'ring-2 ring-blue-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-blue-600 mb-1">{{ $statusCounts['In Progress'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">In Progress</p>
        </a>

        <!-- Success -->
        <a href="{{ route('rescues.index', ['status' => 'Success']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Success' ? 'ring-2 ring-green-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-green-600 mb-1">{{ $statusCounts['Success'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Success</p>
        </a>
    </div>
