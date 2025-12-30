<x-admin-layout>
    {{-- Page Title --}}
    <x-slot name="title">Rescue Map</x-slot>

    {{-- Additional Styles --}}
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #map {
                height: 100%;
                width: 100%;
                border-radius: 0.75rem;
                z-index: 1;
            }

            .cluster-marker {
                border-radius: 50%;
                text-align: center;
                color: white;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border: 2px solid rgba(255,255,255,0.9);
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .cluster-marker:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            }

            .cluster-small { width: 40px; height: 40px; font-size: 12px; }
            .cluster-medium { width: 50px; height: 50px; font-size: 14px; }
            .cluster-large { width: 60px; height: 60px; font-size: 16px; }
            .cluster-xlarge { width: 70px; height: 70px; font-size: 18px; }

            .cluster-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
            .cluster-yellow { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
            .cluster-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

            .leaflet-popup-content-wrapper {
                border-radius: 0.75rem;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
        </style>
    @endpush

    {{-- Database Warning Banner --}}
    @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
        <div class="mb-4 flex items-center gap-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($dbDisconnected as $connection => $info)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $info['module'] }}
                        </span>
                    @endforeach
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    {{-- Page Content --}}
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Rescue Operations Map</h1>
            <p class="text-sm text-gray-600 mt-1">Real-time rescue operations tracking</p>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-admin.stat-card
                title="Total"
                :value="number_format($statistics['total'])"
                color="blue"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Success"
                :value="number_format($statistics['success'])"
                color="green"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Failed"
                :value="number_format($statistics['failed'])"
                color="red"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M6 18L18 6M6 6l12 12\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Scheduled"
                :value="number_format($statistics['scheduled'])"
                color="orange"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="In Progress"
                :value="number_format($statistics['in_progress'])"
                color="purple"
                :icon="'<svg class=\'w-6 h-6 animate-spin\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15\'/>
                </svg>'"
            />

            <x-admin.stat-card
                title="Pending"
                :value="number_format($statistics['pending'])"
                color="indigo"
                :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\'/>
                </svg>'"
            />
        </div>

        {{-- Map Container --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" style="height: calc(100vh - 450px); min-height: 500px;">
            <div class="relative h-full">
                <div id="map" class="h-full"></div>

                {{-- Details Panel (Slide-in) --}}
                <div id="detailsPanel" class="hidden absolute top-4 right-4 bg-white rounded-xl shadow-xl border border-gray-200 p-6 w-96 max-h-[calc(100%-2rem)] overflow-y-auto z-[1000]">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 id="clusterCity" class="text-lg font-bold text-gray-900"></h3>
                            <p class="text-xs text-gray-500 mt-1">Cluster Overview</p>
                        </div>
                        <button onclick="closeDetails()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div id="clusterStats" class="space-y-2 mb-4"></div>

                    <div class="bg-green-50 rounded-lg p-3 border border-green-100 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-medium text-gray-700">Success Rate</span>
                            <span id="successRate" class="text-sm font-bold text-green-600"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div id="successBar" class="bg-green-500 h-2 rounded-full transition-all duration-500"></div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Recent Reports
                        </h4>
                        <div id="reportsList" class="space-y-2 max-h-48 overflow-y-auto"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-center gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-gradient-to-br from-green-500 to-green-600 rounded-full"></div>
                    <span class="text-gray-700">High Success <span class="text-gray-500">(≥70%)</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full"></div>
                    <span class="text-gray-700">Medium <span class="text-gray-500">(40-70%)</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-gradient-to-br from-red-500 to-red-600 rounded-full"></div>
                    <span class="text-gray-700">Low <span class="text-gray-500">(&lt;40%)</span></span>
                </div>
            </div>
        </div>
    </div>

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
</x-admin-layout>
