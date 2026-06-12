'use strict';

// ── Sidebar toggle (mobile) ──────────────────────────────────────
const sidebar = document.getElementById('admin-sidebar');
const overlay = document.getElementById('sidebar-overlay');
const toggle  = document.getElementById('sidebar-toggle');

toggle?.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('visible');
});

overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('visible');
});

// ── Tarifa inline edit ───────────────────────────────────────────
document.querySelectorAll('[data-tarifa-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
        const id  = btn.dataset.tarifaToggle;
        const row = document.getElementById('tarifa-form-' + id);
        if (!row) return;

        const isOpen = row.style.display === 'table-row';

        // Cerrar todos
        document.querySelectorAll('.tarifa-edit-row').forEach(r => {
            r.style.display = 'none';
        });
        document.querySelectorAll('[data-tarifa-toggle]').forEach(b => {
            b.textContent = 'Editar';
        });

        if (!isOpen) {
            row.style.display = 'table-row';
            btn.textContent = 'Cancelar';
        }
    });
});
