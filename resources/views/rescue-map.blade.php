<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rescue Reports Map</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        #map {
            height: 100%;
            width: 100%;
            filter: brightness(0.95) contrast(1.05);
            z-index: 0;
        }
        .cluster-marker {
            border-radius: 50%;
            text-align: center;
            color: white;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2), 0 0 0 1px rgba(255,255,255,0.2) inset;
            border: 2px solid rgba(255,255,255,0.9);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .cluster-marker:hover {
            transform: scale(1.15);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.3) inset;
        }

        .cluster-small { width: 44px; height: 44px; font-size: 13px; }
        .cluster-medium { width: 54px; height: 54px; font-size: 15px; }
        .cluster-large { width: 64px; height: 64px; font-size: 17px; }
        .cluster-xlarge { width: 76px; height: 76px; font-size: 20px; }

        .cluster-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .cluster-yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .cluster-red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .glassmorphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            z-index: 10;
        }

        .details-panel {
            animation: slideIn 0.3s ease-out;
            z-index: 1000;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .progress-ring {
            transition: stroke-dashoffset 0.5s ease;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #9333ea;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #7e22ce;
        }
        /* Smooth line clamp */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #9333ea;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #7e22ce;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100">
    {{-- Navbar --}}
    @include('navbar')

    <!-- Limited Connectivity Warning Banner -->
    @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
    <div id="connectivityBanner" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
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
            <button onclick="closeConnectivityBanner()" class="flex-shrink-0 ml-4 text-yellow-400 hover:text-yellow-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <script>
        function closeConnectivityBanner() {
            document.getElementById('connectivityBanner').style.display = 'none';
        }
    </script>
    @endif

    {{-- Main Content Wrapper --}}
    <div class="flex flex-col h-screen">
        <div class="glassmorphism shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Animal Rescue Reports</h1>
                    <p class="text-sm text-gray-500 mt-1">Real-time rescue operations dashboard</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600 font-medium">Live Data</span>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-2xl border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Total</span>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($statistics['total']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-gradient-to-br from-green-50 to-emerald-100 p-4 rounded-2xl border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-green-700 uppercase tracking-wide">Success</span>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ number_format($statistics['success']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-2xl border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-red-700 uppercase tracking-wide">Failed</span>
                            <p class="text-2xl font-bold text-red-900 mt-1">{{ number_format($statistics['failed']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-gradient-to-br from-yellow-50 to-amber-100 p-4 rounded-2xl border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-yellow-700 uppercase tracking-wide">Scheduled</span>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ number_format($statistics['scheduled']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-2xl border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-purple-700 uppercase tracking-wide">In Progress</span>
                            <p class="text-2xl font-bold text-purple-900 mt-1">{{ number_format($statistics['in_progress']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-gradient-to-br from-gray-50 to-slate-100 p-4 rounded-2xl border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Pending</span>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($statistics['pending']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gray-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 relative">
            <div id="map"></div>

            <div id="detailsPanel" class="hidden absolute top-6 right-6 glassmorphism rounded-3xl shadow-2xl p-6 w-96 max-h-[calc(100%-3rem)] overflow-y-auto z-[1000] details-panel">
                <div class="flex justify-between items-start mb-5">
                    <div>
                        <h3 id="clusterCity" class="text-2xl font-bold text-gray-800"></h3>
                        <p class="text-sm text-gray-500 mt-1">Cluster Overview</p>
                    </div>
                    <button onclick="closeDetails()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div id="clusterStats" class="space-y-3 mb-5"></div>

                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-4 border border-green-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">Success Rate</span>
                        <span id="successRate" class="text-lg font-bold text-green-600"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div id="successBar" class="bg-gradient-to-r from-green-500 to-emerald-500 h-3 rounded-full transition-all duration-1000 ease-out"></div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-5 mt-5">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Recent Reports
                    </h4>
                    <div id="reportsList" class="space-y-2 max-h-64 overflow-y-auto"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Legend (fixed to bottom) --}}
    <div class="glassmorphism border-t border-gray-200 p-4 fixed bottom-0 left-0 w-full z-50">
        <div class="flex items-center justify-center gap-8 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full shadow-md"></div>
                <span class="font-medium text-gray-700">High Success <span class="text-gray-500">(≥70%)</span></span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-full shadow-md"></div>
                <span class="font-medium text-gray-700">Medium <span class="text-gray-500">(40-70%)</span></span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-gradient-to-br from-red-500 to-red-600 rounded-full shadow-md"></div>
                <span class="font-medium text-gray-700">Low <span class="text-gray-500">(&lt;40%)</span></span>
            </div>
        </div>
    </div>

    <script>
        // --- START OF CORRECTED JAVASCRIPT ---

        // 1. Initialize map centered on Malaysia
        const map = L.map('map', { zoomControl: false }).setView([4.2105, 101.9758], 6);

        // 2. Add controls and tiles
        L.control.zoom({ position: 'bottomright' }).addTo(map);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 18 }).addTo(map);

        // 3. Reports data from Laravel (Keep this one)
        const reportsData = @json($reports);

        function clusterReports(reports) {
            const clusters = [];
            // Adjusted clusterRadius for better clustering across larger areas
            const clusterRadius = 0.003; // Use a slightly larger radius for initial clustering

            reports.forEach(report => {
                let foundCluster = null;

                for (let cluster of clusters) {
                    // Check if the report is within a 0.5 degree latitude/longitude box
                    // This is a simplified check for clustering in a map context
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

                    // Recalculate centroid (simple average)
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
                offset: [0, -20],
                className: 'modern-tooltip'
            });
        });

        if (clusters.length > 0) {
            const bounds = L.latLngBounds(clusters.map(c => [c.lat, c.lng]));
            map.fitBounds(bounds, { padding: [50, 50] });
        } else {
            // Show message when no reports are available (database offline)
            const noDataMessage = L.control({ position: 'topright' });
            noDataMessage.onAdd = function(map) {
                const div = L.DomUtil.create('div', 'leaflet-control-custom');
                div.innerHTML = `
                    <div class="bg-white rounded-xl shadow-2xl p-6 border-l-4 border-yellow-400" style="max-width: 350px;">
                        <div class="flex items-start gap-3">
                            <svg class="w-8 h-8 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-1">No Reports Available</h3>
                                <p class="text-sm text-gray-600">
                                    The database connection is currently unavailable. The map is displaying Malaysia's location.
                                </p>
                                <p class="text-xs text-gray-500 mt-2">
                                    Report markers will appear here when the database is back online.
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                div.style.pointerEvents = 'all';
                return div;
            };
            noDataMessage.addTo(map);
        }

        function showClusterDetails(cluster) {
            const panel = document.getElementById('detailsPanel');
            const total = cluster.reports.length;
            const successRate = total > 0 ? ((cluster.success / total) * 100).toFixed(1) : 0;

            document.getElementById('clusterCity').textContent =
                `${cluster.city || 'Unknown'}, ${cluster.state || ''}`;

            document.getElementById('clusterStats').innerHTML = `
                <div class="bg-white rounded-xl p-3 flex justify-between items-center border border-gray-100">
                    <span class="text-sm text-gray-600 font-medium">Total Reports</span>
                    <span class="font-bold text-xl text-gray-900">${total}</span>
                </div>
                <div class="bg-green-50 rounded-xl p-3 flex justify-between items-center border border-green-100">
                    <span class="text-sm text-green-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Success
                    </span>
                    <span class="font-bold text-lg text-green-600">${cluster.success}</span>
                </div>
                <div class="bg-red-50 rounded-xl p-3 flex justify-between items-center border border-red-100">
                    <span class="text-sm text-red-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Failed
                    </span>
                    <span class="font-bold text-lg text-red-600">${cluster.failed}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="bg-yellow-50 rounded-xl p-2 text-center border border-yellow-100">
                        <div class="text-xs text-yellow-700 font-medium">Scheduled</div>
                        <div class="text-lg font-bold text-yellow-600">${cluster.scheduled}</div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-2 text-center border border-purple-100">
                        <div class="text-xs text-purple-700 font-medium">In Progress</div>
                        <div class="text-lg font-bold text-purple-600">${cluster.inProgress}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-2 text-center border border-gray-200">
                        <div class="text-xs text-gray-700 font-medium">Pending</div>
                        <div class="text-lg font-bold text-gray-600">${cluster.pending}</div>
                    </div>
                </div>
            `;

            document.getElementById('successRate').textContent = `${successRate}%`;
            document.getElementById('successBar').style.width = `${successRate}%`;

            const reportsHTML = cluster.reports.slice(0, 5).map(report => `
                <div class="bg-white rounded-xl p-3 border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-start justify-between mb-2">
                        <div class="font-semibold text-gray-800">Report #${report.id}</div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                            report.status === 'Success' ? 'bg-green-100 text-green-700' :
                            report.status === 'Failed' ? 'bg-red-100 text-red-700' :
                            report.status === 'Scheduled' ? 'bg-yellow-100 text-yellow-700' :
                            report.status === 'In Progress' ? 'bg-purple-100 text-purple-700' :
                            'bg-gray-100 text-gray-700'
                        }">${report.status}</span>
                    </div>
                    <div class="text-xs text-gray-600 mb-1">${report.address || 'No address'}</div>
                    ${report.description ? `<div class="text-xs text-gray-500 mt-2 line-clamp-2">${report.description}</div>` : ''}
                    ${report.report_status ? `<div class="text-xs text-blue-600 mt-1 font-medium">Report Status: ${report.report_status}</div>` : ''}
                </div>
            `).join('');

            document.getElementById('reportsList').innerHTML = reportsHTML;
            panel.classList.remove('hidden');
        }

        function closeDetails() {
            document.getElementById('detailsPanel').classList.add('hidden');
        }
        // --- END OF CORRECTED JAVASCRIPT ---
    </script>
</body>
</html>
