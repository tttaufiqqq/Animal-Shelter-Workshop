<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      onerror="console.warn('Leaflet CSS failed to load')"/>

<style>
    /* Smooth animations */
    #toastContainer > div {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Loading spinner */
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Accuracy indicator colors - more vibrant */
    .accuracy-good { color: #10B981; font-weight: 700; }
    .accuracy-medium { color: #F59E0B; font-weight: 700; }
    .accuracy-poor { color: #EF4444; font-weight: 700; }

    /* Pulsing animation for marker */
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.9; }
    }

    /* Map controls */
    .leaflet-control-geocoder {
        border-radius: 8px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    /* Custom marker styling */
    .custom-marker {
        background: transparent !important;
        border: none !important;
    }

    /* Leaflet popup styling */
    .leaflet-popup-content-wrapper {
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
    }

    /* Enhanced accuracy indicator with cyan theme */
    #accuracyIndicator {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.95), rgba(34, 211, 238, 0.95)) !important;
        border: 2px solid rgba(255, 255, 255, 0.8);
        color: white;
        font-weight: 600;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4) !important;
    }

    #accuracyIndicator span:first-child {
        color: rgba(255, 255, 255, 0.95);
    }

    /* Enhanced accuracy colors with glow effects */
    .accuracy-good {
        color: #10B981 !important;
        font-weight: 800;
        text-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
    }
    .accuracy-medium {
        color: #F59E0B !important;
        font-weight: 800;
        text-shadow: 0 0 8px rgba(245, 158, 11, 0.5);
    }
    .accuracy-poor {
        color: #EF4444 !important;
        font-weight: 800;
        text-shadow: 0 0 8px rgba(239, 68, 68, 0.5);
    }

    /* Map border with cyan glow effect */
    #map {
        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.1),
                    0 8px 24px rgba(0, 0, 0, 0.12) !important;
        transition: all 0.3s ease;
    }

    #map:hover {
        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.2),
                    0 10px 28px rgba(0, 0, 0, 0.15) !important;
    }
</style>
