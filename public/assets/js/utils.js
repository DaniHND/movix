// Movix — Utilidades compartidas (Fase 2+)
// Fase 1: archivo de marcador de posición

/**
 * Realiza una petición fetch con JSON y CSRF token.
 * @param {string} url
 * @param {Object} data
 * @returns {Promise<Object>}
 */
async function fetchPost(url, data = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
        },
        body: JSON.stringify(data),
    });
    return res.json();
}
