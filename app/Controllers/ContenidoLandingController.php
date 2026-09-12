<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/ContenidoLandingController.php
//  Panel para que un admin cargue el contenido real de la landing pública
//  ("/", sin sesión — ver HomeController.php/Views/Landing/Inicio.php)
//  directamente desde el navegador, sin tocar la base de datos a mano ni
//  inventar textos institucionales en el código.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    mostrar_error(403);
    exit;
}

// Claves fijas que espera Views/Landing/Inicio.php — no es una tabla libre,
// así que la lista de "secciones" válidas vive acá, no en la base de datos.
// Cada una declara qué campos usa de verdad la vista (los demás quedan
// ocultos en el formulario para no confundir con texto que nunca se ve).
// 'tab' agrupa cada clave bajo la pestaña del panel que le corresponde
// (ver ContenidoLanding.php) — refleja el orden real de secciones de
// Views/Landing/Inicio.php, no un orden arbitrario.
const LANDING_SECCIONES_DEF = [
    'hero'      => ['nombre' => 'Hero (portada)',                  'etiqueta' => true,  'descripcion' => true,  'tab' => 'hero'],
    'por_que'   => ['nombre' => '¿Por qué Portal CORE?',           'etiqueta' => true,  'descripcion' => true,  'tab' => 'porque'],
    'seguridad' => ['nombre' => 'Tarjeta — Seguridad',             'etiqueta' => false, 'descripcion' => true,  'tab' => 'porque'],
    'conexion'  => ['nombre' => 'Tarjeta — Conexión',              'etiqueta' => false, 'descripcion' => true,  'tab' => 'porque'],
    'medida'    => ['nombre' => 'Tarjeta — A tu medida',           'etiqueta' => false, 'descripcion' => true,  'tab' => 'porque'],
    'modulos'   => ['nombre' => 'Sección Módulos (encabezado)',    'etiqueta' => false, 'descripcion' => true,  'tab' => 'modulos'],
    'roles'     => ['nombre' => 'Sección Para tu rol (intro)',     'etiqueta' => false, 'descripcion' => true,  'tab' => 'roles'],
    'pasos'     => ['nombre' => 'Sección Cómo empiezas (título)',  'etiqueta' => false, 'descripcion' => false, 'tab' => 'pasos'],
];

function landing_volver(string $resultado): void
{
    header('Location: ' . BASE_URL . '/contenido-landing?landing=' . $resultado);
    exit;
}

function landing_csrf_ok(): bool
{
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '');
}

// ---- /contenido-landing/guardar-seccion ----
if ($uri === '/contenido-landing/guardar-seccion') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !landing_csrf_ok()) {
        landing_volver('error');
    }
    $clave = $_POST['clave'] ?? '';
    if (!isset(LANDING_SECCIONES_DEF[$clave])) {
        landing_volver('error');
    }
    $def = LANDING_SECCIONES_DEF[$clave];
    $etiqueta = $def['etiqueta'] ? trim($_POST['etiqueta'] ?? '') : '';
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = $def['descripcion'] ? trim($_POST['descripcion'] ?? '') : '';

    if ($titulo === '') {
        landing_volver('error');
    }

    $pdo->prepare('
        INSERT INTO landing_secciones (clave, etiqueta, titulo, descripcion)
        VALUES (:clave, :etiqueta, :titulo, :descripcion)
        ON DUPLICATE KEY UPDATE etiqueta = :etiqueta2, titulo = :titulo2, descripcion = :descripcion2
    ')->execute([
        ':clave' => $clave, ':etiqueta' => $etiqueta, ':titulo' => $titulo, ':descripcion' => $descripcion,
        ':etiqueta2' => $etiqueta, ':titulo2' => $titulo, ':descripcion2' => $descripcion,
    ]);

    landing_volver('guardado');
}

