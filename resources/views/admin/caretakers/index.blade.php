@php
$breadcrumbs = [
    ['label' => 'Caretakers', 'icon' => '<svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>']
];
@endphp

<x-admin-layout title="Caretakers Management" :breadcrumbs="$breadcrumbs">

    <!-- Page Header with Add Button -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <svg class="w-7 h-7 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Caretakers Management
            </h1>
            <p class="text-gray-600 mt-1">Manage caretaker accounts and permissions</p>
        </div>
        <button onclick="openCaretakerModal()"
                class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add New Caretaker
        </button>
    </div>

    <!-- Success/Error Messages -->
    @if (session('caretaker_success'))
        <div class="mb-6 flex items-start gap-3 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg shadow-sm">
            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-green-800">{{ session('caretaker_success') }}</p>
            </div>
        </div>
    @endif

    @if (session('caretaker_error'))
        <div class="mb-6 flex items-start gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-red-800">{{ session('caretaker_error') }}</p>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <!-- Total Caretakers -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Caretakers</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $caretakers->total() }}</p>
                    <p class="text-xs text-gray-500 mt-2">Active accounts</p>
                </div>
                <div class="bg-teal-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- New This Month -->
        <div class="bg-white border border-blue-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-1">New This Month</p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ $caretakers->filter(fn($c) => $c->created_at->isCurrentMonth())->count() }}
                    </p>
                    <p class="text-xs text-blue-600 mt-2">Recently added</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Today -->
        <div class="bg-white border border-green-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-1">Showing</p>
                    <p class="text-3xl font-bold text-green-600">{{ $caretakers->count() }}</p>
                    <p class="text-xs text-green-600 mt-2">On current page</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Caretakers Table -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">All Caretakers</h3>
                    <p class="text-sm text-gray-600 mt-1">Manage caretaker accounts and view their information</p>
                </div>
                <span class="text-sm text-gray-600">Total: {{ $caretakers->total() }} accounts</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Caretaker
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Contact
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Location
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Joined
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($caretakers as $caretaker)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-teal-100 rounded-full flex items-center justify-center">
                                        <span class="text-teal-700 font-semibold text-sm">
                                            {{ substr($caretaker->name, 0, 2) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $caretaker->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $caretaker->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $caretaker->phoneNum }}</div>
                                <div class="text-xs text-gray-500">Phone</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $caretaker->city }}, {{ $caretaker->state }}</div>
                                <div class="text-xs text-gray-500">{{ $caretaker->address }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $caretaker->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $caretaker->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button class="text-teal-600 hover:text-teal-900 font-medium">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No caretakers found.</p>
                                <p class="text-xs text-gray-400 mt-1">Click "Add New Caretaker" to create the first account</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($caretakers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $caretakers->links() }}
        </div>
        @endif
    </div>

    <!-- Include the Caretaker Modal -->
    <x-modals.add-caretaker />

    <!-- Scripts -->
    @push('scripts')
    <script>
        function openCaretakerModal() {
            const modal = document.getElementById('caretakerModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCaretakerModal() {
            const modal = document.getElementById('caretakerModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Auto-open modal if there are validation errors
        @if($errors->caretaker->any())
            document.addEventListener('DOMContentLoaded', function() {
                openCaretakerModal();
            });
        @endif

        // Auto-close modal on success
        @if(session('caretaker_success'))
            document.addEventListener('DOMContentLoaded', function() {
                closeCaretakerModal();
            });
        @endif
    </script>
    @endpush
</x-admin-layout>
