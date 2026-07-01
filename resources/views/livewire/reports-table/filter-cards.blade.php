    {{-- Status Filter Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
        <!-- Total Reports -->
        <button wire:click="$set('status', '')"
           class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ empty($status) ? 'ring-2 ring-purple-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-xl font-bold text-purple-700 mb-0.5">{{ $totalReports }}</p>
            <p class="text-gray-600 text-xs">Total</p>
        </button>

        <!-- Pending -->
        <button wire:click="$set('status', 'Pending')"
           class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ $status == 'Pending' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xl font-bold text-yellow-600 mb-0.5">{{ $statusCounts['Pending'] ?? 0 }}</p>
            <p class="text-gray-600 text-xs">Pending</p>
        </button>

        <!-- Assigned -->
        <button wire:click="$set('status', 'Assigned')"
           class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ $status == 'Assigned' ? 'ring-2 ring-blue-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <p class="text-xl font-bold text-blue-600 mb-0.5">{{ $statusCounts['Assigned'] ?? 0 }}</p>
            <p class="text-gray-600 text-xs">Assigned</p>
        </button>

        <!-- In Progress -->
        <button wire:click="$set('status', 'In Progress')"
           class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ $status == 'In Progress' ? 'ring-2 ring-purple-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <p class="text-xl font-bold text-purple-600 mb-0.5">{{ $statusCounts['In Progress'] ?? 0 }}</p>
            <p class="text-gray-600 text-xs">In Progress</p>
        </button>

        <!-- Completed -->
        <button wire:click="$set('status', 'Completed')"
           class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ $status == 'Completed' ? 'ring-2 ring-green-500' : '' }}">
            <div class="flex justify-center mb-2">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xl font-bold text-green-600 mb-0.5">{{ $statusCounts['Completed'] ?? 0 }}</p>
            <p class="text-gray-600 text-xs">Completed</p>
        </button>
    </div>
