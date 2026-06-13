'use strict';

const Maps = {
    map: null,
    userMarker: null,
    taxiMarkers: [],
    directionsService: null,
    directionsRenderer: null,
    destAutocomplete: null,
    originAutocomplete: null,

    init() {
        if (typeof google === 'undefined' || !google.maps) {
            this._showNoKey();
            return;
        }

        const tegucigalpa = { lat: 14.0818, lng: -87.2068 };

        this.map = new google.maps.Map(document.getElementById('mapa-container'), {
            center: tegucigalpa,
            zoom: 14,
            disableDefaultUI: true,
            gestureHandling: 'greedy',
            styles: this._mapStyles(),
        });

        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: { strokeColor: '#0F3460', strokeWeight: 5, strokeOpacity: 0.85 },
        });
        this.directionsRenderer.setMap(this.map);

        this._setupAutocomplete();
        this._getUserLocation();
        this._loadTaxiMarkers();
    },

    _showNoKey() {
        const banner = document.getElementById('no-key-banner');
        const canvas = document.getElementById('mapa-container');
        if (banner) banner.hidden = false;
        if (canvas) canvas.style.display = 'none';
    },

    _getUserLocation() {
        if (!navigator.geolocation) return;

        navigator.geolocation.getCurrentPosition(
            ({ coords: { latitude: lat, longitude: lng } }) => {
                this.map.setCenter({ lat, lng });
                this.map.setZoom(15);

                this.userMarker = new google.maps.Marker({
                    position: { lat, lng },
                    map: this.map,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 10,
                        fillColor: '#0F3460',
                        fillOpacity: 1,
                        strokeColor: 'white',
                        strokeWeight: 3,
                    },
                    title: 'Tu ubicación',
                    zIndex: 10,
                });

                // Auto-fill origin field
                const origenInput = document.getElementById('origen-input');
                if (origenInput && !origenInput.value) {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            origenInput.value = results[0].formatted_address;
                        }
                    });
                    origenInput.dataset.lat = String(lat);
                    origenInput.dataset.lng = String(lng);
                }
            },
            () => { /* geolocation denegada — usar La Ceiba por defecto */ }
        );
    },

    _setupAutocomplete() {
        const opts = {
            componentRestrictions: { country: 'hn' },
            fields: ['formatted_address', 'geometry'],
        };

        const destEl = document.getElementById('destino-input');
        if (destEl && google.maps.places) {
            this.destAutocomplete = new google.maps.places.Autocomplete(destEl, opts);
            this.destAutocomplete.addListener('place_changed', () => {
                const place = this.destAutocomplete.getPlace();
                if (!place.geometry) return;
                destEl.dataset.lat = String(place.geometry.location.lat());
                destEl.dataset.lng = String(place.geometry.location.lng());
                if (typeof Viaje !== 'undefined') Viaje.onDestinoSelected();
            });
        }

        const origenEl = document.getElementById('origen-input');
        if (origenEl && google.maps.places) {
            this.originAutocomplete = new google.maps.places.Autocomplete(origenEl, opts);
            this.originAutocomplete.addListener('place_changed', () => {
                const place = this.originAutocomplete.getPlace();
                if (!place.geometry) return;
                origenEl.dataset.lat = String(place.geometry.location.lat());
                origenEl.dataset.lng = String(place.geometry.location.lng());
                if (typeof Viaje !== 'undefined') Viaje.onOrigenChanged();
            });
        }
    },

    calculateRoute(origin, destination, callback) {
        if (!this.directionsService) return;

        this.directionsService.route(
            { origin, destination, travelMode: google.maps.TravelMode.DRIVING },
            (result, status) => {
                if (status !== 'OK') {
                    console.warn('Directions API:', status);
                    return;
                }
                this.directionsRenderer.setDirections(result);
                const leg = result.routes[0].legs[0];
                const distKm = leg.distance.value / 1000;
                const durMin = Math.round(leg.duration.value / 60);

                // Ajustar mapa a la ruta
                const bounds = new google.maps.LatLngBounds();
                bounds.extend(leg.start_location);
                bounds.extend(leg.end_location);
                this.map.fitBounds(bounds, { bottom: 320 });

                callback({ distKm, durMin });
            }
        );
    },

    async _loadTaxiMarkers() {
        try {
            const res = await fetch(window.MOVIX.baseUrl + '/api/conductores');
            if (!res.ok) return;
            const data = await res.json();

            this.taxiMarkers.forEach(m => m.setMap(null));
            this.taxiMarkers = [];

            (data.conductores || []).forEach(c => {
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(c.lat_actual), lng: parseFloat(c.lng_actual) },
                    map: this.map,
                    title: c.nombre,
                    icon: {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                        scale: 5,
                        fillColor: c.tipo === 'vip' ? '#E94560' : '#0F3460',
                        fillOpacity: 1,
                        strokeColor: 'white',
                        strokeWeight: 1.5,
                    },
                });
                this.taxiMarkers.push(marker);
            });
        } catch (_) { /* sin marcadores si la API falla */ }
    },

    centerOnUser() {
        if (this.userMarker) {
            const pos = this.userMarker.getPosition();
            if (pos) { this.map.panTo(pos); this.map.setZoom(15); }
        } else {
            this._getUserLocation();
        }
    },

    _mapStyles() {
        return [
            { featureType: 'poi',     stylers: [{ visibility: 'off' }] },
            { featureType: 'transit', stylers: [{ visibility: 'off' }] },
            { elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
        ];
    },
};

// Callback invocado por la API de Google Maps al cargar
function initMaps() { Maps.init(); }

// Sin clave de API: mostrar banner
document.addEventListener('DOMContentLoaded', () => {
    if (!window.MOVIX?.mapsKey) {
        Maps._showNoKey();
    }
});
