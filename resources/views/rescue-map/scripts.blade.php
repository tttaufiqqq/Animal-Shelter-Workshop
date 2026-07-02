    {{-- Scripts --}}
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            // Malaysia bounds
            const malaysiaBounds = L.latLngBounds(
                L.latLng(0.855222, 99.643478),
                L.latLng(7.363417, 119.267502)
            );

            // Initialize map
            const map = L.map('map', {
                zoomControl: true,
                maxBounds: malaysiaBounds.pad(0.5),
                maxBoundsViscosity: 1.0,
                minZoom: 5,
                maxZoom: 18
            }).setView([4.2105, 101.9758], 6);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18,
                minZoom: 5
            }).addTo(map);

            // Reports data
            const reportsData = @json($reports);

            // Cluster reports
            function clusterReports(reports) {
                const clusters = [];
                const clusterRadius = 0.003;

                reports.forEach(report => {
                    let foundCluster = null;

                    for (let cluster of clusters) {
                        const distance = Math.sqrt(
                            Math.pow(cluster.lat - report.lat, 2) +
                            Math.pow(cluster.lng - report.lng, 2)
                        );

                        if (distance <= clusterRadius) {
                            foundCluster = cluster;
                            break;
                        }
                    }

                    if (foundCluster) {
                        foundCluster.reports.push(report);
                        const count = foundCluster.reports.length;
                        foundCluster.lat = ((foundCluster.lat * (count - 1)) + report.lat) / count;
                        foundCluster.lng = ((foundCluster.lng * (count - 1)) + report.lng) / count;

                        switch(report.status) {
                            case 'Success': foundCluster.success++; break;
                            case 'Failed': foundCluster.failed++; break;
                            case 'Scheduled': foundCluster.scheduled++; break;
                            case 'In Progress': foundCluster.inProgress++; break;
                            default: foundCluster.pending++;
                        }
                    } else {
                        clusters.push({
                            city: report.city,
                            state: report.state,
                            lat: report.lat,
                            lng: report.lng,
                            reports: [report],
                            success: report.status === 'Success' ? 1 : 0,
                            failed: report.status === 'Failed' ? 1 : 0,
                            scheduled: report.status === 'Scheduled' ? 1 : 0,
                            inProgress: report.status === 'In Progress' ? 1 : 0,
                            pending: !report.status || report.status === 'Pending' ? 1 : 0
                        });
                    }
                });

                return clusters;
            }

            const clusters = clusterReports(reportsData);

            function getClusterStyle(cluster) {
                const total = cluster.reports.length;
                const successRate = total > 0 ? cluster.success / total : 0;

                let sizeClass = 'cluster-small';
                if (total > 10) sizeClass = 'cluster-xlarge';
                else if (total > 5) sizeClass = 'cluster-large';
                else if (total > 2) sizeClass = 'cluster-medium';

                let colorClass = 'cluster-red';
                if (successRate >= 0.7) colorClass = 'cluster-green';
                else if (successRate >= 0.4) colorClass = 'cluster-yellow';

                return { sizeClass, colorClass };
            }

            clusters.forEach(cluster => {
                const { sizeClass, colorClass } = getClusterStyle(cluster);
                const total = cluster.reports.length;

                const icon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="cluster-marker ${sizeClass} ${colorClass}">${total}</div>`,
                    iconSize: [50, 50],
                    iconAnchor: [25, 25]
                });

                const marker = L.marker([cluster.lat, cluster.lng], { icon: icon })
                    .addTo(map)
                    .on('click', () => showClusterDetails(cluster));

                marker.bindTooltip(`<strong>${cluster.city || 'Unknown'}, ${cluster.state || ''}</strong><br>${total} reports`, {
                    direction: 'top',
                    offset: [0, -20]
                });
            });

            if (clusters.length > 0) {
                const bounds = L.latLngBounds(clusters.map(c => [c.lat, c.lng]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }

            function showClusterDetails(cluster) {
                const panel = document.getElementById('detailsPanel');
                const total = cluster.reports.length;
                const successRate = total > 0 ? ((cluster.success / total) * 100).toFixed(1) : 0;

                document.getElementById('clusterCity').textContent = `${cluster.city || 'Unknown'}, ${cluster.state || ''}`;

                document.getElementById('clusterStats').innerHTML = `
                    <div class="bg-gray-50 rounded-lg p-2 flex justify-between items-center border border-gray-200">
                        <span class="text-xs text-gray-600 font-medium">Total</span>
                        <span class="font-bold text-sm text-gray-900">${total}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-green-50 rounded-lg p-2 border border-green-100">
                            <div class="text-xs text-green-700">Success</div>
                            <div class="text-sm font-bold text-green-600">${cluster.success}</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-2 border border-red-100">
                            <div class="text-xs text-red-700">Failed</div>
                            <div class="text-sm font-bold text-red-600">${cluster.failed}</div>
                        </div>
                    </div>
                `;

                document.getElementById('successRate').textContent = `${successRate}%`;
                document.getElementById('successBar').style.width = `${successRate}%`;

                const reportsHTML = cluster.reports.slice(0, 5).map(report => `
                    <div class="bg-gray-50 rounded-lg p-2 border border-gray-200 hover:border-purple-300 transition">
                        <div class="flex items-start justify-between mb-1">
                            <div class="text-xs font-semibold text-gray-800">Report #${report.id}</div>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full ${
                                report.status === 'Success' ? 'bg-green-100 text-green-700' :
                                report.status === 'Failed' ? 'bg-red-100 text-red-700' :
                                report.status === 'Scheduled' ? 'bg-yellow-100 text-yellow-700' :
                                report.status === 'In Progress' ? 'bg-purple-100 text-purple-700' :
                                'bg-gray-100 text-gray-700'
                            }">${report.status}</span>
                        </div>
                        <div class="text-xs text-gray-600">${report.address || 'No address'}</div>
                    </div>
                `).join('');

                document.getElementById('reportsList').innerHTML = reportsHTML;
                panel.classList.remove('hidden');
            }

            function closeDetails() {
                document.getElementById('detailsPanel').classList.add('hidden');
            }
        </script>
    @endpush
