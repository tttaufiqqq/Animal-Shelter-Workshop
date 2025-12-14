/**
 * Prevent Loading Hang Script
 *
 * This script ensures pages load properly even when:
 * - CDN resources fail to load (no internet)
 * - External scripts timeout
 * - Loading overlays get stuck
 */

(function() {
    'use strict';

    // Maximum time to wait for page load (5 seconds)
    const MAX_LOAD_TIME = 5000;

    // Force page to become visible after timeout
    const forceVisible = () => {
        document.documentElement.style.opacity = '1';
        document.documentElement.style.visibility = 'visible';
        document.body.style.opacity = '1';
        document.body.style.visibility = 'visible';

        // Remove any loading overlays
        const loadingElements = document.querySelectorAll('[id*="loading"], [class*="loading"], [class*="spinner"]');
        loadingElements.forEach(el => {
            if (el.style.position === 'fixed' || el.style.position === 'absolute') {
                el.style.display = 'none';
            }
        });

        console.log('Page visibility forced - loading timeout reached');
    };

    // Set timeout to force page visibility
    const loadTimeout = setTimeout(forceVisible, MAX_LOAD_TIME);

    // Clear timeout if page loads normally
    window.addEventListener('load', () => {
        clearTimeout(loadTimeout);
        forceVisible(); // Still call to ensure cleanup
    });

    // Also try on DOMContentLoaded (earlier than 'load')
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (document.readyState === 'complete') {
                clearTimeout(loadTimeout);
            }
        }, 100);
    });

    // Handle Vite's dev server overlay
    if (import.meta && import.meta.hot) {
        import.meta.hot.on('vite:beforeUpdate', () => {
            forceVisible();
        });
    }

    // Detect and log failed resource loads
    window.addEventListener('error', (event) => {
        if (event.target.tagName === 'SCRIPT' || event.target.tagName === 'LINK') {
            console.warn('Resource failed to load:', event.target.src || event.target.href);
        }
    }, true);

    // Add CSS to prevent any opacity/visibility tricks from hiding content
    const style = document.createElement('style');
    style.textContent = `
        /* Prevent page from being hidden */
        html, body {
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Auto-hide stuck loading overlays */
        .loading-overlay[style*="display: block"],
        .loading-overlay[style*="display: flex"],
        [class*="vite-error-overlay"] {
            animation: autoHideLoading 3s forwards !important;
        }

        @keyframes autoHideLoading {
            99% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                visibility: hidden;
                display: none !important;
            }
        }
    `;
    document.head.appendChild(style);

})();
