<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1D9E75">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Movix Conductor">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/icon.svg">
    <title><?= htmlspecialchars($title ?? 'Movix Conductor') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/mobile.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/conductor.css">
</head>
<body class="portal-conductor">

    <header class="conductor-topbar">
        <div class="topbar-logo">
            <span class="topbar-logo-icon">M</span>
            <span class="topbar-logo-text">Movix</span>
        </div>
        <div class="topbar-info">
            <span class="topbar-nombre"><?= htmlspecialchars(Auth::nombre()) ?></span>
        </div>
    </header>

    <main class="conductor-page">
        <?= $content ?>
    </main>

    <nav class="bottom-nav" role="navigation" aria-label="Menú conductor">
        <a href="<?= BASE_URL ?>/conductor"
           class="nav-item <?= ($accion ?? '') === 'inicio' ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Inicio</span>
        </a>
        <a href="<?= BASE_URL ?>/conductor/historial"
           class="nav-item <?= ($accion ?? '') === 'historial' ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>Historial</span>
        </a>
        <a href="<?= BASE_URL ?>/conductor/perfil"
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

    <script>
    window.MOVIX = {
        baseUrl:     '<?= BASE_URL ?>',
        conductorId: <?= Auth::conductorId() ?? 'null' ?>,
        accion:      '<?= htmlspecialchars($accion ?? '') ?>',
        activo:      <?= isset($conductor) && $conductor['activo'] ? 'true' : 'false' ?>,
    };
    </script>
    <script src="<?= BASE_URL ?>/assets/js/gps.js"></script>
    <?php if (!empty($viajeActivo) && in_array($viajeActivo['estado'] ?? '', ['asignado','en_curso'])): ?>
    <script src="<?= BASE_URL ?>/assets/js/chat.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Chat.init(<?= (int)$viajeActivo['id'] ?>, 'conductor');
    });
    </script>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/assets/js/pwa.js"></script>
    <?php if (!empty($_ENV['FIREBASE_API_KEY'])): ?>
    <script>
    // pwa.js ya registró el SW — usamos navigator.serviceWorker.ready para FCM
    if ('serviceWorker' in navigator && 'Notification' in window) {
        Notification.requestPermission().then(function(perm) {
            if (perm !== 'granted') return;
            var script = document.createElement('script');
            script.src = 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js';
            script.onload = function() {
                var s2 = document.createElement('script');
                s2.src = 'https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js';
                s2.onload = function() {
                    navigator.serviceWorker.ready.then(function(reg) {
                        firebase.initializeApp({
                            apiKey:            '<?= htmlspecialchars($_ENV['FIREBASE_API_KEY'] ?? '') ?>',
                            authDomain:        '<?= htmlspecialchars($_ENV['FIREBASE_AUTH_DOMAIN'] ?? '') ?>',
                            projectId:         '<?= htmlspecialchars($_ENV['FIREBASE_PROJECT_ID'] ?? '') ?>',
                            messagingSenderId: '<?= htmlspecialchars($_ENV['FIREBASE_MESSAGING_SENDER_ID'] ?? '') ?>',
                            appId:             '<?= htmlspecialchars($_ENV['FIREBASE_APP_ID'] ?? '') ?>',
                        });
                        var messaging = firebase.messaging();
                        messaging.getToken({ serviceWorkerRegistration: reg }).then(function(token) {
                            if (!token) return;
                            fetch(window.MOVIX.baseUrl + '/api/conductor/fcm-token', {
                                method:      'POST',
                                headers:     { 'Content-Type': 'application/json' },
                                credentials: 'same-origin',
                                body:        JSON.stringify({ action: 'fcm-token', token: token }),
                            });
                            if (reg.active) {
                                reg.active.postMessage({ type: 'FIREBASE_CONFIG', config: firebase.app().options });
                            }
                        });
                    });
                };
                document.head.appendChild(s2);
            };
            document.head.appendChild(script);
        });
    }
    </script>
    <?php endif; ?>
</body>
</html>
