<!-- My Reports Modal -->
<div id="myReportsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-[1400px] max-w-full max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 flex-shrink-0 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <h2 class="text-2xl font-bold">My Reports</h2>
                        <p class="text-purple-100 text-sm">View all your submitted reports</p>
                    </div>
                </div>
                <button onclick="closeMyReportsModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            @if($userReports->isEmpty())
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🐾</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No reports yet</h3>
                    <p class="text-gray-600 mb-6">You haven't submitted any reports</p>
                    <button onclick="closeMyReportsModal()" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
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

                                    <!-- Status -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border
                                                @if($report->report_status == 'Pending') bg-yellow-100 text-yellow-800 border-yellow-300
                                                @elseif($report->report_status == 'In Progress') bg-blue-100 text-blue-800 border-blue-300
                                                @elseif($report->report_status == 'Resolved') bg-green-100 text-green-800 border-green-300
                                                @endif">
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
                        {{ $userReports->appends(['open_modal' => 1])->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Report Detail Modal (Expanded View) -->
<div id="reportDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-[1000px] max-w-full max-h-[90vh] overflow-y-auto">
        <!-- Detail Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 sticky top-0 z-10 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📄</span>
                    <div>
                        <h2 class="text-2xl font-bold" id="detailReportTitle">Report Details</h2>
                        <p class="text-purple-100 text-sm">Complete information about this report</p>
                    </div>
                </div>
                <button onclick="closeReportDetailModal()" class="text-white hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Detail Body -->
        <div class="p-6" id="reportDetailContent">
            <!-- Content will be populated dynamically -->
        </div>
    </div>
</div>

<!-- Image Modal (Full Size Preview) -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-[70] flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-6xl max-h-full">
        <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-screen rounded-xl shadow-2xl">
    </div>
</div>

<script>
    let miniMaps = {};
    let detailMap = null;

    // Store report data for detail view
    const reportsData = @json($userReports->items());

    // Open Report Detail Modal
    function openReportDetailModal(reportId) {
        const report = reportsData.find(r => r.id === reportId);
        if (!report) return;

        // Update title
        document.getElementById('detailReportTitle').textContent = `Report #${report.id}`;

        // Build detail content
        const statusClass =
            report.report_status === 'Pending' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' :
                report.report_status === 'In Progress' ? 'bg-blue-100 text-blue-800 border-blue-300' :
                    'bg-green-100 text-green-800 border-green-300';

        let imagesHtml = '';
        if (report.images && report.images.length > 0) {
            imagesHtml = `
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-images text-purple-600 mr-2"></i>
                        Images (${report.images.length})
                    </h4>
                    <div class="grid grid-cols-4 gap-3">
                        ${report.images.map(img => `
                            <img src="${img.url}"
                                 alt="Report Image"
                                 class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-75 transition shadow-md"
                                 onclick="openImageModal('${img.url}')">
                        `).join('')}
                    </div>
                </div>
            `;
        }

        const content = `
            <div class="space-y-6">
                <!-- Status and Date -->
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border ${statusClass}">
                        ${report.report_status}
                    </span>
                    <span class="text-sm text-gray-500">
                        ${new Date(report.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })} -
                        ${new Date(report.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                    </span>
                </div>

                <!-- Location Info -->
                <div class="bg-purple-50 rounded-lg p-5">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center text-lg">
                        <i class="fas fa-map-marker-alt text-purple-600 mr-2"></i>
                        Location Details
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-600 font-medium">Address:</span>
                            <p class="text-gray-900 font-medium">${report.address}</p>
                        </div>
                        <div class="flex gap-6">
                            <div>
                                <span class="text-sm text-gray-600 font-medium">City:</span>
                                <p class="text-gray-900 font-medium">${report.city}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600 font-medium">State:</span>
                                <p class="text-gray-900 font-medium">${report.state}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-map text-purple-600 mr-2"></i>
                        Map Location
                    </h4>
                    <div id="detail-map"
                         class="rounded-lg border-2 border-gray-200 shadow-md"
                         style="height: 300px;"
                         data-lat="${report.latitude}"
                         data-lng="${report.longitude}"></div>
                </div>

                <!-- Description -->
                ${report.description ? `
                    <div class="bg-gray-50 rounded-lg p-5">
                        <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-comment-alt text-purple-600 mr-2"></i>
                            Description
                        </h4>
                        <p class="text-gray-700 leading-relaxed">${report.description}</p>
                    </div>
                ` : ''}

                ${imagesHtml}
            </div>
        `;

        document.getElementById('reportDetailContent').innerHTML = content;
        document.getElementById('reportDetailModal').classList.remove('hidden');

        // Initialize detail map
        setTimeout(() => {
            if (detailMap) {
                detailMap.remove();
            }

            const mapContainer = document.getElementById('detail-map');
            const lat = parseFloat(mapContainer.dataset.lat);
            const lng = parseFloat(mapContainer.dataset.lng);

            detailMap = L.map('detail-map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(detailMap);

            L.marker([lat, lng]).addTo(detailMap);

            setTimeout(() => {
                detailMap.invalidateSize();
            }, 100);
        }, 100);
    }

    // Close Report Detail Modal
    function closeReportDetailModal() {
        document.getElementById('reportDetailModal').classList.add('hidden');

        if (detailMap) {
            detailMap.remove();
            detailMap = null;
        }
    }

    // Open My Reports Modal
    function openMyReportsModal() {
        const modal = document.getElementById('myReportsModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close My Reports Modal
    function closeMyReportsModal() {
        document.getElementById('myReportsModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Image modal functions
    function openImageModal(imageSrc) {
        event.stopPropagation();
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    // Close modals when clicking outside
    document.getElementById('myReportsModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMyReportsModal();
        }
    });

    document.getElementById('reportDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeReportDetailModal();
        }
    });

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const imageModal = document.getElementById('imageModal');
            const detailModal = document.getElementById('reportDetailModal');
            const reportsModal = document.getElementById('myReportsModal');

            if (!imageModal.classList.contains('hidden')) {
                closeImageModal();
            } else if (!detailModal.classList.contains('hidden')) {
                closeReportDetailModal();
            } else if (!reportsModal.classList.contains('hidden')) {
                closeMyReportsModal();
            }
        }
    });

    // Re-open modal automatically if pagination triggers a reload with ?open_modal=1
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_modal') == 1) {
        window.addEventListener('load', () => {
            openMyReportsModal();
        });
    }
</script>
