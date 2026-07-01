<style>
    /* Visual feedback for auto-filled fields */
    .auto-filled {
        background-color: #f0f9ff !important;
        border-color: #0ea5e9 !important;
        color: #0369a1 !important;
        transition: all 0.3s ease;
    }

    .auto-filled-success {
        background-color: #f0fdf4 !important;
        border-color: #22c55e !important;
        color: #15803d !important;
    }

    /* Accuracy indicator colors */
    .accuracy-good { color: #10B981; }
    .accuracy-medium { color: #F59E0B; }
    .accuracy-poor { color: #EF4444; }

    /* Toast animations */
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

    /* Bounce animation for manual adjustment */
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce {
        animation: bounce 0.5s ease-in-out 2;
    }

    /* Map marker pulse animation */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .animate-pulse {
        animation: pulse 2s ease-in-out infinite;
    }
</style>
