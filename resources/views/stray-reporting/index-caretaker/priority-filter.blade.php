    <!-- Priority Filter -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Filter by Priority</h3>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('rescues.index', array_filter(['status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition {{ !request('priority') ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                All Priorities
            </a>
            <a href="{{ route('rescues.index', array_filter(['priority' => 'critical', 'status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2 {{ request('priority') == 'critical' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Critical
            </a>
            <a href="{{ route('rescues.index', array_filter(['priority' => 'high', 'status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2 {{ request('priority') == 'high' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                High
            </a>
            <a href="{{ route('rescues.index', array_filter(['priority' => 'normal', 'status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2 {{ request('priority') == 'normal' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Normal
            </a>

            @if(request('priority') || request('status'))
                <a href="{{ route('rescues.index') }}" class="ml-auto px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition">
                    Clear Filters
                </a>
            @endif
        </div>
    </div>
