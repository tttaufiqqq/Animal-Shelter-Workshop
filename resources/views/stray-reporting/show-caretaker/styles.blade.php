<style>
    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: #9333ea; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #7e22ce; }

    /* Smooth line clamp */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Ensure maps stay below modals and overlays */
    .leaflet-map { position: relative; z-index: 1; width: 100%; height: 100%; }
    .leaflet-container { height: 100%; width: 100%; }

    /* Ensure loading overlay is above everything */
    #loadingOverlay { z-index: 999999 !important; }

    /* Ensure modals are above map */
    #remarksModal, #imageModal { z-index: 100000 !important; }

    /* Spinner animation */
    .animate-spin { animation: spin 1s linear infinite; }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Fade-in animation for step transitions */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in { animation: fadeIn 0.4s ease-out; }

    /* Fade-in-up animation for alerts */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in-up { animation: fadeInUp 0.6s ease-out; }

    /* Apply fade-in to modal body content */
    #successModalBody > div:not(.hidden) { animation: fadeIn 0.4s ease-out; }

    /* Smooth progress indicator transitions */
    #progressIndicator > div > div { transition: all 0.3s ease; }

    /* Input focus effects */
    input:focus, select:focus, textarea:focus { outline: none; }
</style>
