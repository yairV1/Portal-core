<?php
// ══════════════════════════════════════════════════════════
//  public/index.php — punto de entrada único de todo el sistema
// ══════════════════════════════════════════════════════════

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', ''); // si el proyecto vive en una subcarpeta (ej. /portal-core), ponla aquí

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// e(): escapa texto antes de imprimirlo en HTML (evita XSS)
function e(?string $texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

// Conexión a la base de datos (deja $pdo listo para todo el proyecto)
require ROOT_PATH . '/config/database.php';

// Token CSRF: uno por sesión, para proteger los formularios (login, etc.)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// ── Enrutamiento ──
// routes/web.php debe devolver un arreglo ['/ruta' => 'ArchivoControlador.php']
$rutas = require ROOT_PATH . '/routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

// Si el proyecto vive en subcarpeta, la quitamos del inicio de la URI
if (BASE_URL !== '' && strpos($uri, BASE_URL) === 0) {
    $uri = substr($uri, strlen(BASE_URL));
    if ($uri === '') {
        $uri = '/';
    }
}

if (isset($rutas[$uri])) {
    require ROOT_PATH . '/app/Controllers/' . $rutas[$uri];
} else {
    http_response_code(404);
    echo '<h1>404 — Página no encontrada</h1><p><a href="' . BASE_URL . '/login">Volver al login</a></p>';
}