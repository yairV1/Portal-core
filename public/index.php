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

// Headers de seguridad básicos — van acá (con header(), no en .htaccess)
// para que apliquen sin importar si mod_headers está habilitado en Apache.
header('X-Content-Type-Options: nosniff');       // no "adivinar" el tipo de un archivo distinto al declarado
header('X-Frame-Options: SAMEORIGIN');            // nadie puede meter el portal en un <iframe> de otro sitio (clickjacking)
header('Referrer-Policy: strict-origin-when-cross-origin'); // no filtra la URL completa (con tokens en query) a sitios externos

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

// usuario_admin_de(): ¿puede el usuario en sesión administrar (crear/subir/
// eliminar carpetas y documentos de) esta dirección puntual? Ver migración
// 036_roles_por_direccion.sql — 'admin' sigue siendo global (todo el
// portal, igual que siempre); 'admin_direccion' solo puede administrar la
// única dirección a la que quedó atado al crear su usuario (Panel de
// Usuarios, ver UsuariosController.php). $direccionId en null (ej. antes
// de resolver a qué dirección pertenece algo) nunca autoriza a un
// admin_direccion, solo a un admin global.
function usuario_admin_de(?int $direccionId): bool {
    $rol = $_SESSION['usuario_rol'] ?? '';
    if ($rol === 'admin') return true;
    if ($rol === 'admin_direccion' && $direccionId !== null) {
        return (int) ($_SESSION['usuario_direccion_id'] ?? 0) === $direccionId;
    }
    return false;
}

// usuario_area_asignada(): a qué dirección quedó atado este usuario para
// EFECTOS DE VER contenido — no solo de administrarlo (ver
// usuario_admin_de() arriba). null = puede ver todas las direcciones (el
// admin global, o cualquiera sin área asignada — hoy la mayoría de
// 'usuario', mismo comportamiento de siempre). Un entero = solo puede ver
// esa dirección puntual; cualquier otra le devuelve 403 (ver
// PortalController.php/CarpetaController.php/DocumentoController.php) y no
// aparece como tarjeta en "Todos los módulos" (ver ModulosController.php).
// La columna usuarios.direccion_id ya existía para 'admin_direccion' (ver
// migración 036_roles_por_direccion.sql); esto solo deja que un 'usuario'
// normal también la tenga (UsuariosController.php) — no hace falta
// migración nueva, la columna ya admitía NULL para cualquier rol.
function usuario_area_asignada(): ?int {
    if (($_SESSION['usuario_rol'] ?? '') === 'admin') return null;
    $id = $_SESSION['usuario_direccion_id'] ?? null;
    return $id !== null ? (int) $id : null;
}

// ── Permisos por rol (ver PermisosController.php y config/modulos.php) ──
// La lista de módulos vetables vive en config/modulos.php y los vetos en
// permisos_rol_modulo (migración 045 — DEBE aplicarse ANTES que este código:
// sin esa tabla, los roles admin_direccion/usuario quedan sin acceso a los
// módulos, ver usuario_modulos_vetados()).
function modulos_config(): array {
    static $config = null;
    return $config ??= require ROOT_PATH . '/config/modulos.php';
}

// ¿A qué módulo vetable pertenece esta URL? Prefijo más largo, respetando el
// límite de segmento: '/sgi' y '/sgi/carpetas/descargar' son del módulo 'sgi',
// '/sgix' no. null = la URL no es de ningún módulo vetable (/, /perfil,
// /usuarios, /administracion, /permisos-por-rol, /documentos, /editor...).
function modulo_de_ruta(string $uri): ?string {
    $mejor = null;
    $largo = -1;
    foreach (modulos_config() as $clave => $modulo) {
        foreach ($modulo['rutas'] as $ruta) {
            if (($uri === $ruta || str_starts_with($uri, $ruta . '/')) && strlen($ruta) > $largo) {
                $mejor = $clave;
                $largo = strlen($ruta);
            }
        }
    }
    return $mejor;
}

