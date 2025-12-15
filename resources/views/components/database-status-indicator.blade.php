{{-- Database Connection Status Indicator --}}
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
<div class="fixed bottom-4 right-4 z-50">
    <!-- Collapsed Status Badge -->
    <div id="dbStatusBadge"
         class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow-lg cursor-pointer flex items-center gap-2 transition-all"
         onclick="toggleDbStatus()">
        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span class="font-medium">{{ count($dbDisconnected) }} DB Offline</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>

    <!-- Expanded Status Panel -->
    <div id="dbStatusPanel"
         class="hidden absolute bottom-14 right-0 bg-white rounded-lg shadow-2xl border border-gray-200 w-96 max-h-96 overflow-y-auto">

        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4 rounded-t-lg flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
                <h3 class="font-bold">Database Status</h3>
            </div>
            <button onclick="toggleDbStatus()" class="text-white hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Connection List -->
        <div class="p-4 space-y-3">
            @foreach($dbConnectionStatus as $connection => $info)
            <div class="flex items-start gap-3 p-3 rounded-lg {{ $info['connected'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <!-- Status Icon -->
                <div class="flex-shrink-0 mt-0.5">
                    @if($info['connected'])
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>

                <!-- Connection Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-sm {{ $info['connected'] ? 'text-green-900' : 'text-red-900' }}">
                            {{ ucfirst($connection) }}
                        </h4>
                        <span class="text-xs px-2 py-1 rounded {{ $info['connected'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $info['connected'] ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <p class="text-xs {{ $info['connected'] ? 'text-green-700' : 'text-red-700' }} mt-1">
                        {{ $info['module'] }}
                    </p>
                    <p class="text-xs {{ $info['connected'] ? 'text-green-600' : 'text-red-600' }} mt-0.5">
                        {{ $info['name'] }} • Port {{ $info['port'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 p-4 rounded-b-lg border-t">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">
                    <strong>{{ count($dbConnected) }}/{{ count($dbConnectionStatus) }}</strong> databases online
                </span>
                <button onclick="refreshDbStatus()"
                        class="flex items-center gap-1 text-purple-600 hover:text-purple-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>

            @if(count($dbDisconnected) > 0)
            <p class="text-xs text-yellow-700 mt-2 flex items-start gap-1">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <span>Some features may be unavailable. Check SSH connections to remote databases.</span>
            </p>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleDbStatus() {
        const panel = document.getElementById('dbStatusPanel');
        const badge = document.getElementById('dbStatusBadge');

        panel.classList.toggle('hidden');

        // Rotate arrow icon
        const arrow = badge.querySelector('svg:last-child');
        if (!panel.classList.contains('hidden')) {
            arrow.style.transform = 'rotate(180deg)';
        } else {
            arrow.style.transform = 'rotate(0deg)';
        }
    }

    function refreshDbStatus() {
        // Show loading state
        const refreshBtn = event.target.closest('button');
        const originalHtml = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Refreshing...';
        refreshBtn.disabled = true;

        // Reload the page to get fresh connection status
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    // Close panel when clicking outside
    document.addEventListener('click', function(event) {
        const panel = document.getElementById('dbStatusPanel');
        const badge = document.getElementById('dbStatusBadge');

        if (!panel.contains(event.target) && !badge.contains(event.target)) {
            panel.classList.add('hidden');
            const arrow = badge.querySelector('svg:last-child');
            arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>
@endif
