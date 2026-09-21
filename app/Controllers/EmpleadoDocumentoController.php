<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/EmpleadoDocumentoController.php
//  Los 3 submódulos nuevos de Talento Humano (migración 048): Hojas de
//  vida / Contratos / Certificaciones laborales — un archivo por empleado
//  y por tipo, cada tabla con sus propios campos de dominio (Contratos
//  necesita tipo_contrato/fecha_inicio/fecha_fin; los otros 2 solo
//  nombre/fecha). Mismo patrón finfo/CSRF/nombrado-en-disco que
//  DocumentoController.php, pero como archivo hermano (no se fusiona ahí
//  para no arriesgar los 2 flujos que ya están en producción).
//  El tipo se resuelve por el PREFIJO de la URL, no por un ?tipo= — así
//  cada ruta pertenece sin ambigüedad a su propia clave de
//  config/modulos.php (para el veto independiente por rol).
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$storage = ROOT_PATH . '/storage/talento_humano_documentos';

$TIPOS_PERMITIDOS = [
    'application/pdf'                                                           => 'pdf',
    'application/msword'                                                        => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
    'application/vnd.ms-excel'                                                  => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
    'application/vnd.ms-powerpoint'                                             => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
];
$TAMANO_MAXIMO = 20 * 1024 * 1024; // 20 MB

$MAPA_TIPO = [
    '/talento-humano/hojas-de-vida' => [
        'tabla' => 'hojas_de_vida', 'prefijo' => 'hoja', 'clave_modulo' => 'talento-humano-hojas-de-vida',
        'vista' => 'Talento_Humano/Hojas_De_Vida.php', 'titulo' => 'Hojas de vida',
    ],
    '/talento-humano/contratos' => [
        'tabla' => 'contratos', 'prefijo' => 'contrato', 'clave_modulo' => 'talento-humano-contratos',
        'vista' => 'Talento_Humano/Contratos.php', 'titulo' => 'Contratos',
    ],
    '/talento-humano/certificaciones-laborales' => [
        'tabla' => 'certificaciones_laborales', 'prefijo' => 'certificacion', 'clave_modulo' => 'talento-humano-certificaciones-laborales',
        'vista' => 'Talento_Humano/Certificaciones.php', 'titulo' => 'Certificaciones laborales',
    ],
];

$rutaBase = null;
$accion = null; // null = página de listado (GET)
foreach (array_keys($MAPA_TIPO) as $prefijo) {
    if (preg_match('#^' . preg_quote($prefijo, '#') . '(?:/(subir|descargar|eliminar))?$#', $uri, $m)) {
        $rutaBase = $prefijo;
        $accion = $m[1] ?? null;
        break;
    }
}
if ($rutaBase === null) {
    http_response_code(404);
    mostrar_error(404);
    exit;
}
$tipo = $MAPA_TIPO[$rutaBase];
$TABLA = $tipo['tabla'];
$PREFIJO = $tipo['prefijo'];
$claveModulo = $tipo['clave_modulo'];

$stmt = $pdo->prepare('SELECT id FROM direcciones WHERE slug = :slug');
$stmt->execute([':slug' => 'talento-humano']);
$direccionTalentoHumanoId = (int) ($stmt->fetchColumn() ?: 0);

function empleado_doc_volver(string $rutaBase, ?string $resultado = null): void
{
    $qs = $resultado !== null ? ('?doc=' . $resultado) : '';
    header('Location: ' . BASE_URL . $rutaBase . $qs);
    exit;
}

// ---- GET /talento-humano/{hojas-de-vida|contratos|certificaciones-laborales} ----
if ($accion === null) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        empleado_doc_volver($rutaBase);
    }
    // Mismo bloqueo por área que el resto de los módulos de dirección
    // (ver PortalController.php) — un usuario con área asignada a otra
    // dirección no puede entrar aquí ni por URL directa.
    $areaAsignada = usuario_area_asignada();
    if ($areaAsignada !== null && $areaAsignada !== $direccionTalentoHumanoId) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!usuario_puede_ver_archivo_de($claveModulo)) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }

    $titulo = $tipo['titulo'];
    $empleados = $pdo->query("SELECT e.*, d.id AS doc_id, d.nombre AS doc_nombre, d.archivo AS doc_archivo, d.peso_bytes AS doc_peso, d.creado_en AS doc_creado_en" .
        ($TABLA === 'contratos' ? ', d.tipo_contrato, d.fecha_inicio, d.fecha_fin' : ($TABLA === 'certificaciones_laborales' ? ', d.fecha_expedicion' : ', d.fecha')) .
        " FROM empleados e LEFT JOIN {$TABLA} d ON d.empleado_id = e.id ORDER BY e.nombre_completo")->fetchAll();
    $puedeAdministrar = usuario_admin_de($direccionTalentoHumanoId ?: null);

    require ROOT_PATH . '/app/Views/Portal/' . $tipo['vista'];
    exit;
}

