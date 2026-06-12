'use strict';

/* Movix — Gráficas de línea con Canvas vanilla */

function drawLineChart(canvasId, labels, values, color, labelFmt) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx    = canvas.getContext('2d');
    const dpr    = window.devicePixelRatio || 1;
    const rect   = canvas.getBoundingClientRect();

    canvas.width  = rect.width  * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);

    const W  = rect.width;
    const H  = rect.height;
    const PL = 48, PR = 12, PT = 16, PB = 32;
    const gW = W - PL - PR;
    const gH = H - PT - PB;

    const max = Math.max(...values, 1);
    const min = 0;

    ctx.clearRect(0, 0, W, H);

    /* ── Líneas de cuadrícula ── */
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth   = 1;
    const gridLines = 4;
    for (let i = 0; i <= gridLines; i++) {
        const y = PT + gH - (i / gridLines) * gH;
        ctx.beginPath();
        ctx.moveTo(PL, y);
        ctx.lineTo(PL + gW, y);
        ctx.stroke();

        // Etiqueta eje Y
        const val = (min + (max - min) * (i / gridLines));
        ctx.fillStyle   = '#9ca3af';
        ctx.font        = `11px Inter, sans-serif`;
        ctx.textAlign   = 'right';
        ctx.fillText(labelFmt(val), PL - 4, y + 4);
    }

    /* ── Puntos X, ticks ── */
    const step      = gW / (values.length - 1 || 1);
    const showEvery = Math.max(1, Math.ceil(values.length / 10));

    labels.forEach((lbl, i) => {
        if (i % showEvery !== 0 && i !== values.length - 1) return;
        const x = PL + i * step;
        ctx.fillStyle = '#9ca3af';
        ctx.font      = '10px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(lbl, x, H - 6);
    });

    /* ── Área bajo la curva ── */
    const grad = ctx.createLinearGradient(0, PT, 0, PT + gH);
    grad.addColorStop(0, color + '33');
    grad.addColorStop(1, color + '00');

    ctx.beginPath();
    values.forEach((v, i) => {
        const x = PL + i * step;
        const y = PT + gH - ((v - min) / (max - min)) * gH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.lineTo(PL + (values.length - 1) * step, PT + gH);
    ctx.lineTo(PL, PT + gH);
    ctx.closePath();
    ctx.fillStyle = grad;
    ctx.fill();

    /* ── Línea principal ── */
    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.lineWidth   = 2.5;
    ctx.lineJoin    = 'round';
    values.forEach((v, i) => {
        const x = PL + i * step;
        const y = PT + gH - ((v - min) / (max - min)) * gH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.stroke();

    /* ── Puntos ── */
    values.forEach((v, i) => {
        const x = PL + i * step;
        const y = PT + gH - ((v - min) / (max - min)) * gH;
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, Math.PI * 2);
        ctx.fillStyle   = color;
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth   = 1.5;
        ctx.stroke();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const stats = window.MOVIX_STATS;
    if (!stats || !stats.length) return;

    const labels   = stats.map(r => r.fecha);
    const viajes   = stats.map(r => r.total_viajes);
    const ingresos = stats.map(r => r.ingresos);

    drawLineChart('chart-viajes',   labels, viajes,   '#0F3460', v => Math.round(v));
    drawLineChart('chart-ingresos', labels, ingresos, '#1D9E75', v => 'L.' + (v >= 1000 ? (v/1000).toFixed(1)+'k' : Math.round(v)));
});
