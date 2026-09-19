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

// Acciones puntuales que se pueden quitar/dar por rol dentro de un módulo al
// que el rol sí tiene acceso (ver usuario_puede_accion() en public/index.php
// y migración 045_permisos_rol_acciones.sql). Complementa al checklist de
// módulos de arriba: ese oculta la página completa, esto ajusta qué se
// puede HACER dentro de ella — la dirección/área asignada al usuario sigue
// aplicando igual, esto solo puede restar, nunca dar acceso a otra dirección.
const ACCIONES_PERMISOS = [
    'carpetas.crear'           => 'Carpetas: crear carpeta',
    'carpetas.subir'           => 'Carpetas: subir archivo',
    'carpetas.crear_documento' => 'Carpetas: crear documento en blanco (Word/Excel/PowerPoint)',
    'carpetas.importar_drive'  => 'Carpetas: importar desde Google Drive',
    'carpetas.eliminar'        => 'Carpetas: eliminar carpeta o archivo',
    'documentos.crear'         => 'Documentos por dirección: crear documento',
    'documentos.subir'         => 'Documentos por dirección: subir archivo',
];

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

    // Mismo modelo de sync total para el checklist de acciones (ver
    // ACCIONES_PERMISOS arriba) — "accion_permitida[<clave>][<rol>]=1" solo
    // por los checkboxes marcados.
    $accionesPermitidas = $_POST['accion_permitida'] ?? [];

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
    $pdo->exec('DELETE FROM permisos_rol_acciones_negadas');
    $stmtAccion = $pdo->prepare('INSERT INTO permisos_rol_acciones_negadas (accion_clave, rol) VALUES (:accion, :rol)');
    foreach (array_keys(ACCIONES_PERMISOS) as $accionClave) {
        foreach (array_keys(PERMISOS_ROLES) as $rol) {
            $marcado = !empty($accionesPermitidas[$accionClave][$rol]);
            if (!$marcado) {
                $stmtAccion->execute([':accion' => $accionClave, ':rol' => $rol]);
            }
        }
    }

    // Mismo sync total, pero por cargo (ver migración 046_catalogo_cargos.sql)
    // en vez de por rol — "permitido_cargo[<nav_item_id>][<cargo_id>]=1" /
    // "accion_permitida_cargo[<clave>][<cargo_id>]=1". La lista de cargos es
    // dinámica (viene de la BD, no de una constante PHP como PERMISOS_ROLES).
    $permitidosCargo = $_POST['permitido_cargo'] ?? [];
    $accionesPermitidasCargo = $_POST['accion_permitida_cargo'] ?? [];
    $cargoIds = array_column($pdo->query('SELECT id FROM catalogo_cargos')->fetchAll(), 'id');

    $pdo->exec('DELETE FROM permisos_cargo_negados');
    $stmtCargo = $pdo->prepare('INSERT INTO permisos_cargo_negados (nav_item_id, cargo_id) VALUES (:id, :cargo)');
    foreach ($navItemIds as $navItemId) {
        foreach ($cargoIds as $cargoId) {
            $marcado = !empty($permitidosCargo[$navItemId][$cargoId]);
            if (!$marcado) {
                $stmtCargo->execute([':id' => $navItemId, ':cargo' => $cargoId]);
            }
        }
    }
    $pdo->exec('DELETE FROM permisos_cargo_acciones_negadas');
    $stmtAccionCargo = $pdo->prepare('INSERT INTO permisos_cargo_acciones_negadas (accion_clave, cargo_id) VALUES (:accion, :cargo)');
    foreach (array_keys(ACCIONES_PERMISOS) as $accionClave) {
        foreach ($cargoIds as $cargoId) {
            $marcado = !empty($accionesPermitidasCargo[$accionClave][$cargoId]);
            if (!$marcado) {
                $stmtAccionCargo->execute([':accion' => $accionClave, ':cargo' => $cargoId]);
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

$accionesNegadas = [];
foreach ($pdo->query('SELECT accion_clave, rol FROM permisos_rol_acciones_negadas')->fetchAll() as $n) {
    $accionesNegadas[$n['accion_clave']][$n['rol']] = true;
}

$cargos = $pdo->query('SELECT id, nombre FROM catalogo_cargos ORDER BY nombre')->fetchAll();

$negadosCargo = [];
foreach ($pdo->query('SELECT nav_item_id, cargo_id FROM permisos_cargo_negados')->fetchAll() as $n) {
    $negadosCargo[$n['nav_item_id']][$n['cargo_id']] = true;
}

$accionesNegadasCargo = [];
foreach ($pdo->query('SELECT accion_clave, cargo_id FROM permisos_cargo_acciones_negadas')->fetchAll() as $n) {
    $accionesNegadasCargo[$n['accion_clave']][$n['cargo_id']] = true;
}

require ROOT_PATH . '/app/Views/Portal/Permisos/Permisos.php';
