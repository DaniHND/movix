'use strict';
/* Movix — PWA: registro de SW, install prompt, notificación de actualización */

(function () {
    if (!('serviceWorker' in navigator)) return;

    let _installEvent = null;

    // ── Registro del Service Worker ──────────────────────────────────────────
    navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .then(function (reg) {
            window._swReg = reg;

            reg.addEventListener('updatefound', function () {
                var worker = reg.installing;
                if (!worker) return;
                worker.addEventListener('statechange', function () {
                    if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                        _showUpdateBanner();
                    }
                });
            });
        })
        .catch(function () {});

    // ── Prompt de instalación ────────────────────────────────────────────────
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        _installEvent = e;
        var btn = document.getElementById('pwa-install-btn');
        if (btn) btn.hidden = false;
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#pwa-install-btn') || !_installEvent) return;
        _installEvent.prompt();
        _installEvent.userChoice.then(function () {
            _installEvent = null;
            var btn = document.getElementById('pwa-install-btn');
            if (btn) btn.hidden = true;
        });
    });

    window.addEventListener('appinstalled', function () {
        _installEvent = null;
    });

    // ── Banner de actualización disponible ───────────────────────────────────
    function _showUpdateBanner() {
        if (document.getElementById('pwa-update-banner')) return;
        var banner = document.createElement('div');
        banner.id = 'pwa-update-banner';
        banner.style.cssText = [
            'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);',
            'background:#0F3460;color:#fff;padding:10px 18px;border-radius:24px;',
            'font-size:14px;z-index:9999;display:flex;gap:12px;align-items:center;',
            'box-shadow:0 4px 16px rgba(0,0,0,.3);white-space:nowrap;',
        ].join('');
        banner.innerHTML = 'Nueva versión disponible '
            + '<button onclick="location.reload()" style="background:#E94560;border:none;'
            + 'color:#fff;padding:4px 14px;border-radius:12px;cursor:pointer;'
            + 'font-size:13px;font-weight:600">Actualizar</button>';
        document.body.appendChild(banner);
    }
})();
