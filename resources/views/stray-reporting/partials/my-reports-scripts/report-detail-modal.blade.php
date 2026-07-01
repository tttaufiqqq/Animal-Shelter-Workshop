<script>
    // Open Report Detail Modal
    function openReportDetailModal(reportId) {
        const reportsData = getReportsData();
        const report = reportsData.find(r => r.id === reportId);
        if (!report) {
            console.error('Report not found:', reportId);
            return;
        }

        document.getElementById('detailReportTitle').textContent = `Report #${report.id}`;

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
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border ${statusClass}">
                        ${report.report_status}
                    </span>
                    <span class="text-sm text-gray-500">
                        ${new Date(report.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })} -
                        ${new Date(report.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                    </span>
                </div>
                ${generateStatusTracker(report.report_status)}
            </div>

            <div class="space-y-6 mt-6">
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

        setTimeout(() => {
            const detailContent = document.getElementById('reportDetailContent');
            if (detailContent) detailContent.scrollTop = 0;
        }, 0);

        setTimeout(() => {
            if (detailMap) detailMap.remove();

            const mapContainer = document.getElementById('detail-map');
            const lat = parseFloat(mapContainer.dataset.lat);
            const lng = parseFloat(mapContainer.dataset.lng);

            detailMap = L.map('detail-map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(detailMap);
            L.marker([lat, lng]).addTo(detailMap);

            setTimeout(() => { detailMap.invalidateSize(); }, 100);
        }, 100);
    }
</script>
