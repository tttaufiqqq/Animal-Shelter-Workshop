@auth
    @if(Auth::user()->hasRole('admin'))
        {{-- Admin View with Admin Layout --}}
        <x-admin-layout>
            <x-slot name="title">All Bookings</x-slot>


            {{-- Database Warning Banner --}}
            @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
                <div class="mb-4 flex items-center gap-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                        <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($dbDisconnected as $connection => $info)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $info['module'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Page Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Bookings Management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage all appointment bookings and adoptions</p>
            </div>

            {{-- Main Content --}}
            <div class="space-y-6">
                @include('booking-adoption.partials.booking-content', [
                    'bookings' => $bookings,
                    'totalBookings' => $totalBookings,
                    'statusCounts' => $statusCounts
                ])
            </div>

            @push('scripts')
                @include('booking-adoption.partials.booking-scripts')
            @endpush
        </x-admin-layout>
    @else
        {{-- Non-Admin View with Standalone Layout --}}
        @include('booking-adoption.admin-standalone', [
            'bookings' => $bookings,
            'totalBookings' => $totalBookings,
            'statusCounts' => $statusCounts
        ])
    @endif
@else
    {{-- Guest View - Redirect to login --}}
    <script>window.location.href = "{{ route('login') }}";</script>
@endauth
