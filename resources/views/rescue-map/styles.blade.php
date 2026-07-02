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
