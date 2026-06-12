'use strict';
/* Movix — Service Worker v3 (PWA assets + Firebase Messaging) */

// ── Firebase (carga opcional) ─────────────────────────────────────────────
var _fbReady = false;
try {
    importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
    importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');
} catch (_) {}

// ── Cache ─────────────────────────────────────────────────────────────────
var CACHE = 'movix-v3';

var PRECACHE = [
    '/assets/css/variables.css',
    '/assets/css/base.css',
    '/assets/css/mobile.css',
    '/assets/css/cliente.css',
    '/assets/css/conductor.css',
    '/assets/img/icon.svg',
    '/assets/js/pwa.js',
    '/assets/js/utils.js',
    '/assets/js/gps.js',
];

// ── Install ───────────────────────────────────────────────────────────────
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE)
            .then(function (cache) { return cache.addAll(PRECACHE).catch(function () {}); })
            .then(function () { return self.skipWaiting(); })
    );
});

// ── Activate: borrar cachés anteriores ───────────────────────────────────
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys()
            .then(function (keys) {
                return Promise.all(
                    keys.filter(function (k) { return k !== CACHE; })
                        .map(function (k) { return caches.delete(k); })
                );
            })
            .then(function () { return self.clients.claim(); })
    );
});

// ── Fetch: SOLO activos estáticos y API ──────────────────────────────────
// Las páginas HTML (navegación) se dejan pasar directo al navegador.
// Esto evita que se cacheen páginas autenticadas y que el logout/redirect
// del servidor sea manejado incorrectamente por el SW.
self.addEventListener('fetch', function (event) {
    var req = event.request;
    var url = new URL(req.url);

    // Solo interceptar same-origin y GET
    if (url.origin !== self.location.origin || req.method !== 'GET') return;

    // API → network-first, respuesta offline si falla
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(req).catch(function () {
                return new Response(JSON.stringify({ error: 'sin conexión' }), {
                    status:  503,
                    headers: { 'Content-Type': 'application/json' },
                });
            })
        );
        return;
    }

    // Assets estáticos → cache-first, red como fallback y actualización en background
    if (url.pathname.startsWith('/assets/')) {
        event.respondWith(
            caches.match(req).then(function (cached) {
                var network = fetch(req).then(function (res) {
                    if (res.ok) {
                        var clone = res.clone();
                        caches.open(CACHE).then(function (c) { c.put(req, clone); });
                    }
                    return res;
                });
                return cached || network;
            })
        );
        return;
    }

    // Todo lo demás (HTML, logout, auth) → pasar directo, sin intercept.
    // El navegador maneja el redirect del servidor normalmente.
});

// ── Firebase: inicializar cuando el cliente envía la config ──────────────
self.addEventListener('message', function (event) {
    if (!event.data || event.data.type !== 'FIREBASE_CONFIG' || _fbReady) return;
    try {
        firebase.initializeApp(event.data.config);
        _fbReady = true;
        var messaging = firebase.messaging();
        messaging.onBackgroundMessage(function (payload) {
            var n = payload.notification || {};
            self.registration.showNotification(n.title || 'Movix', {
                body:    n.body  || '',
                icon:    '/assets/img/icon.svg',
                badge:   '/assets/img/icon.svg',
                data:    payload.data || {},
                vibrate: [200, 100, 200],
            });
        });
    } catch (_) {}
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var viajeId = (event.notification.data || {}).viaje_id;
    var url     = viajeId ? '/conductor#viaje-' + viajeId : '/conductor';
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(function (wins) {
            for (var i = 0; i < wins.length; i++) {
                if ('focus' in wins[i]) return wins[i].focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
