    {{-- Search and Filter Form --}}
    <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Search & Filter Reports</h3>
        </div>

        <div class="flex gap-3 items-end">
            <!-- User Search -->
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Reporter Name or Email</label>
                <div class="relative">
                    <input type="text"
                           wire:model.live.debounce.500ms="userSearch"
                           placeholder="Search by reporter name or email..."
                           class="w-full px-3 py-2 pl-9 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>

            <button wire:click="clearFilters"
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition duration-300 flex items-center gap-1.5 shadow-md">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear
            </button>

            @if(!empty($userSearch))
                <div class="flex items-center text-xs text-purple-600 font-medium">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <span>1 filter active</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Loading Indicator --}}
    <div wire:loading class="mb-4 flex items-center gap-2 p-3 bg-purple-50 border-l-4 border-purple-400 rounded-lg">
        <svg class="animate-spin h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-sm font-medium text-purple-700">Loading reports...</p>
    </div>

    {{-- Reports Table or Empty State --}}
    @if($reports->isEmpty())
        <div class="bg-white rounded shadow p-6 text-center">
            <p class="text-sm text-gray-600">No stray animal reports have been submitted.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Report ID</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Date & Time</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reports as $report)
                        @php
                            $isNewReport = in_array($report->id, $newReportIds);
                        @endphp
                        <tr class="hover:bg-gray-50 transition-all duration-300 {{ $isNewReport ? 'bg-green-50 animate-highlight-fade' : '' }}"
                            data-report-id="{{ $report->id }}">
                            <!-- Report ID -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @if($isNewReport)
                                        <span class="relative flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                        </span>
                                    @endif
                                    <svg class="w-5 h-5 {{ $isNewReport ? 'text-green-600' : 'text-purple-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm font-bold {{ $isNewReport ? 'text-green-700' : 'text-purple-700' }}">#{{ $report->id }}</span>
                                    @if($isNewReport)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold text-green-800 bg-green-200 animate-pulse">NEW</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border
                                    @if($report->report_status == 'Pending') bg-yellow-100 text-yellow-800 border-yellow-300
                                    @elseif($report->report_status == 'Assigned') bg-blue-100 text-blue-800 border-blue-300
                                    @elseif($report->report_status == 'In Progress') bg-purple-100 text-purple-800 border-purple-300
                                    @elseif($report->report_status == 'Completed') bg-green-100 text-green-800 border-green-300
                                    @elseif($report->report_status == 'Rejected') bg-red-100 text-red-800 border-red-300
                                    @endif">
                                    {{ $report->report_status }}
                                </span>
                            </td>

                            <!-- Date & Time -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium">{{ $report->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $report->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Location -->
                            <td class="px-4 py-4 max-w-xs">
                                <div class="text-sm text-gray-900 font-medium truncate" title="{{ $report->address }}">{{ $report->address }}</div>
                                <div class="text-xs text-gray-500">{{ $report->city }}, {{ $report->state }}</div>
                            </td>

                            <!-- Description -->
                            <td class="px-4 py-4 max-w-xs">
                                @if($report->description)
                                    <div class="text-sm text-gray-700 truncate" title="{{ $report->description }}">{{ Str::limit($report->description, 60) }}</div>
                                @else
                                    <span class="text-sm text-gray-400 italic">No description</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('reports.show', $report->id) }}"
                                   class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    @endif
