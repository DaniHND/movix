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
    <title><?= htmlspecialchars($title ?? 'Movix') ?> — Movix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/mobile.css">
</head>
<body class="portal-auth">
    <main class="auth-main">
        <?= $content ?>
    </main>
    <script src="<?= BASE_URL ?>/assets/js/pwa.js"></script>
</body>
</html>
