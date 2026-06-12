/* Movix — Conductor GPS + estado en línea */

(function () {
    'use strict';

    const BASE = (window.MOVIX && window.MOVIX.baseUrl) ? window.MOVIX.baseUrl : '';
    const cfg  = window.MOVIX || {};

    let watchId = null;
    let posTimer = null;
    let lastLat  = null;
    let lastLng  = null;
    let isOnline = cfg.activo === 1 || cfg.activo === true;

    /* ── Helpers ───────────────────────────────── */

    function post(endpoint, body) {
        return fetch(BASE + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
            credentials: 'same-origin',
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
    }

    /* ── GPS watch ─────────────────────────────── */

    function onPosition(pos) {
        lastLat = pos.coords.latitude;
        lastLng = pos.coords.longitude;
    }

    function startGps() {
        if (!navigator.geolocation || watchId !== null) return;
        watchId = navigator.geolocation.watchPosition(
            onPosition,
            null,
            { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 }
        );
    }

    function stopGps() {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        lastLat = null;
        lastLng = null;
    }

    /* ── Periodic position POST ────────────────── */

    function sendPosition() {
        if (lastLat === null || lastLng === null) return;
        post('/api/conductor/posicion', {
            action: 'posicion',
            lat: lastLat,
            lng: lastLng,
        }).catch(function () { /* silencioso */ });
    }

    function startPositionTimer() {
        if (posTimer) return;
        posTimer = setInterval(sendPosition, 15000);
    }

    function stopPositionTimer() {
        if (posTimer) {
            clearInterval(posTimer);
            posTimer = null;
        }
    }

    /* ── Toggle UI ─────────────────────────────── */

    function setOnlineUI(online) {
        var card  = document.getElementById('online-card');
        var label = document.getElementById('online-label');
        var sub   = document.getElementById('online-sub');

        if (!card) return;

        if (online) {
            card.classList.add('online-card--active');
            if (label) label.textContent = 'En línea';
            if (sub)   sub.textContent   = 'Recibirás solicitudes de viaje';
        } else {
            card.classList.remove('online-card--active');
            if (label) label.textContent = 'Fuera de línea';
            if (sub)   sub.textContent   = 'Actívate para recibir viajes';
        }
    }

    function handleToggle(checked) {
        var toggle = document.getElementById('toggle-activo');
        if (toggle) toggle.disabled = true;

        post('/api/conductor/estado', {
            action: 'estado',
            activo: checked ? 1 : 0,
        }).then(function (data) {
            if (data && data.ok) {
                isOnline = checked;
                setOnlineUI(isOnline);
                if (isOnline) {
                    startGps();
                    startPositionTimer();
                    scheduleRefresh(8000);
                } else {
                    stopGps();
                    stopPositionTimer();
                }
            } else {
                if (toggle) toggle.checked = isOnline;
            }
        }).catch(function () {
            if (toggle) toggle.checked = isOnline;
        }).finally(function () {
            if (toggle) toggle.disabled = false;
        });
    }

    /* ── Auto-refresh when online ──────────────── */

    var refreshTimer = null;

    function scheduleRefresh(ms) {
        if (refreshTimer) return;
        refreshTimer = setTimeout(function () {
            refreshTimer = null;
            if (isOnline) location.reload();
        }, ms);
    }

    /* ── Init ──────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {

        var toggle = document.getElementById('toggle-activo');
        if (toggle) {
            toggle.addEventListener('change', function () {
                handleToggle(this.checked);
            });
        }

        if (isOnline) {
            startGps();
            startPositionTimer();
            var hasActiveTrip = document.querySelector('.viaje-card--activo');
            if (!hasActiveTrip) scheduleRefresh(20000);
        }
    });

}());
