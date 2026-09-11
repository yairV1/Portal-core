<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/CarpetaController.php
//  Gestor de documentos tipo "drive" para un módulo de dirección — de
//  momento solo se usa desde Administrativa y Financiera (ver
//  PortalController.php, slug 'financiera', y
//  Views/Portal/Administrativa_Financiera/_explorador.php), pero no
//  depende de esa dirección en particular: recibe direccion_id desde el
//  formulario, así que activar el mismo explorador en otra dirección el
//  día de mañana es agregar su slug a $mapaSlugRuta más abajo, no tocar
//  este archivo entero.
//  Requiere sesión — mismo criterio que el resto de PortalController.php
//  (no hay todavía módulo real de áreas y permisos, ver roadmap). Crear/
//  subir/eliminar además exigen rol admin, igual que
//  DocumentoController.php para "Documentación destacada" en esta misma
//  página — solo descargar queda abierto a cualquier usuario logueado.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$carpetaStorage = ROOT_PATH . '/storage/direccion_carpetas';

// Igual que TrabajoController.php/ContratacionController.php: se valida el
// contenido real del archivo (finfo), nunca la extensión ni el mimetype
// que manda el navegador.
$MIME_PERMITIDOS = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'application/vnd.ms-powerpoint' => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

// A qué URL de módulo volver según la dirección — agregar acá el día que
// se active el explorador en otra dirección (ej. 'sgi' => '/sgi').
$mapaSlugRuta = ['financiera' => '/administrativa-financiera'];
$rutaPorDireccionId = [];
foreach ($pdo->query('SELECT id, slug FROM direcciones')->fetchAll() as $d) {
    if (isset($mapaSlugRuta[$d['slug']])) {
        $rutaPorDireccionId[$d['id']] = $mapaSlugRuta[$d['slug']];
    }
}

// $resultado viaja como ?drive=... para el toast de portal-footer.php
// (mismo patrón que ?doc=/?evento=/?pendiente=, ver ese archivo) — nunca
// un booleano genérico, así el aviso puede decir qué pasó de verdad.
function volver_a_carpeta(string $rutaModulo, ?int $carpetaId, ?string $resultado = null): void
{
    $params = [];
    if ($carpetaId) $params['carpeta'] = $carpetaId;
    if ($resultado !== null) $params['drive'] = $resultado;
    $qs = $params ? ('?' . http_build_query($params)) : '';
    header('Location: ' . BASE_URL . $rutaModulo . $qs);
    exit;
}

// ---- /administrativa-financiera/carpetas/crear (POST) ----
if ($uri === '/administrativa-financiera/carpetas/crear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/administrativa-financiera');
        exit;
    }
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/administrativa-financiera?drive=error');
        exit;
    }

    $direccionId = (int) ($_POST['direccion_id'] ?? 0);
    $parentId = ($_POST['carpeta_id'] ?? '') !== '' ? (int) $_POST['carpeta_id'] : null;
    $nombre = trim($_POST['nombre'] ?? '');
    $rutaModulo = $rutaPorDireccionId[$direccionId] ?? '/administrativa-financiera';

    if ($nombre === '' || mb_strlen($nombre) > 150) {
        volver_a_carpeta($rutaModulo, $parentId, 'nombre');
    }

    if ($parentId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM direccion_carpetas WHERE id = :id AND direccion_id = :did');
        $stmt->execute([':id' => $parentId, ':did' => $direccionId]);
        if (!$stmt->fetch()) {
            volver_a_carpeta($rutaModulo, null, 'error');
        }
    }

    $stmt = $pdo->prepare('INSERT INTO direccion_carpetas (direccion_id, parent_id, nombre) VALUES (:did, :pid, :nombre)');
    $stmt->execute([':did' => $direccionId, ':pid' => $parentId, ':nombre' => $nombre]);

    volver_a_carpeta($rutaModulo, $parentId, 'carpeta');
}

// ---- /administrativa-financiera/carpetas/subir (POST) ----
if ($uri === '/administrativa-financiera/carpetas/subir') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/administrativa-financiera');
        exit;
    }
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/administrativa-financiera?drive=error');
        exit;
    }

    $direccionId = (int) ($_POST['direccion_id'] ?? 0);
    $carpetaId = (int) ($_POST['carpeta_id'] ?? 0);
    $rutaModulo = $rutaPorDireccionId[$direccionId] ?? '/administrativa-financiera';

    $stmt = $pdo->prepare('SELECT id FROM direccion_carpetas WHERE id = :id AND direccion_id = :did');
    $stmt->execute([':id' => $carpetaId, ':did' => $direccionId]);
    if (!$stmt->fetch()) {
        volver_a_carpeta($rutaModulo, null, 'error');
    }

    $archivo = $_FILES['documento'] ?? null;
    if (empty($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        volver_a_carpeta($rutaModulo, $carpetaId, 'error');
    }

    if ($archivo['size'] > 15 * 1024 * 1024) {
        volver_a_carpeta($rutaModulo, $carpetaId, 'tamano');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($MIME_PERMITIDOS[$mime])) {
        volver_a_carpeta($rutaModulo, $carpetaId, 'formato');
    }

    if (!is_dir($carpetaStorage)) {
        mkdir($carpetaStorage, 0775, true);
    }

    $nombreOriginal = trim($_POST['nombre'] ?? '') ?: pathinfo($archivo['name'], PATHINFO_FILENAME);
    $stmt = $pdo->prepare('INSERT INTO direccion_carpeta_archivos (carpeta_id, nombre, archivo, peso_bytes) VALUES (:cid, :nombre, :archivo, :peso)');
    $stmt->execute([
        ':cid'     => $carpetaId,
        ':nombre'  => mb_substr($nombreOriginal, 0, 150),
        ':archivo' => '',
        ':peso'    => (int) $archivo['size'],
    ]);
    $archivoId = (int) $pdo->lastInsertId();

    $nombreArchivo = 'archivo_' . $archivoId . '.' . $MIME_PERMITIDOS[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], $carpetaStorage . '/' . $nombreArchivo)) {
        // Sin esto quedaría una fila en BD apuntando a un archivo que
        // nunca se escribió en disco (mismo chequeo que ya hace
        // DocumentoController.php en /documentos/subir).
        $pdo->prepare('DELETE FROM direccion_carpeta_archivos WHERE id = :id')->execute([':id' => $archivoId]);
        volver_a_carpeta($rutaModulo, $carpetaId, 'error');
    }

    $pdo->prepare('UPDATE direccion_carpeta_archivos SET archivo = :archivo WHERE id = :id')
        ->execute([':archivo' => $nombreArchivo, ':id' => $archivoId]);

    volver_a_carpeta($rutaModulo, $carpetaId, '1');
}

