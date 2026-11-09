const CACHE_NAME = 'kfhi-offline-card-shell-v13';
const SHELL_FILES = [
    './offline-child-cards.html',
    './offline-attendance.html',
    './offline-child-form-shell.html',
    './qr-scanner-shell.html',
    './manifest.webmanifest',
    './js/offline-child-cards.js',
    './js/offline-child-form-shell.js',
    './js/qr-scanner-shell.js',
    './js/enumerator-attendance.js',
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
    const scopeUrl = new URL(self.registration.scope);
    const relativePath = url.pathname.slice(scopeUrl.pathname.length).replace(/^\/+/, '');
    const existingChildManagementRoute = /^(admin|child-officer)\/children(?:\/(?:create|[1-9]\d*(?:\/edit)?))?\/?$/.test(relativePath);
    const existingScannerRoute = /^(admin|child-officer)\/qr\/scan\/?$/.test(relativePath);

    // Keep the CMS child URLs unchanged. Offline, return the cached form shell only for these routes.
    if (request.mode === 'navigate' && url.origin === scopeUrl.origin && existingScannerRoute) {
        event.respondWith(
            fetch(request).catch(async () => {
                const cachedShell = await caches.open(CACHE_NAME)
                    .then(cache => cache.match(new URL('./qr-scanner-shell.html', self.registration.scope).href));
                if (!cachedShell) return Response.error();
                const html = (await cachedShell.text()).replace('__APP_BASE__', self.registration.scope);
                return new Response(html, {
                    status: 200,
                    headers: {
                        'Content-Type': 'text/html; charset=utf-8',
                        'Cache-Control': 'no-store',
                        'X-Offline-Fallback': 'qr-scanner',
                    },
                });
            })
        );
        return;
    }

    if (request.mode === 'navigate' && url.origin === scopeUrl.origin && existingChildManagementRoute) {
        event.respondWith(
            fetch(request).catch(async () => {
                const cachedShell = await caches.open(CACHE_NAME)
                    .then(cache => cache.match(new URL('./offline-child-form-shell.html', self.registration.scope).href));
                if (!cachedShell) return Response.error();
                const html = (await cachedShell.text()).replace('__APP_BASE__', self.registration.scope);
                return new Response(html, {
                    status: 200,
                    headers: {
                        'Content-Type': 'text/html; charset=utf-8',
                        'Cache-Control': 'no-store',
                        'X-Offline-Fallback': 'child-management',
                    },
                });
            })
        );
        return;
    }

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
