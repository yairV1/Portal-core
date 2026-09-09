<?php
// ══════════════════════════════════════════════════════════
//  public/index.php — punto de entrada único de todo el sistema
// ══════════════════════════════════════════════════════════

define('ROOT_PATH', dirname(__DIR__));

// BASE_URL depende de cómo sirvas el proyecto EN TU MÁQUINA (vhost en la
// raíz vs. acceso por subcarpeta) — por eso no vive acá, sino en
// config/local.php, que está en .gitignore: cada máquina tiene el suyo y
// ningún pull/merge lo vuelve a pisar (ver docs/entorno-local.md). Si no
// existe ese archivo todavía, se asume '' (vhost en la raíz — la config
// recomendada y documentada).
$baseUrlLocal = @include ROOT_PATH . '/config/local.php';
define('BASE_URL', is_string($baseUrlLocal) ? $baseUrlLocal : '');

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

function mostrar_error(int $codigo): void {
    $errores = [
        400 => ['Solicitud incorrecta', 'No pudimos entender la solicitud', 'Revisa los datos enviados e inténtalo nuevamente.', BASE_URL . '/login', 'Ir al inicio de sesión'],
        401 => ['Acceso no autorizado', 'Necesitas iniciar sesión', 'Tu sesión no está activa o ya venció. Inicia sesión para continuar.', BASE_URL . '/login', 'Iniciar sesión'],
        403 => ['Acceso restringido', 'No tienes permiso para ver esta página', 'Si crees que es un error, solicita acceso al administrador del portal.', BASE_URL . '/', 'Volver al portal'],
        404 => ['Página no encontrada', 'Esta ruta no existe', 'La dirección puede haber cambiado o estar escrita de forma incorrecta.', BASE_URL . '/login', 'Volver al inicio de sesión'],
        500 => ['Error del servidor', 'Algo no salió como esperábamos', 'El sistema encontró un problema interno. Inténtalo de nuevo en unos momentos.', BASE_URL . '/login', 'Volver al inicio de sesión'],
    ];
    [$etiqueta, $titulo, $mensaje, $enlace, $textoEnlace] = $errores[$codigo] ?? $errores[500];
    http_response_code($codigo);
    require ROOT_PATH . '/app/Views/Errors/' . $codigo . '.php';
}

// v(): agrega "?v=<fecha de modificación>" a una ruta de asset (css/js).
// Las páginas PHP ya tienen Cache-Control: no-store, pero los .css/.js
// estáticos no — el navegador los cachea con sus propias reglas, así que
// sin esto una copia vieja puede quedar pegada en el navegador después de
// editar el archivo (ej.: un botón que "no responde" porque corre el JS
// de antes del cambio). $rutaRelativa empieza con "/", ej. "/assets/x.js".
function v(string $rutaRelativa): string {
    $archivo = ROOT_PATH . '/public' . $rutaRelativa;
    $version = is_file($archivo) ? filemtime($archivo) : time();
    return BASE_URL . $rutaRelativa . '?v=' . $version;
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
    mostrar_error(404);
}
