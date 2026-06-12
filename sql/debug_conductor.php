<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

$pdo = new PDO(
    'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') . ';dbname=' . ($_ENV['DB_NAME'] ?? 'movix_db') . ';charset=utf8mb4',
    $_ENV['DB_USER'] ?? 'root',
    $_ENV['DB_PASS'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$email = 'conductor@movix.com';
$pass  = 'Conductor1234';

$stmt = $pdo->prepare('SELECT id, nombre, email, password, estado, activo, email_verificado FROM conductores WHERE email = ?');
$stmt->execute([$email]);
$c = $stmt->fetch();

if (!$c) {
    echo "❌ Conductor NO existe en la BD. Ejecuta: php sql/crear_usuarios_prueba.php\n";
} else {
    echo "✅ Conductor encontrado: #{$c['id']} — {$c['nombre']}\n";
    echo "   estado          : {$c['estado']}\n";
    echo "   activo          : {$c['activo']}\n";
    echo "   email_verificado: {$c['email_verificado']}\n";
    echo "   password_verify : " . (password_verify($pass, $c['password']) ? '✅ correcto' : '❌ FALLO') . "\n";
}
