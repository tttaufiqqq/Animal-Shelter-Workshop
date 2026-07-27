const CACHE_NAME = 'asw-shell-v1';
const SHELL_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
});

// Every request except page navigations goes straight to the network,
// untouched — this app is session/DB-state-heavy (Livewire, CSRF tokens,
// live DB health checks), so caching anything beyond the static app shell
// would risk serving stale or broken data. Navigations get a network-first
// fallback to offline.html purely so a lost connection shows a branded
// page instead of the browser's own offline error.
self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match('/offline.html'))
    );
});
