{{-- dashboard-example Orchestrator --}}
{{--
    EXAMPLE ADMIN DASHBOARD PAGE
    This demonstrates how to use the new admin layout system

    To use this page, add this route to web.php:
    Route::get('/admin/dashboard-example', function () {
        return view('admin.dashboard-example');
    })->middleware(['auth', 'role:admin'])->name('admin.dashboard.example');
--}}

<x-admin-layout>
    {{-- Page Title (for browser tab and mobile header) --}}
    <x-slot name="title">Dashboard Overview</x-slot>

    {{-- Optional: Custom Header Section --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
                <p class="text-gray-600 mt-1">Welcome back! Here's what's happening with your shelter today.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Report
                </button>
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Report
                </a>
            </div>
        </div>
    </x-slot>

    @include('admin.dashboard-example.stats-cards')
    @include('admin.dashboard-example.recent-quick')
</x-admin-layout>
