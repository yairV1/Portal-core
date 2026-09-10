<?php
// Conexión a la base de datos portal_core.
// Usa el usuario dedicado 'portal_user' (creado en MySQL), nunca 'root'.
//
// Las credenciales reales viven en config/database.local.php (gitignoreado,
// una por máquina — mismo patrón que config/local.php para BASE_URL). Si no
// existe todavía, copia config/database.local.php.example. Los valores de
// acá abajo son solo de relleno, nunca conectan a nada real — así este
// archivo puede viajar en git sin exponer ninguna contraseña.
$db = @include ROOT_PATH . '/config/database.local.php';
$db = is_array($db) ? $db : [];

$DB_HOST = $db['host'] ?? 'localhost';
$DB_NAME = $db['name'] ?? 'portal_core';
$DB_USER = $db['user'] ?? 'portal_user';
$DB_PASS = $db['pass'] ?? '';

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
    // El detalle real (puede incluir el usuario/host de la BD) va al log del
    // servidor, nunca a la respuesta — un visitante no necesita saber por
    // qué falló la conexión, y ese detalle le sirve más a un atacante que
    // a quien está viendo la página caída.
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    http_response_code(500);
    die('El sistema no está disponible en este momento. Intenta de nuevo en unos minutos.');
}
