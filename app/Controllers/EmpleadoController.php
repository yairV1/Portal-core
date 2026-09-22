<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/EmpleadoController.php
//  CRUD de la tabla maestra `empleados` (migración 048) — compartida por
//  los 3 submódulos nuevos de Talento Humano (Hojas de vida/Contratos/
//  Certificaciones laborales, ver EmpleadoDocumentoController.php). Un
//  único empleado puede tener a lo más una fila en cada una de esas 3
//  tablas; el alta/edición/baja del empleado vive acá para no triplicar
//  este formulario en las 3 vistas.
//  Permiso: admin global, o admin_direccion atado a la dirección de
//  Talento Humano (igual que CarpetaController.php) — no exclusivo de
//  admin global como UsuariosController.php, porque esto no toca cuentas
//  ni roles del portal, solo el padrón de personal de esa dirección.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM direcciones WHERE slug = :slug');
$stmt->execute([':slug' => 'talento-humano']);
$direccionTalentoHumanoId = (int) ($stmt->fetchColumn() ?: 0);

// Solo se acepta volver a una de las 3 páginas reales que muestran
// empleados — cualquier otro valor se ignora (mismo criterio que
// $RUTAS_VOLVER en DocumentoController.php, para no abrir un redirect
// abierto con solo tocar el campo oculto del formulario).
$RUTAS_VOLVER = [
    '/talento-humano/hojas-de-vida',
    '/talento-humano/contratos',
    '/talento-humano/certificaciones-laborales',
];
$volver = in_array($_POST['volver'] ?? '', $RUTAS_VOLVER, true) ? $_POST['volver'] : '/talento-humano/hojas-de-vida';

function empleado_volver(string $volver, string $resultado): void
{
    header('Location: ' . BASE_URL . $volver . '?empleado=' . $resultado);
    exit;
}

// El módulo que gobierna "crear" es el de la página desde la que se llamó
// (cada una de las 3 tiene su propia clave vetable en config/modulos.php) —
// dar de alta un empleado nuevo solo agrega una fila vacía en `empleados`,
// sin tocar contenido de las otras 2 tablas de documentos.
$claveModulo = modulo_de_ruta($volver);
if (!usuario_admin_de($direccionTalentoHumanoId ?: null) || !usuario_puede_ver_archivo_de($claveModulo)) {
    http_response_code(403);
    mostrar_error(403);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . $volver);
    exit;
}
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    empleado_volver($volver, 'error');
}

// "crear"/"editar"/"eliminar" tocan (o exponen, apenas se crea) datos
// compartidos por las 3 vistas — "eliminar" además borra en cascada (BD +
// archivos en disco) las filas de hojas_de_vida/contratos/
// certificaciones_laborales de ese empleado. Un rol al que solo se le
// permitió UN submódulo no puede, desde ahí, crear/tocar/eliminar un
// empleado que también aparece en los otros 2 que tiene vetados. Exige que
// NINGUNO de los 3 esté vetado (hallazgo de seguridad: antes "crear" solo
// validaba el módulo de la página que llamó, y "editar"/"eliminar" tenían
// el mismo hueco hasta una corrección previa que quedó incompleta).
$CLAVES_MODULO_EMPLEADOS = ['talento-humano-hojas-de-vida', 'talento-humano-contratos', 'talento-humano-certificaciones-laborales'];
function empleado_puede_administrar(array $claves): bool
{
    foreach ($claves as $clave) {
        if (!usuario_puede_ver_archivo_de($clave)) {
            return false;
        }
    }
    return true;
}
if (!empleado_puede_administrar($CLAVES_MODULO_EMPLEADOS)) {
    http_response_code(403);
    mostrar_error(403);
    exit;
}

