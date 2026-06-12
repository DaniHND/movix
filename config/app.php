<?php declare(strict_types=1);

define('ROOT_PATH',   dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH',    ROOT_PATH . 'app'    . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', PUBLIC_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOAD_URL',  BASE_URL . '/uploads');
define('LOG_PATH',    ROOT_PATH . 'logs'   . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);

define('APP_NAME',  'Movix');
define('APP_ENV',   $_ENV['APP_ENV']  ?? 'development');
define('BASE_URL',  rtrim($_ENV['APP_URL'] ?? 'http://localhost/Movix/public', '/'));

// Zona horaria del servidor (ajustar según región)
date_default_timezone_set('America/Tegucigalpa');

// En desarrollo mostrar errores, en producción solo logearlos
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

ini_set('error_log', LOG_PATH . 'php_errors.log');
