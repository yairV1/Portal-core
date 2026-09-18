<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/PermisosController.php
//  "Permisos por rol" — panel para que el admin global le quite acceso a
//  módulos puntuales del sidebar a admin_direccion/usuario (ver migración
//  044_permisos_rol_nav_item.sql y usuario_puede_ver_ruta() en
//  public/index.php, que hace cumplir esto en CADA ruta, no solo en el
//  sidebar). El admin global nunca aparece acá — no se autolimitea.
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

const PERMISOS_ROLES = ['admin_direccion' => 'Administrador de dirección', 'usuario' => 'Usuario'];

if ($uri === '/permisos-por-rol/guardar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/permisos-por-rol');
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/permisos-por-rol?error=1');
        exit;
    }

    // El checklist manda "permitido[<nav_item_id>][<rol>]=1" solo por los
    // checkboxes que quedaron MARCADOS — los que faltan (sin marcar) son
    // los que hay que denegar. Se reconstruye la tabla completa en cada
    // guardado (sync total, no altas/bajas sueltas) para que un checkbox
    // que el navegador no mande por lo que sea nunca deje una fila vieja
    // sin actualizar.
    $permitidos = $_POST['permitido'] ?? [];
    $navItemIds = array_column($pdo->query('SELECT id FROM nav_items WHERE parent_id IS NULL AND ruta IS NOT NULL AND ruta != "/"')->fetchAll(), 'id');

    $pdo->beginTransaction();
    $pdo->exec('DELETE FROM permisos_rol_negados');
    $stmt = $pdo->prepare('INSERT INTO permisos_rol_negados (nav_item_id, rol) VALUES (:id, :rol)');
    foreach ($navItemIds as $navItemId) {
        foreach (array_keys(PERMISOS_ROLES) as $rol) {
            $marcado = !empty($permitidos[$navItemId][$rol]);
            if (!$marcado) {
                $stmt->execute([':id' => $navItemId, ':rol' => $rol]);
            }
        }
    }
    $pdo->commit();

    header('Location: ' . BASE_URL . '/permisos-por-rol?guardado=1');
    exit;
}

// ---- /permisos-por-rol (GET) ----
$modulos = $pdo->query("
    SELECT ni.id, ni.label, ni.icono, ns.label AS seccion
    FROM nav_items ni
    JOIN nav_secciones ns ON ns.id = ni.seccion_id
    WHERE ni.parent_id IS NULL AND ni.ruta IS NOT NULL AND ni.ruta != '/'
    ORDER BY ns.orden, ni.orden
")->fetchAll();

$negados = [];
foreach ($pdo->query('SELECT nav_item_id, rol FROM permisos_rol_negados')->fetchAll() as $n) {
    $negados[$n['nav_item_id']][$n['rol']] = true;
}

require ROOT_PATH . '/app/Views/Portal/Permisos/Permisos.php';
