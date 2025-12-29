<script>
    let miniMaps = {};
    let detailMap = null;

    // Store report data for detail view
    const reportsData = @json($userReports->items());

    // Generate Status Tracker HTML
    function generateStatusTracker(currentStatus) {
        const statuses = [
            {
                name: 'Pending',
                label: 'Report Submitted',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            },
            {
                name: 'Assigned',
                label: 'Caretaker Assigned',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
            },
            {
                name: 'In Progress',
                label: 'Rescue In Progress',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
            },
            {
                name: 'Completed',
                label: 'Rescue Completed',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            }
        ];

        // Find current status index
        const currentIndex = statuses.findIndex(s => s.name === currentStatus);

        // Handle Rejected status (alternative path)
        if (currentStatus === 'Rejected') {
            return `
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border-2 border-red-200">
                    <div class="flex items-center justify-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-red-800">Report Rejected</h4>
                            <p class="text-sm text-red-600">This report has been marked as invalid or spam</p>
                        </div>
                    </div>
                </div>
            `;
        }

        let html = '<div class="bg-white rounded-xl p-6 border border-gray-200 overflow-hidden">';
        html += '<div class="flex items-center justify-between relative" style="isolation: isolate;">';

        statuses.forEach((status, index) => {
            const isCompleted = index < currentIndex;
            const isCurrent = index === currentIndex;
            const isPending = index > currentIndex;

            // Step circle and icon
            html += `
                <div class="flex flex-col items-center relative" style="flex: 0 0 auto; z-index: 2;">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3 transition-all duration-300
                        ${isCompleted ? 'bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg' :
                          isCurrent ? 'bg-gradient-to-br from-purple-500 to-purple-600 shadow-xl ring-4 ring-purple-200 animate-pulse' :
                          'bg-gray-200'}">
                        <div class="${isCompleted || isCurrent ? 'text-white' : 'text-gray-400'}">
                            ${isCompleted ?
                                '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' :
                                status.icon}
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold ${isCompleted || isCurrent ? 'text-purple-700' : 'text-gray-500'} whitespace-nowrap">
                            ${status.label}
                        </p>
                        ${isCurrent ? '<p class="text-xs text-purple-600 font-bold mt-1">Current Status</p>' : ''}
                    </div>
                </div>
            `;

            // Connection line (except for last item)
            if (index < statuses.length - 1) {
                html += `
                    <div class="flex-1 h-1 mx-2 rounded-full transition-all duration-500"
                         style="margin-top: -35px; z-index: 1; ${index < currentIndex ? 'background: linear-gradient(to right, #9333ea, #7e22ce);' : 'background: #e5e7eb;'}">
                    </div>
                `;
            }
        });

        html += '</div></div>';
        return html;
    }

    // Open Report Detail Modal
    function openReportDetailModal(reportId) {
        const report = reportsData.find(r => r.id === reportId);
        if (!report) return;

        // Update title
        document.getElementById('detailReportTitle').textContent = `Report #${report.id}`;

        // Build detail content
        const statusClass =
            report.report_status === 'Pending' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' :
                report.report_status === 'Assigned' ? 'bg-blue-100 text-blue-800 border-blue-300' :
                report.report_status === 'In Progress' ? 'bg-purple-100 text-purple-800 border-purple-300' :
                report.report_status === 'Completed' ? 'bg-green-100 text-green-800 border-green-300' :
                report.report_status === 'Rejected' ? 'bg-red-100 text-red-800 border-red-300' :
                    'bg-gray-100 text-gray-800 border-gray-300';

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

                <!-- Status Tracker -->
                ${generateStatusTracker(report.report_status)}
            </div>

            <div class="space-y-6 mt-6">

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

        // Reset scroll position to top (after modal is shown)
        setTimeout(() => {
            const detailContent = document.getElementById('reportDetailContent');
            if (detailContent) {
                detailContent.scrollTop = 0;
            }
        }, 0);

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
        const modal = document.getElementById('reportDetailModal');
        const detailContent = document.getElementById('reportDetailContent');

        // Reset scroll position when closing
        if (detailContent) {
            detailContent.scrollTop = 0;
        }

        modal.classList.add('hidden');

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
        const modal = document.getElementById('myReportsModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Image modal functions
    function openImageModal(imageSrc) {
        event.stopPropagation();
        const modal = document.getElementById('imageModal');
        document.getElementById('modalImage').src = imageSrc;
        modal.classList.remove('hidden');
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
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
