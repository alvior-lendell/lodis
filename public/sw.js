const CACHE_NAME = 'lodis-v2-cache-v5';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/favicon.ico',
    '/images/LODISv2.png'
];

// Immediately activate updated service workers
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Ignore non-GET, non-HTTP(S), or browser extension requests
    if (request.method !== 'GET' || !url.protocol.startsWith('http')) {
        return;
    }

    // Bypass Service Worker entirely for Vite-compiled assets to prevent modulepreload mismatches
    if (url.pathname.startsWith('/build/')) {
        return;
    }

    // Network-First for HTML Page Navigations (Guarantees fresh Blade updates on standard reload)
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return networkResponse;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // Stale-While-Revalidate for CSS, JS, and image assets (non-build)
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            const fetchPromise = fetch(request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }
                return networkResponse;
            }).catch(() => {/* Handle offline asset fallback */});

            return cachedResponse || fetchPromise;
        })
    );
});