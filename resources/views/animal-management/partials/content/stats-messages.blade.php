{{-- Shared Content for Animal Management Page --}}

{{-- Stats Cards (Admin Only) --}}
@if(Auth::check() && Auth::user()->hasRole('admin'))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {{-- Total Animals --}}
        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 text-center">
            <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3">
                <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="7" cy="5" r="1.5" stroke-width="2"/>
                    <circle cx="17" cy="5" r="1.5" stroke-width="2"/>
                    <circle cx="5" cy="11" r="1.5" stroke-width="2"/>
                    <circle cx="19" cy="11" r="1.5" stroke-width="2"/>
                    <ellipse cx="12" cy="16" rx="4" ry="5" stroke-width="2"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-purple-700 mb-1">{{ $animals->total() }}</p>
            <p class="text-gray-600 text-sm font-medium">Total Animals</p>
        </div>

        {{-- Available for Adoption --}}
        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 text-center">
            <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-green-700 mb-1">
                {{ $animals->where('adoption_status', 'Not Adopted')->count() }}
            </p>
            <p class="text-gray-600 text-sm font-medium">Available</p>
        </div>

        {{-- Adopted --}}
        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 text-center">
            <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-blue-700 mb-1">
                {{ $animals->where('adoption_status', 'Adopted')->count() }}
            </p>
            <p class="text-gray-600 text-sm font-medium">Adopted</p>
        </div>

        {{-- Health Status --}}
        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 text-center">
            <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-red-700 mb-1">
                {{ $animals->whereIn('health_details', ['Sick', 'Need Observation'])->count() }}
            </p>
            <p class="text-gray-600 text-sm font-medium">Need Care</p>
        </div>
    </div>
@endif

{{-- Success/Error Messages --}}
@if (session('success'))
    <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
        <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <p class="font-semibold text-green-700">{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <p class="font-semibold text-red-700">{{ session('error') }}</p>
    </div>
@endif

{{-- Caretaker Toggle (Only visible for caretakers) --}}
@role('caretaker')
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4 border border-gray-200">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="p-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Caretaker View</h3>
                    <p class="text-xs text-gray-600">Choose which animals to display</p>
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <a href="{{ route('animal-management.index', array_merge(request()->except('rescued_by_me'), ['rescued_by_me' => 'true'])) }}"
                   class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-medium text-center {{ request('rescued_by_me') === 'true' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-user-check mr-1 text-xs"></i>
                    My Rescues
                </a>
                <a href="{{ route('animal-management.index', request()->except('rescued_by_me')) }}"
                   class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-medium text-center {{ request('rescued_by_me') !== 'true' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-paw mr-1 text-xs"></i>
                    All Animals
                </a>
            </div>
        </div>

        @if(request('rescued_by_me') === 'true')
            <div class="mt-2 bg-blue-50 rounded p-2 flex items-center gap-2 text-xs text-blue-800 border border-blue-200">
                <i class="fas fa-info-circle text-xs"></i>
                <span>Showing only animals from rescues you've been assigned to</span>
            </div>
        @endif
    </div>
@endrole

{{-- Filters Component --}}
@include('animal-management.partials.filters', ['animals' => $animals])
