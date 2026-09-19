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

$modulos = modulos_config();

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

    // Sync total (no altas/bajas sueltas): se reconstruye la tabla completa en
    // cada guardado, para que un checkbox que el navegador no mande nunca deje
    // una fila vieja sin actualizar. Todo o nada: si algo falla, rollBack.
    try {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM permisos_rol_modulo');
        $stmt = $pdo->prepare('INSERT INTO permisos_rol_modulo (modulo, rol) VALUES (:modulo, :rol)');
        foreach (array_keys($modulos) as $clave) {
            foreach (array_keys(PERMISOS_ROLES) as $rol) {
                if (empty($permitidos[$clave][$rol])) {
                    $stmt->execute([':modulo' => $clave, ':rol' => $rol]);
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

require ROOT_PATH . '/app/Views/Portal/Permisos/Permisos.php';
