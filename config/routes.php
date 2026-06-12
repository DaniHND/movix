<?php declare(strict_types=1);

/**
 * Tabla de rutas.
 * Clave: primer segmento de la URL (después de BASE_URL/).
 * Valor: [ControllerClass, method]
 *
 * El método del controller recibe array $params con los segmentos restantes.
 * Ejemplo: /verificar/abc123 → AuthController::verificarEmail(['abc123'])
 */
return [
    ''          => ['AuthController', 'mostrarLogin'],
    'login'     => ['AuthController', 'login'],
    'registro'  => ['AuthController', 'registro'],
    'verificar' => ['AuthController', 'verificarEmail'],
    'recuperar' => ['AuthController', 'recuperar'],
    'logout'    => ['AuthController', 'cerrarSesion'],

    'cliente'   => ['ClienteController',   'dispatch'],
    'conductor'          => ['ConductorController', 'dispatch'],
    'registro-conductor' => ['ConductorController', 'registro'],
    'admin'     => ['AdminController',     'dispatch'],
    'api'       => ['ApiController',       'dispatch'],
];
