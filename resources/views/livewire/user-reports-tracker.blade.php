<div wire:poll.15s="checkForStatusChanges"
     id="myReportsModal"
     x-data="{ open: false }"
     x-show="open"
     x-cloak
     @open-my-reports-modal.window="open = true; document.body.style.overflow = 'hidden'"
     @close-my-reports-modal.window="open = false; document.body.style.overflow = 'auto'"
     @click.self="open = false; document.body.style.overflow = 'auto'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-[1400px] max-w-full max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 flex-shrink-0 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <h2 class="text-2xl font-bold">My Reports</h2>
                        <p class="text-purple-100 text-sm">View all your submitted reports (Live status updates)</p>
                    </div>
                </div>
                <button @click="open = false; document.body.style.overflow = 'auto'" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Status Change Notification Banner -->
        @if($hasStatusChanges)
            <div class="bg-gradient-to-r from-green-50 to-green-100 border-b-2 border-green-300 p-4 animate-pulse">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-green-900">Report Status Updated!</h3>
                            <div class="text-xs text-green-700 mt-1 space-y-1">
                                @foreach($statusChanges as $change)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">Report #{{ $change['report_id'] }}:</span>
                                        <span class="px-2 py-0.5 bg-white rounded text-gray-700">{{ $change['old_status'] }}</span>
                                        <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="px-2 py-0.5 bg-green-600 text-white rounded font-semibold">{{ $change['new_status'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <button wire:click="acknowledgeChanges"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition duration-300 shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Got it!
                    </button>
                </div>
            </div>
        @endif

        <!-- Loading Indicator -->
        <div wire:loading class="bg-purple-50 border-b border-purple-200 p-3">
            <div class="flex items-center gap-2 justify-center">
                <svg class="animate-spin h-4 w-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-xs font-medium text-purple-700">Checking for updates...</p>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            @if($userReports->isEmpty())
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🐾</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No reports yet</h3>
                    <p class="text-gray-600 mb-6">You haven't submitted any reports</p>
                    <button @click="open = false; document.body.style.overflow = 'auto'" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        Close
                    </button>
                </div>
            @else
                <!-- Table View -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                    Report ID
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                    Location
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                    Images
                                </th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($userReports as $report)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <!-- Report ID -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">📍</span>
                                            <span class="text-sm font-bold text-purple-700">#{{ $report->id }}</span>
                                        </div>
                                    </td>

                                    <!-- Status with animation for changes -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border
                                            @if($report->report_status == 'Pending') bg-yellow-100 text-yellow-800 border-yellow-300
                                            @elseif($report->report_status == 'Assigned') bg-blue-100 text-blue-800 border-blue-300
                                            @elseif($report->report_status == 'In Progress') bg-purple-100 text-purple-800 border-purple-300
                                            @elseif($report->report_status == 'Completed') bg-green-100 text-green-800 border-green-300
                                            @elseif($report->report_status == 'Rejected') bg-red-100 text-red-800 border-red-300
                                            @endif
                                            @if(collect($statusChanges)->pluck('report_id')->contains($report->id)) animate-pulse ring-2 ring-green-400 @endif">
                                            {{ $report->report_status }}
                                        </span>
                                    </td>

                                    <!-- Date & Time -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">
                                            {{ $report->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $report->created_at->format('h:i A') }}
                                        </div>
                                    </td>

                                    <!-- Location -->
                                    <td class="px-4 py-4 max-w-xs">
                                        <div class="text-sm text-gray-900 font-medium truncate" title="{{ $report->address }}">
                                            {{ $report->address }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $report->city }}, {{ $report->state }}
                                        </div>
                                    </td>

                                    <!-- Description -->
                                    <td class="px-4 py-4 max-w-xs">
                                        @if($report->description)
                                            <div class="text-sm text-gray-700 truncate" title="{{ $report->description }}">
                                                {{ Str::limit($report->description, 60) }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400 italic">No description</span>
                                        @endif
                                    </td>

                                    <!-- Images -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($report->images->count() > 0)
                                            <div class="flex items-center gap-2">
                                                <div class="flex -space-x-2">
                                                    @foreach($report->images->take(3) as $image)
                                                        <img src="{{ $image->url }}"
                                                             alt="Report Image"
                                                             class="w-8 h-8 rounded-full object-cover border-2 border-white cursor-pointer hover:scale-110 transition-transform shadow-sm"
                                                             onclick="openImageModal('{{ $image->url }}')">
                                                    @endforeach
                                                </div>
                                                @if($report->images->count() > 3)
                                                    <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded-full">
                                                        +{{ $report->images->count() - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">No images</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <button type="button"
                                                onclick="openReportDetailModal({{ $report->id }})"
                                                class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination in Modal -->
                @if($userReports->hasPages())
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        {{ $userReports->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Store report data in JavaScript for detail modal --}}
    <script>
        window.reportsData = @json($userReports->items());
    </script>
</div>
