    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#7e22ce">
    <link rel="apple-touch-icon" href="/images/icons/icon-192.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
        }
    </script>