// ---- /administrativa-financiera/carpetas/descargar (GET) ----
if ($uri === '/administrativa-financiera/carpetas/descargar') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT nombre, archivo FROM direccion_carpeta_archivos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    $ruta = $fila ? $carpetaStorage . '/' . $fila['archivo'] : null;
    if (!$fila || !is_file($ruta)) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $extension = pathinfo($fila['archivo'], PATHINFO_EXTENSION);
    $nombreDescarga = preg_replace('/[^A-Za-z0-9 _.-]/', '', $fila['nombre']) . '.' . $extension;

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
    header('Content-Length: ' . filesize($ruta));
    header('Cache-Control: no-store');
    readfile($ruta);
    exit;
}

// ---- /administrativa-financiera/carpetas/eliminar (POST) ----
// Borra un archivo suelto (archivo_id) o una carpeta completa con todo lo
// que cuelgue de ella (carpeta_id) — uno de los dos por envío, nunca
// ambos. ON DELETE CASCADE limpia las filas en BD; acá solo hace falta
// borrar los archivos físicos correspondientes antes.
if ($uri === '/administrativa-financiera/carpetas/eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/administrativa-financiera');
        exit;
    }
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/administrativa-financiera?drive=error');
        exit;
    }

    $direccionId = (int) ($_POST['direccion_id'] ?? 0);
    $rutaModulo = $rutaPorDireccionId[$direccionId] ?? '/administrativa-financiera';

    if (!empty($_POST['archivo_id'])) {
        $archivoId = (int) $_POST['archivo_id'];
        $stmt = $pdo->prepare('
            SELECT a.archivo, a.carpeta_id FROM direccion_carpeta_archivos a
            JOIN direccion_carpetas c ON c.id = a.carpeta_id
            WHERE a.id = :id AND c.direccion_id = :did
        ');
        $stmt->execute([':id' => $archivoId, ':did' => $direccionId]);
        $fila = $stmt->fetch();
        if (!$fila) {
            volver_a_carpeta($rutaModulo, null, 'error');
        }

        $ruta = $carpetaStorage . '/' . $fila['archivo'];
        if (is_file($ruta)) unlink($ruta);
        $pdo->prepare('DELETE FROM direccion_carpeta_archivos WHERE id = :id')->execute([':id' => $archivoId]);

        volver_a_carpeta($rutaModulo, (int) $fila['carpeta_id'], 'eliminado');
    }

    if (!empty($_POST['carpeta_id'])) {
        $carpetaId = (int) $_POST['carpeta_id'];
        $stmt = $pdo->prepare('SELECT parent_id FROM direccion_carpetas WHERE id = :id AND direccion_id = :did');
        $stmt->execute([':id' => $carpetaId, ':did' => $direccionId]);
        $fila = $stmt->fetch();
        if (!$fila) {
            volver_a_carpeta($rutaModulo, null, 'error');
        }

        $stmt2 = $pdo->prepare('
            WITH RECURSIVE subcarpetas AS (
                SELECT id FROM direccion_carpetas WHERE id = :id
                UNION ALL
                SELECT c.id FROM direccion_carpetas c JOIN subcarpetas s ON c.parent_id = s.id
            )
            SELECT archivo FROM direccion_carpeta_archivos WHERE carpeta_id IN (SELECT id FROM subcarpetas)
        ');
        $stmt2->execute([':id' => $carpetaId]);
        foreach ($stmt2->fetchAll() as $a) {
            $rutaArchivo = $carpetaStorage . '/' . $a['archivo'];
            if (is_file($rutaArchivo)) unlink($rutaArchivo);
        }
        $pdo->prepare('DELETE FROM direccion_carpetas WHERE id = :id')->execute([':id' => $carpetaId]);

        volver_a_carpeta($rutaModulo, $fila['parent_id'] ? (int) $fila['parent_id'] : null, 'carpeta_eliminada');
    }

    volver_a_carpeta($rutaModulo, null, 'error');
}
