<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0F3460">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/icon.svg">
    <title><?= htmlspecialchars($title ?? 'Panel Admin — Movix') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="portal-admin">

<div class="admin-shell">

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <span class="sidebar-logo-icon">M</span>
                <span class="sidebar-logo-text">Movix Admin</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>/admin"
               class="sidebar-link <?= ($accion ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/conductores"
               class="sidebar-link <?= ($accion ?? '') === 'conductores' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
                Conductores
            </a>
            <a href="<?= BASE_URL ?>/admin/tarifas"
               class="sidebar-link <?= ($accion ?? '') === 'tarifas' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="2" x2="12" y2="22"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
                Tarifas
            </a>
            <a href="<?= BASE_URL ?>/admin/viajes"
               class="sidebar-link <?= ($accion ?? '') === 'viajes' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"/>
                    <circle cx="12" cy="11" r="3"/>
                </svg>
                Viajes
            </a>
            <a href="<?= BASE_URL ?>/admin/clientes"
               class="sidebar-link <?= ($accion ?? '') === 'clientes' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Clientes
            </a>
            <a href="<?= BASE_URL ?>/admin/cupones"
               class="sidebar-link <?= ($accion ?? '') === 'cupones' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/>
                    <path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/>
                    <path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/>
                </svg>
                Cupones
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>/logout" class="sidebar-link sidebar-link--logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Cerrar sesión
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="topbar-menu-btn" id="sidebar-toggle" aria-label="Abrir menú">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6"  x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <span class="topbar-breadcrumb"><?= htmlspecialchars($title ?? '') ?></span>
            <div class="topbar-user">
                <span class="topbar-avatar"><?= strtoupper(mb_substr(Auth::nombre(), 0, 1)) ?></span>
                <span class="topbar-name"><?= htmlspecialchars(Auth::nombre()) ?></span>
            </div>
        </header>

        <main class="admin-content">
            <?= $content ?>
        </main>
    </div>
</div>

<script>
window.MOVIX = { baseUrl: '<?= BASE_URL ?>' };
</script>
<script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>/assets/js/pwa.js"></script>
</body>
</html>
