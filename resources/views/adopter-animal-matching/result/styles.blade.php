        <!-- Animation Styles -->
        <style>
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

            @keyframes scaleIn {
                from {
                    opacity: 0;
                    transform: scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out forwards;
                opacity: 0;
            }

            .animate-scale-in {
                animation: scaleIn 0.5s ease-out forwards;
                opacity: 0;
            }

            /* Smooth hover effect */
            .match-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .match-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            /* Score number styling */
            .score-number {
                display: inline-block;
            }

            /* Match score pulse animation */
            @keyframes scorePulse {
                0%, 100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.7);
                }
                50% {
                    transform: scale(1.05);
                    box-shadow: 0 0 0 10px rgba(168, 85, 247, 0);
                }
            }

            .score-badge {
                animation: scorePulse 2s ease-in-out infinite;
            }

            .score-badge-top {
                animation: scorePulse 1.5s ease-in-out infinite;
            }

            /* Animal image hover effect */
            .animal-image {
                transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .match-card:hover .animal-image {
                transform: scale(1.05) rotate(2deg);
            }
        </style>
