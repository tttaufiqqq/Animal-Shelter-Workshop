    @if($rescues->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center">
            <p class="text-gray-600">No rescues assigned yet.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Rescue #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Report #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">City/State</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Assigned On</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rescues as $rescue)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">RES {{ $rescue->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($rescue->priority == 'critical')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded bg-red-100 text-red-800">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Critical
                                    </span>
                                @elseif($rescue->priority == 'high')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded bg-orange-100 text-orange-800">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        High
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Normal
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">REP {{ $rescue->report->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($rescue->status == 'Scheduled') bg-yellow-100 text-yellow-800
                                    @elseif($rescue->status == 'In Progress') bg-blue-100 text-blue-800
                                    @elseif($rescue->status == 'Success') bg-green-100 text-green-800
                                    @elseif($rescue->status == 'Failed') bg-red-100 text-red-800
                                    @endif">
                                    {{ $rescue->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $rescue->report->address }}">{{ $rescue->report->address }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $rescue->report->city }}, {{ $rescue->report->state }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $rescue->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <a href="{{ route('rescues.show', $rescue->id) }}"
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
            {{ $rescues->links() }}
        </div>
    @endif
</div>
