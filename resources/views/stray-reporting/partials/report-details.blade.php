<!-- Caretaker-Focused Rescue Details -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden border-l-4 border-purple-500">
    {{-- Priority Badge at Top --}}
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @php
                    $priorityColors = [
                        'critical' => 'bg-red-500',
                        'high' => 'bg-orange-500',
                        'normal' => 'bg-blue-500',
                    ];
                    $priorityColor = $priorityColors[$rescue->priority] ?? 'bg-gray-500';
                @endphp
                <span class="{{ $priorityColor }} text-white px-3 py-1 rounded-full text-xs font-bold uppercase">
                    {{ $rescue->priority }} Priority
                </span>
            </div>
            <span class="text-white text-sm font-semibold">Rescue #{{ $rescue->id }}</span>
        </div>
    </div>

    <div class="p-4 md:p-6 space-y-4 md:space-y-6">
        {{-- LOCATION - Most Important Section --}}
        <div class="bg-purple-50 rounded-xl p-4 md:p-5 border-2 border-purple-200">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="text-base md:text-lg font-bold text-gray-900">Rescue Location</h3>
            </div>

            {{-- Big, Clear Address - Responsive Text --}}
            <div class="bg-white rounded-lg p-3 md:p-4 mb-3 shadow-sm">
                <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900 mb-1">{{ $rescue->report->address }}</p>
                <p class="text-sm md:text-base text-gray-600">{{ $rescue->report->city }}, {{ $rescue->report->state }}</p>
            </div>

            {{-- Quick Navigation Buttons - Responsive Layout --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3">
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $rescue->report->latitude }},{{ $rescue->report->longitude }}"
                   target="_blank"
                   class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-semibold transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    Google Maps
                </a>
                <a href="https://waze.com/ul?ll={{ $rescue->report->latitude }},{{ $rescue->report->longitude }}&navigate=yes"
                   target="_blank"
                   class="flex items-center justify-center gap-2 bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-3 rounded-lg font-semibold transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                    </svg>
                    Waze
                </a>
            </div>
        </div>

        {{-- SITUATION DETAILS - Responsive --}}
        @if($rescue->report->description)
        <div class="bg-amber-50 rounded-xl p-4 md:p-5 border-2 border-amber-200">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 md:w-6 md:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-base md:text-lg font-bold text-gray-900">Situation Details</h3>
            </div>
            <p class="text-sm md:text-base text-gray-800 leading-relaxed whitespace-pre-line">{{ $rescue->report->description }}</p>
        </div>
        @endif

        {{-- REPORTER CONTACT - Quick Access - Responsive --}}
        <div class="bg-gray-50 rounded-xl p-4 md:p-5 border-2 border-gray-200">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 md:w-6 md:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <h3 class="text-base md:text-lg font-bold text-gray-900">Reporter Contact</h3>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-sm md:text-base font-semibold text-gray-900">{{ $rescue->report->user->name ?? 'Unknown' }}</p>
                    <p class="text-xs md:text-sm text-gray-600 break-all">{{ $rescue->report->user->email ?? 'No contact info' }}</p>
                </div>
                @if($rescue->report->user)
                <a href="mailto:{{ $rescue->report->user->email }}"
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-md whitespace-nowrap text-center">
                    Contact
                </a>
                @endif
            </div>
        </div>

        {{-- RESCUE INFO - Responsive --}}
        <div class="bg-blue-50 rounded-lg p-3 md:p-4 border border-blue-200">
            <div class="flex items-center justify-between text-xs md:text-sm">
                <span class="text-blue-700 font-medium">Rescue Status:</span>
                <span class="px-3 py-1 rounded-full font-bold
                    @if($rescue->status === 'Scheduled') bg-amber-100 text-amber-800
                    @elseif($rescue->status === 'In Progress') bg-blue-100 text-blue-800
                    @elseif($rescue->status === 'Success') bg-green-100 text-green-800
                    @elseif($rescue->status === 'Failed') bg-red-100 text-red-800
                    @endif">
                    {{ $rescue->status }}
                </span>
            </div>
        </div>
    </div>
</div>
