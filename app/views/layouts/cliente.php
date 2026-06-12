<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0F3460">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Movix">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/icon.svg">
    <title><?= htmlspecialchars($title ?? 'Movix') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/mobile.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cliente.css">
</head>
<body class="portal-cliente <?= ($accion ?? '') === 'mapa' || ($accion ?? '') === 'viaje' ? 'mapa-activo' : '' ?>">

    <?= $content ?>

    <button id="pwa-install-btn" hidden
            style="position:fixed;bottom:76px;right:16px;z-index:200;
                   background:#0F3460;color:#fff;border:none;border-radius:24px;
                   padding:10px 18px;font-size:13px;font-weight:600;
                   box-shadow:0 4px 14px rgba(0,0,0,.25);cursor:pointer;
                   display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 16V4M6 10l6 6 6-6"/><rect x="3" y="18" width="18" height="2" rx="1" fill="currentColor" stroke="none"/>
        </svg>
        Instalar app
    </button>
    <nav class="bottom-nav" role="navigation" aria-label="Menú principal">
        <a href="<?= BASE_URL ?>/cliente"
           class="nav-item <?= ($accion ?? '') === 'mapa' ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Inicio</span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/historial"
           class="nav-item <?= ($accion ?? '') === 'historial' ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>Historial</span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/favoritos"
           class="nav-item <?= ($accion ?? '') === 'favoritos' ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
            </svg>
            <span>Favoritos</span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/perfil"
           class="nav-item <?= ($accion ?? '') === 'perfil' ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Perfil</span>
        </a>
        <a href="<?= BASE_URL ?>/logout" class="nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Salir</span>
        </a>
    </nav>

    <!-- Config global para JS -->
    <script>
    window.MOVIX = {
        baseUrl: '<?= BASE_URL ?>',
        mapsKey: '<?= htmlspecialchars($_ENV['GOOGLE_MAPS_KEY'] ?? '', ENT_QUOTES) ?>',
        userId:  <?= Auth::userId() ?>,
        accion:  '<?= htmlspecialchars($accion ?? '') ?>',
    <?php if (($accion ?? '') === 'viaje' && !empty($viaje)): ?>
        tracking: {
            viajeId:      <?= (int)$viaje['id'] ?>,
            estado:       '<?= htmlspecialchars($viaje['estado']) ?>',
            latOrigen:    <?= (float)$viaje['lat_origen'] ?>,
            lngOrigen:    <?= (float)$viaje['lng_origen'] ?>,
            latDest:      <?= (float)$viaje['lat_destino'] ?>,
            lngDest:      <?= (float)$viaje['lng_destino'] ?>,
            conductorLat: <?= $viaje['conductor_lat'] !== null ? (float)$viaje['conductor_lat'] : 'null' ?>,
            conductorLng: <?= $viaje['conductor_lng'] !== null ? (float)$viaje['conductor_lng'] : 'null' ?>,
        },
    <?php endif; ?>
    };
    </script>

    <?php if (!empty($_ENV['GOOGLE_MAPS_KEY'])): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($_ENV['GOOGLE_MAPS_KEY'], ENT_QUOTES) ?>&libraries=places&callback=initMaps" async defer></script>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/pwa.js"></script>
    <?php if (($accion ?? '') === 'viaje'): ?>
    <script src="<?= BASE_URL ?>/assets/js/tracking.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/chat.js"></script>
    <?php if (!empty($viaje['conductor_id']) && in_array($viaje['estado'] ?? '', ['asignado','en_curso'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Chat.init(<?= (int)$viaje['id'] ?>, 'cliente');
    });
    </script>
    <?php endif; ?>
    <?php else: ?>
    <script src="<?= BASE_URL ?>/assets/js/maps.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/viaje.js"></script>
    <?php endif; ?>
</body>
</html>