// Claves de los módulos vetados al rol de la sesión. Solo para admin_direccion
// y usuario (el admin global nunca se veta: devuelve []). Falla CERRADO: si la
// consulta no se puede hacer (p. ej. falta la migración 045) se registra con
// error_log y se devuelve null, y quien llama trata null como "todo vetado" —
// nunca como "nada vetado".
function usuario_modulos_vetados(): ?array {
    global $pdo;
    static $cache = [];
    $rol = $_SESSION['usuario_rol'] ?? '';
    if (!in_array($rol, ['admin_direccion', 'usuario'], true)) return [];
    if (!array_key_exists($rol, $cache)) {
        try {
            $stmt = $pdo->prepare('SELECT modulo FROM permisos_rol_modulo WHERE rol = :rol');
            $stmt->execute([':rol' => $rol]);
            $cache[$rol] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log('Permisos por rol: no se pudo leer permisos_rol_modulo (¿falta aplicar la migración 045?): ' . $e->getMessage());
            $cache[$rol] = null;
        }
    }
    return $cache[$rol];
}

// usuario_puede_accion(): ¿el rol de la sesión actual tiene vetada esta
// acción puntual (crear/subir/importar/eliminar...)? Ver PermisosController.php
// / migración 045_permisos_rol_acciones.sql. Complementa a
// usuario_puede_ver_ruta() (que solo oculta módulos completos) para poder
// ajustar qué puede HACER cada rol dentro de un módulo al que sí tiene
// acceso — esto se suma a usuario_admin_de()/usuario_area_asignada(), que
// siguen aplicando primero (la dirección/área asignada nunca se salta).
// Mismo modelo "solo excepciones": sin fila, la acción está permitida. El
// admin global nunca se autolimitea.
function usuario_puede_accion(string $accionClave): bool {
    global $pdo;
    $rol = $_SESSION['usuario_rol'] ?? '';
    if ($rol === 'admin') return true;
    $stmt = $pdo->prepare('SELECT 1 FROM permisos_rol_acciones_negadas WHERE rol = :rol AND accion_clave = :accion');
    $stmt->execute([':rol' => $rol, ':accion' => $accionClave]);
    if ($stmt->fetchColumn()) return false;

    // Mismo criterio que usuario_puede_ver_ruta(): el cargo también puede
    // negar una acción encima del rol (ver migración 046_catalogo_cargos.sql).
    $cargoId = $_SESSION['usuario_cargo_id'] ?? null;
    if ($cargoId !== null) {
        $stmt = $pdo->prepare('SELECT 1 FROM permisos_cargo_acciones_negadas WHERE cargo_id = :cargo AND accion_clave = :accion');
        $stmt->execute([':cargo' => $cargoId, ':accion' => $accionClave]);
        if ($stmt->fetchColumn()) return false;
    }

    return true;
}

// ¿El rol de la sesión puede ver este módulo (clave de config/modulos.php)?
// Modelo "solo excepciones": sin fila en permisos_rol_modulo está permitido;
// esto solo puede QUITAR acceso, nunca dar uno que la propia dirección/área ya
// no permitiera (usuario_area_asignada() sigue aplicando aparte).
function usuario_puede_ver_modulo(string $clave): bool {
    $vetados = usuario_modulos_vetados();
    if ($vetados === null) return false;
    if (in_array($clave, $vetados, true)) return false;

    global $pdo;
    $modulo = modulos_config()[$clave] ?? null;
    $ruta = $modulo['rutas'][0] ?? null;
    if ($ruta === null) return false;
    $stmt = $pdo->prepare('SELECT id FROM nav_items WHERE parent_id IS NULL AND ruta = :ruta LIMIT 1');
    $stmt->execute([':ruta' => $ruta]);
    $navItemId = $stmt->fetchColumn();
    $cargoId = $_SESSION['usuario_cargo_id'] ?? null;
    if ($navItemId && $cargoId !== null) {
        $stmt = $pdo->prepare('SELECT 1 FROM permisos_cargo_negados WHERE nav_item_id = :id AND cargo_id = :cargo');
        $stmt->execute([':id' => $navItemId, ':cargo' => $cargoId]);
        if ($stmt->fetchColumn()) return false;
    }
    return true;
}

