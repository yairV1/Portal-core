<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/PermisosController.php
//  "Permisos por rol" — panel para que el admin global le quite acceso a
//  módulos puntuales a admin_direccion/usuario. Los módulos vetables salen de
//  config/modulos.php y los vetos se guardan en permisos_rol_modulo (migración
//  045_permisos_rol_modulo.sql); usuario_puede_ver_ruta() en public/index.php
//  hace cumplir esto en CADA ruta, no solo en el menú. El admin global nunca
//  aparece acá — no se autolimitea.
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
$modulos = modulos_config();

// Los permisos por cargo siguen usando nav_item_id por compatibilidad con la
// migración 046; los permisos por rol usan las claves estables de config.
// Sin filtrar por parent_id IS NULL: los 3 submódulos de Talento Humano
// (migración 048) son hijos de nav_items y si no, quedarían con id=0 acá
// (la vista los salta, sin casilla de "permisos por cargo" — bug real
// encontrado 2026-09-21). La `ruta` de cada nav_item sigue siendo única
// sin importar si es de primer nivel o un hijo, así que el mapeo es igual
// de seguro.
$navItemsPorRuta = [];
foreach ($pdo->query("SELECT id, ruta FROM nav_items WHERE ruta IS NOT NULL AND ruta != '/'")->fetchAll() as $navItem) {
    $navItemsPorRuta[$navItem['ruta']] = (int) $navItem['id'];
}
foreach ($modulos as $clave => &$modulo) {
    $modulo['id'] = 0;
    foreach ($modulo['rutas'] as $ruta) {
        if (isset($navItemsPorRuta[$ruta])) {
            $modulo['id'] = $navItemsPorRuta[$ruta];
            break;
        }
    }
}
unset($modulo);

function permisos_rechazar(string $motivo): void
{
    header('Location: ' . BASE_URL . '/permisos-por-rol?error=' . $motivo);
    exit;
}

if ($uri === '/permisos-por-rol/guardar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/permisos-por-rol');
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        permisos_rechazar('csrf');
    }

    // El checklist manda "permitido[<clave del módulo>][<rol>]=1" solo por los
    // checkboxes que quedaron MARCADOS — los que faltan (sin marcar) son los
    // que hay que denegar. Antes de tocar nada se valida TODO lo recibido: solo
    // claves que existan en config/modulos.php y solo los roles del panel
    // (admin_direccion/usuario) — cualquier otra cosa rechaza el guardado
    // completo, sin guardar a medias.
    $permitidos = $_POST['permitido'] ?? [];
    if (!is_array($permitidos)) {
        permisos_rechazar('datos');
    }
    foreach ($permitidos as $clave => $roles) {
        if (!isset($modulos[$clave])) {
            permisos_rechazar('modulo');
        }
        if (!is_array($roles)) {
            permisos_rechazar('datos');
        }
        foreach ($roles as $rol => $valor) {
            if (!isset(PERMISOS_ROLES[$rol])) {
                permisos_rechazar('rol');
            }
        }
    }
    // Mismo modelo de sync total para el checklist de acciones (ver
    // ACCIONES_PERMISOS arriba) — "accion_permitida[<clave>][<rol>]=1" solo
    // por los checkboxes marcados.
    $accionesPermitidas = $_POST['accion_permitida'] ?? [];

    try {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM permisos_rol_modulo');
        $stmtModulo = $pdo->prepare('INSERT INTO permisos_rol_modulo (modulo, rol) VALUES (:modulo, :rol)');
        foreach (array_keys($modulos) as $clave) {
            foreach (array_keys(PERMISOS_ROLES) as $rol) {
                if (empty($permitidos[$clave][$rol])) {
                    $stmtModulo->execute([':modulo' => $clave, ':rol' => $rol]);
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
    $navItemIds = array_values($navItemsPorRuta);

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
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Permisos por rol: no se pudo guardar (¿falta aplicar la migración 045?): ' . $e->getMessage());
        permisos_rechazar('guardar');
    }

    header('Location: ' . BASE_URL . '/permisos-por-rol?guardado=1');
    exit;
}

// ---- /permisos-por-rol (GET) ----
$negados = [];
try {
    foreach ($pdo->query('SELECT modulo, rol FROM permisos_rol_modulo')->fetchAll() as $n) {
        $negados[$n['modulo']][$n['rol']] = true;
    }
} catch (PDOException $e) {
    error_log('Permisos por rol: no se pudo leer permisos_rol_modulo (¿falta aplicar la migración 045?): ' . $e->getMessage());
    http_response_code(500);
    mostrar_error(500);
    exit;
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
