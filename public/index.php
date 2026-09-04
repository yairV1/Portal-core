<?php
// ══════════════════════════════════════════════════════════
//  public/index.php — punto de entrada único de todo el sistema
// ══════════════════════════════════════════════════════════

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', ''); // si el proyecto vive en una subcarpeta (ej. /portal-core), ponla aquí

// Cuánto tiempo puede estar una sesión inactiva antes de cerrarse sola
// (ver el bloque de inactividad más abajo).
define('SESION_INACTIVIDAD_SEG', 30 * 60); // 30 minutos

if (session_status() === PHP_SESSION_NONE) {
    // Cookie de sesión más estricta:
    // - httponly: JavaScript no puede leerla (mitiga robo por XSS).
    // - samesite=Lax: no se envía en peticiones cruzadas de otros sitios
    //   (mitiga CSRF), sin romper la navegación normal por enlaces.
    // - secure: solo por HTTPS — condicionado a que la petición ya venga
    //   por HTTPS, para no romper el login en el entorno local (HTTP).
    $porHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $porHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Evita que el navegador guarde en caché las páginas que pasan por acá
// (dashboard, tableros, etc.). Sin esto, después de cerrar sesión el botón
// "atrás" del navegador puede mostrar una copia en caché de una página
// protegida en vez de volver a pedírsela al servidor — y esa nueva petición
// es la que de verdad revisa si la sesión sigue activa (ver los "if
// (empty($_SESSION['usuario_id']))" de PortalController/HomeController).
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Cierra la sesión sola tras un rato de inactividad — evita que una sesión
// olvidada abierta en un equipo compartido quede vigente indefinidamente.
// Va antes del enrutamiento para que ninguna vista protegida llegue a
// pintarse con una sesión que ya debió expirar.
if (!empty($_SESSION['usuario_id'])) {
    if (!empty($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad']) > SESION_INACTIVIDAD_SEG) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . BASE_URL . '/login?expirada=1');
        exit;
    }
    $_SESSION['ultima_actividad'] = time();
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