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
$RUTAS_VOLVER = [
    '/gestion-documental', '/gestion-institucional', '/sgi', '/vicerrectoria-academica',
    '/administrativa-financiera', '/investigacion-innovacion', '/novedades', '/talento-humano',
    // Perspectivas del CMI (ver PortalController.php, Perspectiva.php) — faltaban
    // acá, así que "Subir"/"Agregar documento" desde esas 4 páginas mandaba de
    // vuelta a Gestión Documental en vez de a la perspectiva de origen.
    '/cuadro-mando-integral/finanzas', '/cuadro-mando-integral/planeacion',
    '/cuadro-mando-integral/vicerrectoria-academica', '/cuadro-mando-integral/investigacion',
];
$volver = in_array($_POST['volver'] ?? '', $RUTAS_VOLVER, true) ? $_POST['volver'] : '/gestion-documental';

// $area solo aplica a direccion_documentos de Financiera (ver migración
// 032) — para las demás tablas/módulos siempre queda null y no se agrega
// a la URL, sin cambiarles nada.
function volver_documento(string $volver, ?string $resultado = null, ?string $area = null): void
{
    $params = [];
    if ($area !== null) $params['area'] = $area;
    if ($resultado !== null) $params['doc'] = $resultado;
    $qs = $params ? ('?' . http_build_query($params)) : '';
    header('Location: ' . BASE_URL . $volver . $qs);
    exit;
}

// ---- /documentos/crear ----
// Antes de esto, la única forma de meter una fila en archivos_documentales
// o direccion_documentos era a mano en la base de datos — /subir solo
// puede adjuntarle un archivo a una fila que YA existe. Esto crea esa fila
// (sin archivo todavía, igual que si alguien la hubiera dejado pendiente),
// para que de ahí en adelante el flujo de "Subir" de siempre funcione.
if ($uri === '/documentos/crear') {
    // Pestaña activa (Administración/Finanzas) — solo tiene sentido para
    // direccion_documentos (ver migración 032); se preserva en el
    // redirect para no bajarte de vuelta a la otra pestaña.
    $area = ($_POST['area'] ?? '') === 'finanzas' ? 'finanzas' : 'administracion';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        volver_documento($volver);
    }
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        volver_documento($volver, 'error', $area);
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = trim($_POST['tipo_doc'] ?? '');
    $version = trim($_POST['version'] ?? '') ?: 'v1.0';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
    if ($nombre === '' || !$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
        volver_documento($volver, 'nombre', $area);
    }

    if ($esDireccion) {
        $direccionId = (int) ($_POST['direccion_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT id FROM direcciones WHERE id = :id');
        $stmt->execute([':id' => $direccionId]);
        if (!$stmt->fetch()) {
            volver_documento($volver, 'error', $area);
        }
        // categoria distingue "Documentación destacada" de "Formatos"
        // (plantillas en blanco) — ver migración 031. Los formularios que
        // no la mandan (los 6 módulos genéricos "de siempre") siguen
        // creando documentos normales, sin romper nada.
        $categoria = ($_POST['categoria'] ?? '') === 'formato' ? 'formato' : 'documento';
        $pdo->prepare('INSERT INTO direccion_documentos (direccion_id, categoria, area, nombre, tipo, version, fecha) VALUES (:did, :categoria, :area, :nombre, :tipo, :version, :fecha)')
            ->execute([':did' => $direccionId, ':categoria' => $categoria, ':area' => $area, ':nombre' => $nombre, ':tipo' => $tipo, ':version' => $version, ':fecha' => $fecha]);
    } else {
        $carpetaId = (int) ($_POST['carpeta_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT id FROM direccion_areas WHERE id = :id');
        $stmt->execute([':id' => $carpetaId]);
        if (!$stmt->fetch()) {
            volver_documento($volver, 'error');
        }
        $responsable = trim($_POST['responsable'] ?? '');
        $pdo->prepare('INSERT INTO archivos_documentales (carpeta_id, nombre, tipo, version, estado, responsable, fecha) VALUES (:cid, :nombre, :tipo, :version, :estado, :responsable, :fecha)')
            ->execute([':cid' => $carpetaId, ':nombre' => $nombre, ':tipo' => $tipo, ':version' => $version, ':estado' => 'Vigente', ':responsable' => $responsable, ':fecha' => $fecha]);
    }

    volver_documento($volver, 'creado', $esDireccion ? $area : null);
}

// ---- /documentos/subir ----
if ($uri === '/documentos/subir') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        volver_documento($volver);
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
        volver_documento($volver, 'error');
    }

    $archivoId = (int) ($_POST['archivo_id'] ?? 0);
    // area viene de la fila misma (si es direccion_documentos) — más
    // confiable que confiar en lo que mande el formulario.
    $campoArea = $esDireccion ? ', area' : '';
    $stmt = $pdo->prepare("SELECT id{$campoArea} FROM {$TABLA} WHERE id = :id");
    $stmt->execute([':id' => $archivoId]);
    $filaDoc = $stmt->fetch();
    if (!$filaDoc) {
        volver_documento($volver, 'error');
    }
    $area = $esDireccion ? $filaDoc['area'] : null;

    if (empty($_FILES['archivo']['tmp_name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        volver_documento($volver, 'error', $area);
    }
    if ($_FILES['archivo']['size'] > $TAMANO_MAXIMO) {
        volver_documento($volver, 'tamano', $area);
    }

    $tmp = $_FILES['archivo']['tmp_name'];

    // El mimetype real del contenido, no el que manda el navegador (ese lo
    // puede falsificar cualquiera con solo renombrar el archivo) — mismo
    // criterio que ya usa PerfilController.php con getimagesize() para fotos.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset($TIPOS_PERMITIDOS[$mime])) {
        volver_documento($volver, 'formato', $area);
    }

    if (!is_dir($carpetaArchivos)) {
        mkdir($carpetaArchivos, 0775, true);
    }

    // Nombre fijo armado por el servidor a partir de la tabla + id de la
    // fila — nunca del nombre que mande el navegador, para no arriesgar
    // path traversal ni pisar el archivo de otro documento.
    $nombreArchivo = $PREFIJO . '_' . $archivoId . '.' . $TIPOS_PERMITIDOS[$mime];
    if (!move_uploaded_file($tmp, $carpetaArchivos . '/' . $nombreArchivo)) {
        volver_documento($volver, 'error', $area);
    }

    $stmt = $pdo->prepare("UPDATE {$TABLA} SET archivo = :archivo WHERE id = :id");
    $stmt->execute([':archivo' => $nombreArchivo, ':id' => $archivoId]);

    volver_documento($volver, '1', $area);
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

    // Mismo criterio que CarpetaController.php: PDF se puede ver directo
    // en el navegador, así que se sirve "inline" en vez de forzar la
    // descarga (Word/Excel/PowerPoint siguen sin visor nativo).
    $mimePorExtension = array_flip($TIPOS_PERMITIDOS);
    $mimeReal = $mimePorExtension[$extension] ?? 'application/octet-stream';
    $previsualizable = $mimeReal === 'application/pdf';

    header('Content-Type: ' . $mimeReal);
    header('Content-Disposition: ' . ($previsualizable ? 'inline' : 'attachment') . '; filename="' . $nombreDescarga . '"');
    header('Content-Length: ' . filesize($ruta));
    header('Cache-Control: no-store');
    readfile($ruta);
    exit;
}