// ¿Puede el rol de la sesión abrir esta URL? Las que no son de ningún módulo
// vetable (ver modulo_de_ruta()) siempre pasan.
function usuario_puede_ver_ruta(string $uri): bool {
    $modulo = modulo_de_ruta($uri);
    if ($modulo === null) return true;
    return usuario_puede_ver_modulo($modulo);
}

// Módulo (clave de config/modulos.php) al que pertenece una dirección, según
// direcciones.slug y el 'slugs_direccion' de cada módulo. null si la dirección
// no existe o su slug no está en ningún módulo.
function modulo_de_direccion(?int $direccionId): ?string {
    global $pdo;
    static $slugPorId = null;
    if ($direccionId === null) return null;
    if ($slugPorId === null) {
        $slugPorId = [];
        foreach ($pdo->query('SELECT id, slug FROM direcciones')->fetchAll() as $d) {
            $slugPorId[(int) $d['id']] = $d['slug'];
        }
    }
    $slug = $slugPorId[$direccionId] ?? null;
    if ($slug === null) return null;
    foreach (modulos_config() as $clave => $modulo) {
        if (in_array($slug, $modulo['slugs_direccion'] ?? [], true)) return $clave;
    }
    return null;
}

// ¿Puede el usuario de la sesión ver/descargar/editar un archivo de ESTE
// módulo? Se decide por el módulo DEL ARCHIVO (resuelto desde su fila), no por
// el prefijo de la URL con que se pidió: un rol con /sgi vetado no puede bajar
// un archivo de SGI por /talento-humano/carpetas/descargar. El admin global
// siempre; el resto falla CERRADO: si el módulo no se pudo resolver (null) o
// está vetado, no. Quien llama debe dar la MISMA respuesta (403) a un archivo
// que no existe que a uno vetado, para no revelar qué ids existen.
function usuario_puede_ver_archivo_de(?string $modulo): bool {
    if (($_SESSION['usuario_rol'] ?? '') === 'admin') return true;
    return $modulo !== null && usuario_puede_ver_modulo($modulo);
}

// Conexión a la base de datos (deja $pdo listo para todo el proyecto)
require ROOT_PATH . '/config/database.php';

// Token CSRF: uno por sesión, para proteger los formularios (login, etc.)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// Rol y dirección vigentes: AuthController.php los copia a la sesión al
// iniciar sesión, y sin esto un admin que le baja el rol a alguien (o lo
// elimina) no surte efecto hasta que esa persona cierre sesión o pase 30
// minutos inactiva. Se relee de la BD (una consulta por clave primaria) en
// cada petición CON sesión — las públicas y las que llama OnlyOffice sin
// cookie (/editor/archivo, /editor/callback) no pasan por acá. Si el
// usuario ya no existe, se trata igual que una sesión expirada. (usuarios no
// tiene columna de estado/activo, así que "existe" es la única condición.)
if (!empty($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare('SELECT rol, direccion_id FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $usuarioVigente = $stmt->fetch();
    if (!$usuarioVigente) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . BASE_URL . '/login?expirada=1');
        exit;
    }
    $_SESSION['usuario_rol']          = $usuarioVigente['rol'];
    $_SESSION['usuario_direccion_id'] = $usuarioVigente['direccion_id'];
}

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
    if (!empty($_SESSION['usuario_id']) && !usuario_puede_ver_ruta($uri)) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    require ROOT_PATH . '/app/Controllers/' . $rutas[$uri];
} else {
    mostrar_error(404);
}
