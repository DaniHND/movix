'use strict';

/* Movix — Seguimiento de viaje en tiempo real */

const Tracking = {
    map:             null,
    originMarker:    null,
    destMarker:      null,
    conductorMarker: null,
    pollTimer:       null,
    lastEstado:      null,
    cfg:             null,

    // ── Callback de Google Maps ────────────────────────────

    initMap() {
        this.cfg       = window.MOVIX?.tracking;
        this.lastEstado = this.cfg?.estado ?? null;

        if (!this.cfg) {
            this._showNoBanner();
            return;
        }

        const origin = { lat: this.cfg.latOrigen, lng: this.cfg.lngOrigen };
        const dest   = { lat: this.cfg.latDest,   lng: this.cfg.lngDest   };

        this.map = new google.maps.Map(document.getElementById('mapa-container'), {
            center:           origin,
            zoom:             14,
            disableDefaultUI: true,
            gestureHandling:  'greedy',
            styles: [
                { featureType: 'poi',     stylers: [{ visibility: 'off' }] },
                { featureType: 'transit', stylers: [{ visibility: 'off' }] },
            ],
        });

        // Marcador origen (azul)
        this.originMarker = new google.maps.Marker({
            position: origin,
            map:      this.map,
            title:    'Origen',
            icon: {
                path:        google.maps.SymbolPath.CIRCLE,
                scale:       9,
                fillColor:   '#0F3460',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 3,
            },
            zIndex: 5,
        });

        // Marcador destino (rojo)
        this.destMarker = new google.maps.Marker({
            position: dest,
            map:      this.map,
            title:    'Destino',
            icon: {
                path:        google.maps.SymbolPath.CIRCLE,
                scale:       9,
                fillColor:   '#E94560',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 3,
            },
            zIndex: 5,
        });

        // Dibujar ruta origen → destino
        this._drawRoute(origin, dest);

        // Marcador del conductor si ya tiene posición
        if (this.cfg.conductorLat !== null && this.cfg.conductorLng !== null) {
            this._setOrMoveConductorMarker(this.cfg.conductorLat, this.cfg.conductorLng);
        }

        // Centrar mapa en ambos puntos
        const bounds = new google.maps.LatLngBounds();
        bounds.extend(origin);
        bounds.extend(dest);
        this.map.fitBounds(bounds, { bottom: 300, top: 60, left: 20, right: 20 });

        this._startPolling();
    },

    // ── Sin clave de Google Maps — solo hacer polling ─────

    initNoMap() {
        this.cfg        = window.MOVIX?.tracking;
        this.lastEstado = this.cfg?.estado ?? null;
        this._showNoBanner();
        if (this.cfg) this._startPolling();
    },

    // ── Polling ───────────────────────────────────────────

    _startPolling() {
        if (!this.cfg) return;
        const activos = ['pendiente', 'asignado', 'en_curso'];
        if (!activos.includes(this.lastEstado)) return;
        this._poll();
    },

    _poll() {
        fetch(window.MOVIX.baseUrl + '/api/viaje/' + this.cfg.viajeId, {
            credentials: 'same-origin',
        })
            .then(r => r.json())
            .then(data => {
                if (data.error) return this._schedulePoll(15000);
                this._handleData(data);
            })
            .catch(() => this._schedulePoll(15000));
    },

    _schedulePoll(ms) {
        clearTimeout(this.pollTimer);
        this.pollTimer = setTimeout(() => this._poll(), ms);
    },

    // ── Actualizar UI con datos del servidor ──────────────

    _handleData(data) {
        const estadosActivos = ['pendiente', 'asignado', 'en_curso'];

        // Estado cambió → recargar para mostrar nueva UI
        if (data.estado !== this.lastEstado) {
            window.location.reload();
            return;
        }

        this.lastEstado = data.estado;

        // Mover marcador del conductor
        if (data.conductor_lat !== null && data.conductor_lng !== null && this.map) {
            this._setOrMoveConductorMarker(data.conductor_lat, data.conductor_lng);
        }

        // Actualizar ETA
        this._updateEta(data.eta_min);

        // Actualizar info del conductor si aparece por primera vez
        if (data.conductor_nombre) this._updateConductorInfo(data);

        if (estadosActivos.includes(data.estado)) {
            this._schedulePoll(5000);
        }
    },

    // ── Marcador del conductor ────────────────────────────

    _setOrMoveConductorMarker(lat, lng) {
        const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };

        if (!this.conductorMarker) {
            this.conductorMarker = new google.maps.Marker({
                position: pos,
                map:      this.map,
                title:    'Conductor',
                icon: {
                    path:        google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                    scale:       7,
                    fillColor:   '#1D9E75',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 2,
                    rotation:    0,
                },
                zIndex: 10,
                animation: google.maps.Animation.DROP,
            });
        } else {
            // Animación suave: interpolación en pasos
            this._animateMarker(this.conductorMarker, pos);
        }
    },

    _animateMarker(marker, targetPos) {
        const start     = marker.getPosition();
        const startLat  = start.lat();
        const startLng  = start.lng();
        const steps     = 20;
        let   step      = 0;

        const tick = () => {
            step++;
            const t   = step / steps;
            const lat = startLat + (targetPos.lat - startLat) * t;
            const lng = startLng + (targetPos.lng - startLng) * t;
            marker.setPosition({ lat, lng });
            if (step < steps) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    },

    // ── ETA ───────────────────────────────────────────────

    _updateEta(etaMin) {
        const el = document.getElementById('eta-display');
        if (!el) return;

        if (etaMin === null || etaMin === undefined) {
            el.textContent = 'Calculando tiempo de llegada…';
            return;
        }
        if (etaMin <= 1) {
            el.textContent = 'El conductor está llegando…';
        } else {
            el.textContent = `Llega en aprox. ${etaMin} min`;
        }
    },

    // ── Actualizar tarjeta del conductor ──────────────────

    _updateConductorInfo(data) {
        const card = document.getElementById('conductor-card');
        const msg  = document.getElementById('buscando-msg');
        if (card || !msg) return; // ya está visible o no hay msg

        // Recargar para mostrar la tarjeta del conductor
        window.location.reload();
    },

    // ── Ruta en el mapa ───────────────────────────────────

    _drawRoute(origin, dest) {
        if (!google?.maps?.DirectionsService) return;

        const service  = new google.maps.DirectionsService();
        const renderer = new google.maps.DirectionsRenderer({
            suppressMarkers:  true,
            polylineOptions: {
                strokeColor:   '#0F3460',
                strokeWeight:  4,
                strokeOpacity: 0.75,
            },
        });
        renderer.setMap(this.map);

        service.route({
            origin,
            destination:  dest,
            travelMode:   google.maps.TravelMode.DRIVING,
        }, (result, status) => {
            if (status === 'OK') renderer.setDirections(result);
        });
    },

    // ── Sin API key ───────────────────────────────────────

    _showNoBanner() {
        const banner = document.getElementById('no-key-banner');
        const canvas = document.getElementById('mapa-container');
        if (banner) banner.hidden = false;
        if (canvas) canvas.style.display = 'none';
    },
};

// Callback de Google Maps
function initMaps() { Tracking.initMap(); }

// Arranque sin Google Maps
document.addEventListener('DOMContentLoaded', () => {
    if (!window.MOVIX?.mapsKey) {
        Tracking.initNoMap();
    }
});
