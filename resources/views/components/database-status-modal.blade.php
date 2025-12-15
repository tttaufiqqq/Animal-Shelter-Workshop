{{-- Database Connection Status Modal - Welcome Page Only --}}
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
<div id="dbStatusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <h2 class="text-2xl font-bold">Database Connection Notice</h2>
                        <p class="text-sm text-yellow-100 mt-1">Some databases are currently offline</p>
                    </div>
                </div>
                <button onclick="closeDbModal()" class="text-white hover:text-yellow-200 transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 max-h-[60vh] overflow-y-auto">
            <!-- Summary Banner -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold text-yellow-800">
                            {{ count($dbConnected) }}/{{ count($dbConnectionStatus) }} databases are online
                        </p>
                        <p class="text-sm text-yellow-700 mt-1">
                            The application will run with limited functionality. Some features may be unavailable or display incomplete data.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Database Status Grid -->
            <div class="space-y-3">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Database Status:</h3>

                @foreach($dbConnectionStatus as $connection => $info)
                <div class="flex items-center gap-4 p-4 rounded-lg border {{ $info['connected'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                    <!-- Status Icon -->
                    <div class="flex-shrink-0">
                        @if($info['connected'])
                            <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        @else
                            <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Connection Info -->
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-bold text-lg {{ $info['connected'] ? 'text-green-900' : 'text-red-900' }}">
                                {{ ucfirst($connection) }}
                            </h4>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $info['connected'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $info['connected'] ? 'ONLINE' : 'OFFLINE' }}
                            </span>
                        </div>
                        <p class="text-sm font-medium {{ $info['connected'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $info['module'] }}
                        </p>
                        <p class="text-xs {{ $info['connected'] ? 'text-green-600' : 'text-red-600' }} mt-1">
                            {{ $info['name'] }} • Port {{ $info['port'] }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Help Section -->
            @if(count($dbDisconnected) > 0)
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    Developer Tips
                </h4>
                <ul class="text-sm text-blue-800 space-y-1 ml-7">
                    <li>• Ensure SSH tunnels are active to remote databases</li>
                    <li>• Verify database services are running on remote machines</li>
                    <li>• Check firewall rules allow connections on specified ports</li>
                    <li>• Use <code class="bg-blue-100 px-2 py-0.5 rounded">php artisan db:check-connections</code> to diagnose</li>
                </ul>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t flex items-center justify-between">
            <p class="text-sm text-gray-600">
                You can continue using the application with limited functionality.
            </p>
            <button onclick="closeDbModal()"
                    class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition shadow-lg">
                Continue Anyway
            </button>
        </div>
    </div>
</div>

<script>
    function closeDbModal() {
        document.getElementById('dbStatusModal').classList.add('hidden');
        // Store in session that user has seen the modal
        sessionStorage.setItem('db_modal_dismissed', 'true');
    }

    // Auto-show modal only if not dismissed in this session
    window.addEventListener('DOMContentLoaded', function() {
        if (sessionStorage.getItem('db_modal_dismissed') !== 'true') {
            document.getElementById('dbStatusModal').classList.remove('hidden');
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDbModal();
        }
    });
</script>
@endif
