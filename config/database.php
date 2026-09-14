<?php
// Conexión a la base de datos portal_core.
// Credenciales SIEMPRE por variable de entorno — nunca quemadas acá.
// - Con Docker: vienen de docker-compose.yml + tu .env (ver .env.example).
// - Sin Docker: expórtalas en tu shell o cárgalas desde config/local.php
//   antes de este require (config/local.php ya está gitignorado).
// Usa el usuario dedicado 'portal_user' (creado en MySQL), nunca 'root'.

$configLocal = is_file(__DIR__ . '/database.local.php')
    ? require __DIR__ . '/database.local.php'
    : [];

$DB_HOST = getenv('DB_HOST') ?: ($configLocal['host'] ?? '127.0.0.1');
$DB_NAME = getenv('DB_NAME') ?: ($configLocal['name'] ?? 'portal_core');
$DB_USER = getenv('DB_USER') ?: ($configLocal['user'] ?? 'portal_user');
$DB_PASS = getenv('DB_PASS') ?: ($configLocal['pass'] ?? null);

if ($DB_PASS === null) {
    die('Falta configurar DB_PASS. Con Docker: revisa tu .env. Sin Docker: exporta la variable de entorno o defínela en config/local.php antes de este archivo.');
}

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
