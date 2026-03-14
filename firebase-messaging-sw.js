
// service-worker.js
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open('visitor-app-cache').then(cache => {
            return cache.addAll([
                '/',
                '/smart-visitor-app/owner/owner_login.html',
                '/smart-visitor-app/guard/guard_login.html',
                '/smart-visitor-app/style.css'
                // Add more assets here
            ]);
        })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => response || fetch(event.request))
    );
});
