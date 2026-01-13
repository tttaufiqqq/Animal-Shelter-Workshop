{{-- Filters and Search Component --}}
<div class="bg-white rounded-lg shadow-sm p-4 md:p-5 mb-6 border border-gray-200">
    <div class="flex items-center gap-2 mb-4">
        <div class="bg-purple-600 p-2 rounded">
            <i class="fas fa-filter text-white text-base"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">Filter Animals</h2>
            <p class="text-xs text-gray-600">Search and filter animals</p>
        </div>
    </div>

    <form method="GET" action="{{ route('animal-management.index') }}" class="space-y-4">
        <!-- Hidden field to preserve rescued_by_me filter -->
        @if(request('rescued_by_me'))
            <input type="hidden" name="rescued_by_me" value="{{ request('rescued_by_me') }}">
        @endif

        {{-- Filter Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 {{ Auth::check() && Auth::user()->hasRole('admin') ? 'xl:grid-cols-5' : 'xl:grid-cols-4' }} gap-3">
            <!-- Search -->
            <div class="xl:col-span-2">
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-search text-purple-600 text-xs"></i>
                    Search by Name
                </label>
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Type animal name..."
                           class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                    <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            <!-- Species -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-dog text-purple-600 text-xs"></i>
                    Species
                </label>
                <select name="species"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 appearance-none bg-white cursor-pointer">
                    <option value="">All Species</option>
                    <option value="Dog" {{ request('species') == 'Dog' ? 'selected' : '' }}>Dog</option>
                    <option value="Cat" {{ request('species') == 'Cat' ? 'selected' : '' }}>Cat</option>
                </select>
            </div>

            <!-- Health Condition -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-heartbeat text-purple-600 text-xs"></i>
                    Health
                </label>
                <select name="health_details"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 appearance-none bg-white cursor-pointer">
                    <option value="">All Conditions</option>
                    <option value="Healthy" {{ request('health_details') == 'Healthy' ? 'selected' : '' }}>Healthy</option>
                    <option value="Sick" {{ request('health_details') == 'Sick' ? 'selected' : '' }}>Sick</option>
                    <option value="Need Observation" {{ request('health_details') == 'Need Observation' ? 'selected' : '' }}>Need Observation</option>
                </select>
            </div>

            <!-- Adoption Status (Admin Only) -->
            @if(Auth::check() && Auth::user()->hasRole('admin'))
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-home text-purple-600 text-xs"></i>
                    Adoption Availability
                </label>
                <select name="adoption_status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 appearance-none bg-white cursor-pointer">
                    <option value="">All Status</option>
                    <option value="Not Adopted" {{ request('adoption_status') == 'Not Adopted' ? 'selected' : '' }}>Available for Adoption</option>
                    <option value="Adopted" {{ request('adoption_status') == 'Adopted' ? 'selected' : '' }}>Adopted</option>
                </select>
            </div>
            @endif

            <!-- Gender -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-venus-mars text-purple-600 text-xs"></i>
                    Gender
                </label>
                <select name="gender"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 appearance-none bg-white cursor-pointer">
                    <option value="">All Genders</option>
                    <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pt-3 border-t border-gray-200">
            <div class="flex flex-wrap gap-2">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 text-sm rounded-lg font-medium flex items-center gap-1.5">
                    <i class="fas fa-search text-xs"></i>
                    Apply Filters
                </button>
                <a href="{{ route('animal-management.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 text-sm rounded-lg font-medium flex items-center gap-1.5">
                    <i class="fas fa-redo text-xs"></i>
                    Clear All
                </a>
            </div>
            <div class="flex items-center gap-1.5 bg-gray-100 px-3 py-1.5 rounded border border-gray-300">
                <i class="fas fa-paw text-purple-600 text-xs"></i>
                <p class="text-gray-700 text-xs">
                    Showing <span class="font-bold text-gray-900">{{ $animals->total() }}</span> animal{{ $animals->total() != 1 ? 's' : '' }}
                </p>
            </div>
        </div>
    </form>
</div>
