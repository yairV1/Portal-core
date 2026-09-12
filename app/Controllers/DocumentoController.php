<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/DocumentoController.php
//  Sube y descarga el archivo real de un documento — de
//  archivos_documentales (Gestion_Documental/Documental.php) o de
//  direccion_documentos ("Documentación destacada" de los 6 módulos
//  genéricos de dirección), según ?tipo=documental|direccion.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Fuera de public/ a propósito: si viviera en public/uploads/, Apache lo
// serviría directo con solo conocer la URL, sin pasar por el login (así
// funcionan hoy el logo y las fotos de perfil, pero esos no son
// documentos institucionales). Acá la descarga siempre pasa por este
// controlador, que sí exige sesión.
$carpetaArchivos = ROOT_PATH . '/storage/documentos';

$TIPOS_PERMITIDOS = [
    'application/pdf'                                                          => 'pdf',
    'application/msword'                                                       => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'  => 'docx',
    'application/vnd.ms-excel'                                                 => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'        => 'xlsx',
    'application/vnd.ms-powerpoint'                                            => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'=> 'pptx',
];
$TAMANO_MAXIMO = 20 * 1024 * 1024; // 20 MB

// Dos tablas comparten la misma mecánica de subir/descargar — la de
// dirección_id nunca viaja como texto libre: solo "documental"/"direccion"
// deciden qué tabla y qué prefijo de archivo en disco se usan.
$esDireccion = ($_REQUEST['tipo'] ?? '') === 'direccion';
$TABLA = $esDireccion ? 'direccion_documentos' : 'archivos_documentales';
$PREFIJO = $esDireccion ? 'direccion' : 'documento';

// A qué página volver tras subir — viene del formulario (cada módulo pone
// su propia ruta), pero solo se acepta si es una de las páginas reales que
// muestran documentos; cualquier otro valor se ignora, para no abrir un
// redirect a donde sea con solo tocar el campo oculto del formulario.
$RUTAS_VOLVER = ['/gestion-documental', '/gestion-institucional', '/sgi', '/vicerrectoria-academica', '/administrativa-financiera', '/investigacion-innovacion', '/novedades'];
$volver = in_array($_POST['volver'] ?? '', $RUTAS_VOLVER, true) ? $_POST['volver'] : '/gestion-documental';

// ---- /documentos/subir ----
if ($uri === '/documentos/subir') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . $volver);
        exit;
    }

    // Subir es una acción administrativa (mismo criterio que el resto del
    // portal hoy: rol admin/usuario, sin niveles de acceso todavía — eso
    // vive en el módulo de áreas y permisos, pendiente).
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . $volver . '?doc=error');
        exit;
    }

    $archivoId = (int) ($_POST['archivo_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id FROM {$TABLA} WHERE id = :id");
    $stmt->execute([':id' => $archivoId]);
    if (!$stmt->fetch()) {
        header('Location: ' . BASE_URL . $volver . '?doc=error');
        exit;
    }

    if (empty($_FILES['archivo']['tmp_name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        header('Location: ' . BASE_URL . $volver . '?doc=error');
        exit;
    }
    if ($_FILES['archivo']['size'] > $TAMANO_MAXIMO) {
        header('Location: ' . BASE_URL . $volver . '?doc=tamano');
        exit;
    }

    $tmp = $_FILES['archivo']['tmp_name'];

    // El mimetype real del contenido, no el que manda el navegador (ese lo
    // puede falsificar cualquiera con solo renombrar el archivo) — mismo
    // criterio que ya usa PerfilController.php con getimagesize() para fotos.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset($TIPOS_PERMITIDOS[$mime])) {
        header('Location: ' . BASE_URL . $volver . '?doc=formato');
        exit;
    }

    if (!is_dir($carpetaArchivos)) {
        mkdir($carpetaArchivos, 0775, true);
    }

    // Nombre fijo armado por el servidor a partir de la tabla + id de la
    // fila — nunca del nombre que mande el navegador, para no arriesgar
    // path traversal ni pisar el archivo de otro documento.
    $nombreArchivo = $PREFIJO . '_' . $archivoId . '.' . $TIPOS_PERMITIDOS[$mime];
    if (!move_uploaded_file($tmp, $carpetaArchivos . '/' . $nombreArchivo)) {
        header('Location: ' . BASE_URL . $volver . '?doc=error');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE {$TABLA} SET archivo = :archivo WHERE id = :id");
    $stmt->execute([':archivo' => $nombreArchivo, ':id' => $archivoId]);

    header('Location: ' . BASE_URL . $volver . '?doc=1');
    exit;
}

// ---- /documentos/descargar ----
if ($uri === '/documentos/descargar') {
    $archivoId = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT nombre, archivo FROM {$TABLA} WHERE id = :id");
    $stmt->execute([':id' => $archivoId]);
    $fila = $stmt->fetch();

    if (!$fila || !$fila['archivo']) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $ruta = $carpetaArchivos . '/' . $fila['archivo'];
    if (!is_file($ruta)) {
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
