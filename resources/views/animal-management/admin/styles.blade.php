<style>
    /* Card hover effects */
    .animal-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .animal-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .animal-card:hover .animal-image {
        transform: scale(1.05);
    }

    .animal-image {
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Stat card animations */
    .stat-card {
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.15);
    }

    /* Pulse animation for critical notifications */
    @keyframes pulse-ring {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }

    .pulse-red {
        animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* Quick action button animations */
    .quick-action-btn {
        transition: all 0.2s ease;
    }

    .quick-action-btn:hover {
        transform: scale(1.05);
    }

    /* Smooth fade-in animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
</style>
