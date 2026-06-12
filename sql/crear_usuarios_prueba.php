<?php declare(strict_types=1);
/**
 * Script de utilidad — crear usuarios de prueba con bcrypt correcto.
 * Ejecutar UNA VEZ desde la raíz del proyecto:
 *   php sql/crear_usuarios_prueba.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$name = $_ENV['DB_NAME'] ?? 'movix_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage() . PHP_EOL);
}

// Cliente de prueba — password: Movix1234
$hashCliente = password_hash('Movix1234', PASSWORD_BCRYPT);
$stmt = $pdo->prepare(
    'INSERT IGNORE INTO usuarios (nombre, email, telefono, password, email_verificado)
     VALUES (?, ?, ?, ?, 1)'
);
$stmt->execute(['Cliente Prueba', 'test@movix.com', '99990001', $hashCliente]);
echo "Cliente de prueba creado: test@movix.com / Movix1234" . PHP_EOL;

// Admin de prueba — password: Admin1234
$hashAdmin = password_hash('Admin1234', PASSWORD_BCRYPT);
$stmt = $pdo->prepare(
    'INSERT IGNORE INTO admins (nombre, email, password)
     VALUES (?, ?, ?)'
);
$stmt->execute(['Administrador', 'admin@movix.com', $hashAdmin]);
echo "Admin de prueba creado: admin@movix.com / Admin1234" . PHP_EOL;

// Conductor de prueba — password: Conductor1234
$hashConductor = password_hash('Conductor1234', PASSWORD_BCRYPT);
$stmt = $pdo->prepare(
    'INSERT IGNORE INTO conductores
        (nombre, fecha_nacimiento, identidad, telefono, email, password,
         tipo, estado, activo, email_verificado)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    'Conductor Prueba',
    '1990-01-01',
    '0801199012345',
    '99990002',
    'conductor@movix.com',
    $hashConductor,
    'convencional',
    'aprobado',
    1,
    1,
]);
echo "Conductor de prueba creado: conductor@movix.com / Conductor1234" . PHP_EOL;

echo PHP_EOL . "Listo. Elimina este script en produccion." . PHP_EOL;
