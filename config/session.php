<?php declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');

    if (defined('APP_ENV') && APP_ENV === 'production') {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

// Token CSRF — regenerar si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar timeout de sesión
$timeout_admin    = 4 * 3600;   // 4 horas para admin
$timeout_usuario  = 8 * 3600;   // 8 horas para clientes y conductores

$tiene_sesion = isset($_SESSION['usuario_id'])
             || isset($_SESSION['conductor_id'])
             || isset($_SESSION['admin_id']);

if ($tiene_sesion && isset($_SESSION['last_activity'])) {
    $limite = isset($_SESSION['admin_id']) ? $timeout_admin : $timeout_usuario;

    if ((time() - $_SESSION['last_activity']) > $limite) {
        session_unset();
        session_destroy();
        $base = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $base . '/login?timeout=1');
        exit;
    }
}

if ($tiene_sesion) {
    $_SESSION['last_activity'] = time();
}