// ---- /talento-humano/empleados/crear ----
if ($uri === '/talento-humano/empleados/crear') {
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '') ?: null;
    $telefono = trim($_POST['telefono'] ?? '') ?: null;
    $correo = trim($_POST['correo'] ?? '') ?: null;
    $fechaIngreso = trim($_POST['fecha_ingreso'] ?? '') ?: null;

    if ($nombre === '' || $documento === '') {
        empleado_volver($volver, 'datos');
    }
    if ($fechaIngreso !== null) {
        $f = DateTime::createFromFormat('Y-m-d', $fechaIngreso);
        if (!$f || $f->format('Y-m-d') !== $fechaIngreso) {
            empleado_volver($volver, 'datos');
        }
    }

    try {
        $pdo->prepare('INSERT INTO empleados (nombre_completo, documento, cargo, telefono, correo, fecha_ingreso) VALUES (:nombre, :documento, :cargo, :telefono, :correo, :fecha_ingreso)')
            ->execute([
                ':nombre' => $nombre,
                ':documento' => $documento,
                ':cargo' => $cargo,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':fecha_ingreso' => $fechaIngreso,
            ]);
    } catch (PDOException $e) {
        // El caso esperado es el choque con uq_empleados_documento (ya
        // existe un empleado con ese documento), pero cualquier otro error
        // de BD cae acá también — se registra para no reportar "documento
        // duplicado" como causa genérica de un fallo real distinto.
        error_log('Empleados: no se pudo crear (' . $documento . '): ' . $e->getMessage());
        empleado_volver($volver, 'documento_existe');
    }

    empleado_volver($volver, 'creado');
}

// ---- /talento-humano/empleados/editar ----
if ($uri === '/talento-humano/empleados/editar') {
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '') ?: null;
    $telefono = trim($_POST['telefono'] ?? '') ?: null;
    $correo = trim($_POST['correo'] ?? '') ?: null;
    $fechaIngreso = trim($_POST['fecha_ingreso'] ?? '') ?: null;
    $estado = ($_POST['estado'] ?? '') === 'inactivo' ? 'inactivo' : 'activo';

    if ($id <= 0 || $nombre === '' || $documento === '') {
        empleado_volver($volver, 'datos');
    }
    if ($fechaIngreso !== null) {
        $f = DateTime::createFromFormat('Y-m-d', $fechaIngreso);
        if (!$f || $f->format('Y-m-d') !== $fechaIngreso) {
            empleado_volver($volver, 'datos');
        }
    }

    try {
        $pdo->prepare('UPDATE empleados SET nombre_completo = :nombre, documento = :documento, cargo = :cargo, telefono = :telefono, correo = :correo, fecha_ingreso = :fecha_ingreso, estado = :estado WHERE id = :id')
            ->execute([
                ':nombre' => $nombre,
                ':documento' => $documento,
                ':cargo' => $cargo,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':fecha_ingreso' => $fechaIngreso,
                ':estado' => $estado,
                ':id' => $id,
            ]);
    } catch (PDOException $e) {
        error_log('Empleados: no se pudo actualizar #' . $id . ': ' . $e->getMessage());
        empleado_volver($volver, 'documento_existe');
    }

    empleado_volver($volver, 'actualizado');
}

// ---- /talento-humano/empleados/eliminar ----
if ($uri === '/talento-humano/empleados/eliminar') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        empleado_volver($volver, 'error');
    }

    // El ON DELETE CASCADE de hojas_de_vida/contratos/certificaciones_laborales
    // limpia las filas de BD solas, pero no los archivos físicos en disco —
    // hay que borrarlos primero (mismo cuidado que ya tiene
    // CarpetaController.php al eliminar una carpeta con archivos dentro).
    $storage = ROOT_PATH . '/storage/talento_humano_documentos';
    foreach (['hojas_de_vida', 'contratos', 'certificaciones_laborales'] as $tabla) {
        $stmt = $pdo->prepare("SELECT archivo FROM {$tabla} WHERE empleado_id = :id AND archivo IS NOT NULL");
        $stmt->execute([':id' => $id]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $archivo) {
            $ruta = $storage . '/' . $archivo;
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }
    }

    $pdo->prepare('DELETE FROM empleados WHERE id = :id')->execute([':id' => $id]);

    empleado_volver($volver, 'eliminado');
}

header('Location: ' . BASE_URL . $volver);
exit;
