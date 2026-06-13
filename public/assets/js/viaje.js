'use strict';

const Viaje = {
    distanciaKm:    0,
    duracionMin:    0,
    tipoServicio:   'convencional',
    pasajeros:      1,

    cuponId:  0,
    descPct:  0,

    init() {
        this._bindSearch();
        this._bindServiceCards();
        this._bindCounter();
        this._bindSolicitar();
        this._bindLocationBtn();
        this._bindCupon();
    },

    // ── Búsqueda de destino ─────────────────────────

    _bindSearch() {
        const destinoInput = document.getElementById('destino-input');
        if (!destinoInput) return;

        // Si no hay Maps API, también conectar el blur/enter del input
        destinoInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !window.MOVIX?.mapsKey) {
                e.preventDefault();
                this._calcularSinMaps(destinoInput.value);
            }
        });
    },

    onDestinoSelected() {
        const destEl   = document.getElementById('destino-input');
        const origenEl = document.getElementById('origen-input');

        if (!destEl?.dataset.lat) return;

        const destLat = parseFloat(destEl.dataset.lat);
        const destLng = parseFloat(destEl.dataset.lng);

        // Actualizar label destino en panel ruta
        const label = document.getElementById('destino-label');
        if (label) label.textContent = destEl.value;

        if (Maps.map && Maps.directionsService) {
            // Con Google Maps
            const origLat = origenEl?.dataset.lat ? parseFloat(origenEl.dataset.lat) : null;
            const origLng = origenEl?.dataset.lng ? parseFloat(origenEl.dataset.lng) : null;

            const origin = origLat !== null
                ? { lat: origLat, lng: origLng }
                : (origenEl?.value || 'La Ceiba, Atlántida, Honduras');

            const destination = { lat: destLat, lng: destLng };

            Maps.calculateRoute(origin, destination, ({ distKm, durMin }) => {
                this.distanciaKm = distKm;
                this.duracionMin = durMin;
                this._mostrarPanelRuta();
                this._calcularPrecios();
            });
        } else {
            // Sin Maps API: usar fórmula Haversine
            const origLat = origenEl?.dataset.lat ? parseFloat(origenEl.dataset.lat) : 14.0818;
            const origLng = origenEl?.dataset.lng ? parseFloat(origenEl.dataset.lng) : -87.2068;

            this.distanciaKm = this._haversine(origLat, origLng, destLat, destLng);
            this.duracionMin = Math.round(this.distanciaKm * 2.8);
            this._mostrarPanelRuta();
            this._calcularPrecios();
        }
    },

    onOrigenChanged() {
        if (this.distanciaKm > 0) this.onDestinoSelected();
    },

    _calcularSinMaps(texto) {
        // Demo: si el usuario no tiene Maps y escribe algo, usamos distancia de ejemplo
        if (!texto.trim()) return;
        const destEl = document.getElementById('destino-input');
        if (destEl) {
            destEl.dataset.lat = '14.0900';
            destEl.dataset.lng = '-87.1950';
        }
        this.onDestinoSelected();
    },

    _mostrarPanelRuta() {
        const bsBuscar = document.getElementById('bs-buscar');
        const bsRuta   = document.getElementById('bs-ruta');

        if (bsBuscar) bsBuscar.hidden = true;
        if (bsRuta)   bsRuta.hidden   = false;

        const distEl = document.getElementById('ruta-distancia');
        const durEl  = document.getElementById('ruta-duracion');
        if (distEl) distEl.textContent = `${this.distanciaKm.toFixed(1)} km`;
        if (durEl)  durEl.textContent  = `~${this.duracionMin} min`;

        // FAB más arriba cuando hay panel ruta (que es más alto)
        const fab = document.querySelector('.location-fab');
        if (fab) fab.style.bottom = `calc(var(--navbar-height) + 410px)`;
    },

    // ── Selector de servicio ────────────────────────

    _bindServiceCards() {
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.service-card').forEach(c =>
                    c.classList.remove('service-card--selected')
                );
                card.classList.add('service-card--selected');
                this.tipoServicio = card.dataset.tipo;
            });
        });
    },

    // ── Precio ─────────────────────────────────────

    async _calcularPrecios() {
        for (const tipo of ['convencional', 'vip']) {
            try {
                const url = `${window.MOVIX.baseUrl}/api/precio?tipo=${tipo}&km=${this.distanciaKm.toFixed(2)}&pasajeros=${this.pasajeros}`;
                const res  = await fetch(url);
                const data = await res.json();

                const el = document.getElementById(`precio-${tipo}`);
                if (el && data.precio_total != null) {
                    let precio = parseFloat(data.precio_total);
                    if (this.descPct > 0) {
                        precio = precio * (1 - this.descPct / 100);
                    }
                    el.textContent = `L. ${precio.toFixed(2)}`;
                }
            } catch (_) { /* mantener "L. —" */ }
        }
    },

    // ── Contador pasajeros ──────────────────────────

    _bindCounter() {
        const menosBtn = document.getElementById('btn-menos');
        const masBtn   = document.getElementById('btn-mas');
        const numEl    = document.getElementById('num-pasajeros');

        menosBtn?.addEventListener('click', () => {
            if (this.pasajeros > 1) {
                this.pasajeros--;
                if (numEl) numEl.textContent = this.pasajeros;
                if (this.distanciaKm > 0) this._calcularPrecios();
            }
        });

        masBtn?.addEventListener('click', () => {
            if (this.pasajeros < 4) {
                this.pasajeros++;
                if (numEl) numEl.textContent = this.pasajeros;
                if (this.distanciaKm > 0) this._calcularPrecios();
            }
        });
    },

    // ── Solicitar viaje ─────────────────────────────

    _bindSolicitar() {
        document.getElementById('btn-solicitar')?.addEventListener('click', () =>
            this._solicitar()
        );
    },

    async _solicitar() {
        const destEl   = document.getElementById('destino-input');
        const origenEl = document.getElementById('origen-input');

        if (!destEl?.dataset.lat) {
            alert('Selecciona un destino válido.');
            return;
        }

        if (this.distanciaKm <= 0) {
            alert('Primero calcula la distancia seleccionando un destino.');
            return;
        }

        const csrf = document.getElementById('csrf-token-val')?.value;

        const body = new URLSearchParams({
            csrf_token:         csrf || '',
            tipo_servicio:      this.tipoServicio,
            num_pasajeros:      String(this.pasajeros),
            ida_y_regreso:      document.getElementById('ida-vuelta')?.checked ? '1' : '0',
            lat_origen:         origenEl?.dataset.lat || '14.0818',
            lng_origen:         origenEl?.dataset.lng || '-87.2068',
            direccion_origen:   origenEl?.value || '',
            lat_destino:        destEl.dataset.lat,
            lng_destino:        destEl.dataset.lng,
            direccion_destino:  destEl.value,
            distancia_km:       this.distanciaKm.toFixed(2),
            duracion_min:       String(this.duracionMin),
            cupon_id:           String(this.cuponId || 0),
            descuento_pct:      String(this.descPct || 0),
        });

        this._showLoading(true);

        try {
            const res  = await fetch(window.MOVIX.baseUrl + '/cliente/solicitar', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body,
            });
            const data = await res.json();

            if (data.viaje_id) {
                window.location.href = window.MOVIX.baseUrl + '/cliente/viaje/' + data.viaje_id;
            } else {
                this._showLoading(false);
                alert(data.error || 'Error al solicitar el viaje. Intenta de nuevo.');
            }
        } catch (_) {
            this._showLoading(false);
            alert('Error de conexión. Revisa tu internet e intenta de nuevo.');
        }
    },

    _showLoading(show) {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.hidden = !show;
    },

    // ── Cupón ───────────────────────────────────────

    _bindCupon() {
        document.getElementById('btn-cupon')?.addEventListener('click', () => this._validarCupon());
        document.getElementById('cupon-input')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); this._validarCupon(); }
        });
    },

    async _validarCupon() {
        const input  = document.getElementById('cupon-input');
        const msg    = document.getElementById('cupon-msg');
        const codigo = input?.value.trim().toUpperCase();

        if (!codigo) return;
        if (msg) { msg.textContent = ''; msg.className = ''; }

        try {
            const res  = await fetch(`${window.MOVIX.baseUrl}/api/cupon?codigo=${encodeURIComponent(codigo)}`);
            const data = await res.json();

            if (data.ok) {
                this.cuponId = data.cupon_id;
                this.descPct = data.descuento_pct;
                document.getElementById('cupon-id-val').value      = data.cupon_id;
                document.getElementById('descuento-pct-val').value = data.descuento_pct;
                if (msg) { msg.textContent = `✓ Cupón aplicado — ${data.descuento_pct}% de descuento`; msg.className = 'cupon-ok'; }
                this._calcularPrecios(); // refrescar precios con descuento
            } else {
                this.cuponId = 0; this.descPct = 0;
                document.getElementById('cupon-id-val').value = '';
                document.getElementById('descuento-pct-val').value = '0';
                if (msg) { msg.textContent = data.error || 'Cupón inválido'; msg.className = 'cupon-err'; }
            }
        } catch (_) {
            if (msg) { msg.textContent = 'Error de conexión'; msg.className = 'cupon-err'; }
        }
    },

    // ── Botón mi ubicación ──────────────────────────

    _bindLocationBtn() {
        document.getElementById('btn-mi-ubicacion')
            ?.addEventListener('click', () => {
                if (typeof Maps !== 'undefined') Maps.centerOnUser();
            });
    },

    // ── Utilidades ──────────────────────────────────

    _haversine(lat1, lng1, lat2, lng2) {
        const R    = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a    = Math.sin(dLat / 2) ** 2
                   + Math.cos(lat1 * Math.PI / 180)
                   * Math.cos(lat2 * Math.PI / 180)
                   * Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    },
};

document.addEventListener('DOMContentLoaded', () => Viaje.init());
