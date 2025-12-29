@auth
    @if(Auth::user()->hasRole('admin'))
        {{-- Admin View with Admin Layout --}}
        <x-admin-layout>
            <x-slot name="title">Clinics & Vets</x-slot>

            @push('styles')
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            @endpush

            {{-- Database Warning Banner --}}
            @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
                <div class="mb-4 flex items-center gap-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-yellow-800">Limited Connectivity - {{ count($dbDisconnected) }} database(s) unavailable. Some features may not work.</p>
                    </div>
                </div>
            @endif

            {{-- Page Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Clinics & Veterinarians Management</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage medical partners and veterinary professionals</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded">
                        <span class="text-xs text-gray-600 font-medium">Clinics:</span>
                        <span class="text-sm font-bold text-blue-700 ml-1">{{ $clinics->count() }}</span>
                    </div>
                    <div class="px-4 py-2 bg-green-50 border border-green-200 rounded">
                        <span class="text-xs text-gray-600 font-medium">Vets:</span>
                        <span class="text-sm font-bold text-green-700 ml-1">{{ $vets->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="space-y-6">
                @include('animal-management.partials.cv-content', ['clinics' => $clinics, 'vets' => $vets])
            </div>

            @push('scripts')
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                @include('animal-management.partials.cv-scripts')
            @endpush
        </x-admin-layout>
    @else
        {{-- Non-Admin View with Standalone Layout --}}
        @include('animal-management.main-manage-cv-standalone', ['clinics' => $clinics, 'vets' => $vets])
    @endif
@else
    {{-- Guest View - Redirect to login --}}
    <script>window.location.href = "{{ route('login') }}";</script>
@endauth
