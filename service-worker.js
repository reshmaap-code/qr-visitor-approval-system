const CACHE_NAME = 'visitor-app-cache';
const urlsToCache = [
    '/smart-visitor-app/style.css',
    '/smart-visitor-app/icons/icon-192.png',
    '/smart-visitor-app/icons/icon-512.png',
    '/smart-visitor-app/manifest.json'
    // You can add more static CSS/JS/images here
];

// Install event → cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim());
});

// Fetch event → network-first for HTML, cache-first for others
self.addEventListener('fetch', event => {
    const request = event.request;

    if (request.destination === 'document' || request.url.endsWith('.php')) {
        // Network-first for HTML / PHP pages
        event.respondWith(
            fetch(request)
                .then(response => response)
                .catch(() => caches.match(request))
        );
    } else {
        // Cache-first for CSS/JS/images
        event.respondWith(
            caches.match(request).then(response => response || fetch(request))
        );
    }
});