// ---- Listas repetibles: estadísticas, módulos, roles, pasos ----
// Misma forma para las 4: guardar (crea si id viene vacío/0, si no
// actualiza) y eliminar. Se resuelve con una tabla + columnas según el
// caso en vez de 8 bloques casi idénticos.
$listasRepetibles = [
    '/contenido-landing/estadistica' => [
        'tabla' => 'landing_estadisticas',
        'campos' => ['valor', 'etiqueta', 'descripcion', 'icono', 'orden'],
        'requeridos' => ['valor', 'etiqueta', 'descripcion'],
    ],
    '/contenido-landing/modulo' => [
        'tabla' => 'landing_modulos',
        'campos' => ['titulo', 'meta', 'ubicacion', 'estado', 'icono', 'tema', 'orden'],
        'requeridos' => ['titulo', 'meta', 'ubicacion', 'estado', 'icono'],
    ],
    '/contenido-landing/rol' => [
        'tabla' => 'landing_roles',
        'campos' => ['titulo', 'descripcion', 'icono', 'tema', 'orden'],
        'requeridos' => ['titulo', 'descripcion', 'icono'],
    ],
    '/contenido-landing/paso' => [
        'tabla' => 'landing_pasos',
        'campos' => ['titulo', 'descripcion', 'icono', 'orden'],
        'requeridos' => ['titulo', 'descripcion', 'icono'],
    ],
];

foreach ($listasRepetibles as $base => $conf) {
    if ($uri === $base . '/guardar') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !landing_csrf_ok()) {
            landing_volver('error');
        }

        $valores = [];
        foreach ($conf['campos'] as $campo) {
            $valores[$campo] = $campo === 'orden'
                ? (int) ($_POST['orden'] ?? 0)
                : trim($_POST[$campo] ?? '');
        }
        foreach ($conf['requeridos'] as $campo) {
            if ($valores[$campo] === '') {
                landing_volver('error');
            }
        }

        $id = (int) ($_POST['id'] ?? 0);
        $columnas = implode(', ', $conf['campos']);
        $marcadores = implode(', ', array_map(fn ($c) => ':' . $c, $conf['campos']));

        if ($id > 0) {
            $sets = implode(', ', array_map(fn ($c) => "$c = :$c", $conf['campos']));
            $stmt = $pdo->prepare("UPDATE {$conf['tabla']} SET $sets WHERE id = :id");
            $valores['id'] = $id;
        } else {
            $stmt = $pdo->prepare("INSERT INTO {$conf['tabla']} ($columnas) VALUES ($marcadores)");
        }
        $stmt->execute(array_combine(
            array_map(fn ($k) => ':' . $k, array_keys($valores)),
            array_values($valores)
        ));

        landing_volver('guardado');
    }

    if ($uri === $base . '/eliminar') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !landing_csrf_ok()) {
            landing_volver('error');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM {$conf['tabla']} WHERE id = :id")->execute([':id' => $id]);
        }
        landing_volver('eliminado');
    }
}

// ---- /contenido-landing (panel) ----
$landingSecciones = [];
foreach ($pdo->query('SELECT clave, etiqueta, titulo, descripcion FROM landing_secciones')->fetchAll() as $fila) {
    $landingSecciones[$fila['clave']] = $fila;
}
$landingEstadisticas = $pdo->query('SELECT id, valor, etiqueta, descripcion, icono, orden FROM landing_estadisticas ORDER BY orden')->fetchAll();
$landingModulos = $pdo->query('SELECT id, titulo, meta, ubicacion, estado, icono, tema, orden FROM landing_modulos ORDER BY orden')->fetchAll();
$landingRoles = $pdo->query('SELECT id, titulo, descripcion, icono, tema, orden FROM landing_roles ORDER BY orden')->fetchAll();
$landingPasos = $pdo->query('SELECT id, titulo, descripcion, icono, orden FROM landing_pasos ORDER BY orden')->fetchAll();

require ROOT_PATH . '/app/Views/Portal/ContenidoLanding/ContenidoLanding.php';
