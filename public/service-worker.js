const CACHE_NAME = 'kfhi-offline-card-shell-v6';
const SHELL_FILES = [
    './offline-child-cards.html',
    './offline-attendance.html',
    './manifest.webmanifest',
    './js/offline-child-cards.js',
    './js/offline-attendance-app.js',
    './js/offline-child-cache.js',
    './js/html5-qrcode.min.js',
    './js/qrcode.min.js',
    './images/logo.png',
    './js/offline-attendance.js',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(SHELL_FILES))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(key => key.startsWith('kfhi-offline-card-shell-') && key !== CACHE_NAME).map(key => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    const attendanceShell = new URL('./offline-attendance.html', self.registration.scope);
    if (url.origin === attendanceShell.origin && url.pathname === attendanceShell.pathname) {
        event.respondWith(
            caches.open(CACHE_NAME).then(cache =>
                cache.match(attendanceShell.href).then(cached => cached || fetch(request))
            )
        );
        return;
    }

    const shellUrls = SHELL_FILES.map(path => new URL(path, self.registration.scope).href);
    if (!shellUrls.includes(url.href)) return;

    event.respondWith(
        caches.match(request).then(cached => cached || fetch(request))
    );
});
