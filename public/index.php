<?php declare(strict_types=1);

// Autoloader de Composer (PHPMailer, phpdotenv, etc.)
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    die('Dependencias no instaladas. Ejecuta: composer install');
}
require_once $autoload;

// Cargar variables de entorno desde .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

// Constantes globales de la aplicación
require_once dirname(__DIR__) . '/config/app.php';

// Sesión con configuración de seguridad
require_once CONFIG_PATH . 'session.php';

// Clase PDO singleton
require_once CONFIG_PATH . 'database.php';

// Clases base (deben cargarse antes que sus subclases)
require_once APP_PATH . 'controllers/Controller.php';
require_once APP_PATH . 'models/Model.php';

// Helpers siempre disponibles
require_once APP_PATH . 'helpers/Auth.php';
require_once APP_PATH . 'helpers/Response.php';
require_once APP_PATH . 'helpers/Mailer.php';

// Autoloader para controllers, models y helpers adicionales
spl_autoload_register(function (string $class): void {
    $dirs = [
        APP_PATH . 'controllers/',
        APP_PATH . 'models/',
        APP_PATH . 'helpers/',
        APP_PATH . 'services/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Cargar tabla de rutas
$routes = require_once CONFIG_PATH . 'routes.php';

// Parsear URL
$url      = trim($_GET['url'] ?? '', '/');
$segments = ($url !== '') ? explode('/', $url) : [''];
$base     = $segments[0];
$params   = array_slice($segments, 1);

// Resolver ruta
if (!array_key_exists($base, $routes)) {
    http_response_code(404);
    require APP_PATH . 'views/errors/404.php';
    exit;
}

[$controllerClass, $method] = $routes[$base];

// Instanciar controller y despachar
$controller = new $controllerClass();
$controller->$method($params);
