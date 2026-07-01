<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .fade-in { animation: fadeIn 0.5s ease-out; }
    .slide-in { animation: slideIn 0.4s ease-out; }
    .skeleton { animation: pulse 1.5s ease-in-out infinite; }
    .hover-scale { transition: transform 0.3s ease; }
    .hover-scale:hover { transform: scale(1.02); }
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .gradient-border {
        border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-image-slice: 1;
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
</style>
