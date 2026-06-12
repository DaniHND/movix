'use strict';

/* Movix — Chat cliente ↔ conductor */

const Chat = {
    viajeId:   null,
    rol:       null,  // 'cliente' | 'conductor'
    ultimoId:  0,
    timer:     null,
    open:      false,

    init(viajeId, rol) {
        this.viajeId = viajeId;
        this.rol     = rol;

        const toggle   = document.getElementById('chat-toggle');
        const body     = document.getElementById('chat-body') || document.getElementById('chat-body-conductor');
        const sendBtn  = document.getElementById('chat-send');
        const input    = document.getElementById('chat-input');

        if (!toggle || !body) return;

        toggle.addEventListener('click', () => {
            this.open = !this.open;
            body.classList.toggle('open', this.open);
            if (this.open) {
                this._clearBadge();
                this._scrollBottom();
            }
        });

        sendBtn?.addEventListener('click', () => this._send());
        input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); this._send(); }
        });

        // Cargar mensajes iniciales y arrancar polling
        this._fetch(0);
    },

    _fetch(desde) {
        fetch(`${window.MOVIX.baseUrl}/api/chat/${this.viajeId}?desde=${desde}`, {
            credentials: 'same-origin',
        })
            .then(r => r.json())
            .then(data => {
                if (data.error) return;
                if (data.mensajes?.length) {
                    data.mensajes.forEach(m => this._renderBubble(m));
                    this.ultimoId = data.ultimo_id;
                    if (this.open) this._scrollBottom();
                }
                if (!this.open && data.sin_leer > 0) this._showBadge(data.sin_leer);
                this._schedulePoll();
            })
            .catch(() => this._schedulePoll());
    },

    _send() {
        const input = document.getElementById('chat-input');
        const texto = input?.value.trim();
        if (!texto) return;

        input.value = '';

        fetch(`${window.MOVIX.baseUrl}/api/chat/${this.viajeId}`, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body:        JSON.stringify({ mensaje: texto }),
        })
            .then(r => r.json())
            .then(data => {
                if (data.ok) this._fetch(this.ultimoId);
            })
            .catch(() => {});
    },

    _renderBubble(m) {
        const box = document.getElementById('chat-messages');
        if (!box) return;

        // Evitar duplicados
        if (document.querySelector(`[data-msg-id="${m.id}"]`)) return;

        const isMine = m.de_tipo === this.rol;
        const el     = document.createElement('div');
        el.className = `bubble bubble--${isMine ? 'out' : 'in'}`;
        el.dataset.msgId = m.id;
        el.innerHTML = `${this._esc(m.mensaje)}<span class="bubble-time">${m.hora}</span>`;
        box.appendChild(el);
    },

    _scrollBottom() {
        const box = document.getElementById('chat-messages');
        if (box) box.scrollTop = box.scrollHeight;
    },

    _showBadge(n) {
        const badge = document.getElementById('chat-badge');
        if (badge) { badge.textContent = n > 9 ? '9+' : String(n); badge.hidden = false; }
    },

    _clearBadge() {
        const badge = document.getElementById('chat-badge');
        if (badge) badge.hidden = true;
    },

    _schedulePoll() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this._fetch(this.ultimoId), 4000);
    },

    _esc(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    },
};