// Solo subir/eliminar son escritura y exigen admin de la dirección + módulo
// no vetado + CSRF. Descargar es de solo lectura — cualquiera con acceso de
// lectura al módulo puede descargar (mismo criterio que DocumentoController.php/
// CarpetaController.php, que nunca exigen admin para descargar); su propio
// permiso se resuelve más abajo, en su propio bloque.
if ($accion === 'subir' || $accion === 'eliminar') {
    if (!usuario_admin_de($direccionTalentoHumanoId ?: null) || !usuario_puede_ver_archivo_de($claveModulo)) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        empleado_doc_volver($rutaBase);
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        empleado_doc_volver($rutaBase, 'error');
    }
}

// ---- .../subir ----
// Un solo paso: crea la fila del documento Y le adjunta el archivo en la
// misma petición (a diferencia de DocumentoController.php, que separa
// crear/subir en 2 pasos por herencia de una tabla que ya existía sin
// columna archivo) — acá no tiene sentido dejar un documento "creado" sin
// su archivo, mismo criterio de UX que ya usa CarpetaController.php.
if ($accion === 'subir') {
    $empleadoId = (int) ($_POST['empleado_id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    if ($empleadoId <= 0 || $nombre === '') {
        empleado_doc_volver($rutaBase, 'nombre');
    }
    $stmt = $pdo->prepare('SELECT id FROM empleados WHERE id = :id');
    $stmt->execute([':id' => $empleadoId]);
    if (!$stmt->fetch()) {
        empleado_doc_volver($rutaBase, 'error');
    }

    if (empty($_FILES['archivo']['tmp_name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        empleado_doc_volver($rutaBase, 'error');
    }
    if ($_FILES['archivo']['size'] > $TAMANO_MAXIMO) {
        empleado_doc_volver($rutaBase, 'tamano');
    }
    $tmp = $_FILES['archivo']['tmp_name'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset($TIPOS_PERMITIDOS[$mime])) {
        empleado_doc_volver($rutaBase, 'formato');
    }

    // uq_*_empleado (migración 051) impide 2 filas para el mismo empleado en
    // esta tabla — un doble clic/reintento de red en este mismo formulario ya
    // no puede duplicar el documento, la BD lo rechaza y se avisa en vez de
    // dejar dos filas silenciosas para el mismo empleado.
    try {
        if ($TABLA === 'contratos') {
            $tipoContrato = $_POST['tipo_contrato'] ?? '';
            $fechaInicio = $_POST['fecha_inicio'] ?? '';
            $fechaFin = trim($_POST['fecha_fin'] ?? '') ?: null;
            if (!in_array($tipoContrato, ['termino_fijo', 'indefinido', 'prestacion_servicios'], true)) {
                empleado_doc_volver($rutaBase, 'nombre');
            }
            $fInicio = DateTime::createFromFormat('Y-m-d', $fechaInicio);
            if (!$fInicio || $fInicio->format('Y-m-d') !== $fechaInicio) {
                empleado_doc_volver($rutaBase, 'nombre');
            }
            if ($fechaFin !== null) {
                $fFin = DateTime::createFromFormat('Y-m-d', $fechaFin);
                if (!$fFin || $fFin->format('Y-m-d') !== $fechaFin) {
                    empleado_doc_volver($rutaBase, 'nombre');
                }
            }
            $pdo->prepare("INSERT INTO contratos (empleado_id, nombre, tipo_contrato, fecha_inicio, fecha_fin, archivo) VALUES (:eid, :nombre, :tipo, :inicio, :fin, '')")
                ->execute([':eid' => $empleadoId, ':nombre' => $nombre, ':tipo' => $tipoContrato, ':inicio' => $fechaInicio, ':fin' => $fechaFin]);
            $docId = (int) $pdo->lastInsertId();
        } else {
            $campoFecha = $TABLA === 'certificaciones_laborales' ? 'fecha_expedicion' : 'fecha';
            $fecha = $_POST[$campoFecha] ?? ($_POST['fecha'] ?? '');
            $f = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$f || $f->format('Y-m-d') !== $fecha) {
                empleado_doc_volver($rutaBase, 'nombre');
            }
            $pdo->prepare("INSERT INTO {$TABLA} (empleado_id, nombre, {$campoFecha}, archivo) VALUES (:eid, :nombre, :fecha, '')")
                ->execute([':eid' => $empleadoId, ':nombre' => $nombre, ':fecha' => $fecha]);
            $docId = (int) $pdo->lastInsertId();
        }
    } catch (PDOException $e) {
        error_log('EmpleadoDocumento: no se pudo crear (' . $TABLA . ', empleado ' . $empleadoId . '): ' . $e->getMessage());
        empleado_doc_volver($rutaBase, 'duplicado');
    }
    if (!$docId) {
        empleado_doc_volver($rutaBase, 'error');
    }

    if (!is_dir($storage)) {
        mkdir($storage, 0775, true);
    }
    $nombreArchivo = $PREFIJO . '_' . $docId . '.' . $TIPOS_PERMITIDOS[$mime];
    if (!move_uploaded_file($tmp, $storage . '/' . $nombreArchivo)) {
        // El archivo no se pudo guardar — no dejar una fila huérfana sin
        // archivo (mismo cuidado que DocumentoController.php/CarpetaController.php).
        $pdo->prepare("DELETE FROM {$TABLA} WHERE id = :id")->execute([':id' => $docId]);
        empleado_doc_volver($rutaBase, 'error');
    }

    $pdo->prepare("UPDATE {$TABLA} SET archivo = :archivo, peso_bytes = :peso WHERE id = :id")
        ->execute([':archivo' => $nombreArchivo, ':peso' => (int) $_FILES['archivo']['size'], ':id' => $docId]);

    // Sin sincronización a Google Drive personal a propósito: esa función
    // (ver GoogleDrive.php) exige una columna google_drive_file_id que estas
    // 3 tablas no tienen — agregarla es una expansión de alcance no pedida,
    // así que se deja fuera en vez de dejar una llamada que aparenta
    // sincronizar y en realidad no hace nada.

    empleado_doc_volver($rutaBase, '1');
}

// ---- .../eliminar ----
if ($accion === 'eliminar') {
    $docId = (int) ($_POST['doc_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT archivo FROM {$TABLA} WHERE id = :id");
    $stmt->execute([':id' => $docId]);
    $fila = $stmt->fetch();
    if (!$fila) {
        // id inexistente (ya borrado por otra pestaña, doble clic, etc.) —
        // no reportar "eliminado" como si de verdad hubiera pasado algo.
        empleado_doc_volver($rutaBase, 'error');
    }
    if ($fila['archivo'] && is_file($storage . '/' . $fila['archivo'])) {
        unlink($storage . '/' . $fila['archivo']);
    }
    $pdo->prepare("DELETE FROM {$TABLA} WHERE id = :id")->execute([':id' => $docId]);
    empleado_doc_volver($rutaBase, 'eliminado');
}

// ---- .../descargar ----
if ($accion === 'descargar') {
    $docId = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT nombre, archivo FROM {$TABLA} WHERE id = :id");
    $stmt->execute([':id' => $docId]);
    $fila = $stmt->fetch();

    // Mismo criterio que DocumentoController.php: quien no es admin global
    // necesita el módulo (de este tipo de documento, no de la URL con que
    // se pidió) sin vetar, más el bloqueo por área. Todo deniego da el
    // mismo 403, para no revelar qué ids existen.
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        $areaAsignada = usuario_area_asignada();
        if (!$fila
            || !usuario_puede_ver_archivo_de($claveModulo)
            || ($areaAsignada !== null && $areaAsignada !== $direccionTalentoHumanoId)) {
            http_response_code(403);
            mostrar_error(403);
            exit;
        }
    }

    if (!$fila || !$fila['archivo']) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $ruta = $storage . '/' . $fila['archivo'];
    if (!is_file($ruta)) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $extension = pathinfo($fila['archivo'], PATHINFO_EXTENSION);
    $nombreDescarga = preg_replace('/[^A-Za-z0-9 _.-]/', '', $fila['nombre']) . '.' . $extension;

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

empleado_doc_volver($rutaBase);
