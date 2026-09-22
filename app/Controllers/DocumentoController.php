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

// google_drive_oauth_sincronizar_archivo(): ver CarpetaController.php —
// misma sincronización automática hacia el Drive de quien sube el archivo.
require_once ROOT_PATH . '/app/Helpers/GoogleDrive.php';

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
// a la URL, sin cambiarles nada. $carpeta (solo archivos_documentales, ver
// migración 053) hace lo mismo para no perder la carpeta en la que estabas
// parado dentro de Gestión Documental al volver de crear/subir un archivo.
function volver_documento(string $volver, ?string $resultado = null, ?string $area = null, ?int $carpeta = null): void
{
    $params = [];
    if ($carpeta !== null) $params['carpeta'] = $carpeta;
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
    // carpeta_id crudo del formulario (sin validar todavía) — solo para
    // poder volver al mismo lugar en Gestión Documental pase lo que pase;
    // la validación real contra carpetas_documentales sigue pasando abajo.
    $carpetaVolver = !$esDireccion ? ((int) ($_POST['carpeta_id'] ?? 0) ?: null) : null;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        volver_documento($volver);
    }
    // direccion_documentos (esDireccion) se puede delegar a un
    // admin_direccion atado a esa misma dirección (ver migración 036);
    // archivos_documentales (Gestión Documental) sigue solo para admin
    // global — no tiene direccion_id, así que usuario_admin_de(null) ya
    // exige 'admin' de por sí.
    if (!usuario_admin_de($esDireccion ? (int) ($_POST['direccion_id'] ?? 0) : null) || ($esDireccion && !usuario_puede_accion('documentos.crear'))) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    // Además de ser admin de esa dirección (arriba, sin cambios), su módulo no
    // puede estar vetado para este rol (Permisos por rol): el veto se suma.
    if (!usuario_puede_ver_archivo_de($esDireccion ? modulo_de_direccion((int) ($_POST['direccion_id'] ?? 0)) : 'gestion-documental')) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        volver_documento($volver, 'error', $area, $carpetaVolver);
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = trim($_POST['tipo_doc'] ?? '');
    $version = trim($_POST['version'] ?? '') ?: 'v1.0';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
    if ($nombre === '' || !$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
        volver_documento($volver, 'nombre', $area, $carpetaVolver);
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
        // Los documentos solo cuelgan del nivel de "área" (parent_id NOT
        // NULL), nunca del de "dirección" (ver el comentario de
        // PortalController.php) — la vista ya no ofrece el botón de
        // agregar fuera de un área, pero esto lo exige también acá, no
        // solo en la UI.
        $stmt = $pdo->prepare('SELECT id FROM carpetas_documentales WHERE id = :id AND parent_id IS NOT NULL');
        $stmt->execute([':id' => $carpetaId]);
        if (!$stmt->fetch()) {
            volver_documento($volver, 'error', null, $carpetaVolver);
        }
        $responsable = trim($_POST['responsable'] ?? '');
        $pdo->prepare('INSERT INTO archivos_documentales (carpeta_id, nombre, tipo, version, estado, responsable, fecha) VALUES (:cid, :nombre, :tipo, :version, :estado, :responsable, :fecha)')
            ->execute([':cid' => $carpetaId, ':nombre' => $nombre, ':tipo' => $tipo, ':version' => $version, ':estado' => 'Vigente', ':responsable' => $responsable, ':fecha' => $fecha]);
    }

    volver_documento($volver, 'creado', $esDireccion ? $area : null, $carpetaVolver);
}

// ---- /documentos/subir ----
if ($uri === '/documentos/subir') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        volver_documento($volver);
    }

    $archivoId = (int) ($_POST['archivo_id'] ?? 0);
    // area/direccion_id vienen de la fila misma (si es direccion_documentos)
    // — más confiable que confiar en lo que mande el formulario, y hace
    // falta el direccion_id real para saber si un admin_direccion puede
    // subir acá (ver migración 036). carpeta_id (archivos_documentales) es
    // lo mismo pero para no perder la carpeta al volver a Gestión
    // Documental (ver migración 053).
    $campoArea = $esDireccion ? ', area, direccion_id' : ', carpeta_id';
    $stmt = $pdo->prepare("SELECT id{$campoArea} FROM {$TABLA} WHERE id = :id");
    $stmt->execute([':id' => $archivoId]);
    $filaDoc = $stmt->fetch();
    if (!$filaDoc) {
        // Para quien no es admin global un id inexistente da el mismo 403 que
        // uno al que no tiene derecho (no revela qué ids existen); el admin
        // global sí recibe el aviso de error de siempre.
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            http_response_code(403);
            mostrar_error(403);
            exit;
        }
        volver_documento($volver, 'error');
    }
    $carpetaVolver = !$esDireccion ? (int) $filaDoc['carpeta_id'] : null;

    if (!usuario_admin_de($esDireccion ? (int) $filaDoc['direccion_id'] : null) || ($esDireccion && !usuario_puede_accion('documentos.subir'))) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    // Además de ser admin de esa dirección (arriba, sin cambios), el módulo de
    // ESTE documento no puede estar vetado para este rol: el veto se suma.
    if (!usuario_puede_ver_archivo_de($esDireccion ? modulo_de_direccion((int) $filaDoc['direccion_id']) : 'gestion-documental')) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        volver_documento($volver, 'error', null, $carpetaVolver);
    }
    $area = $esDireccion ? $filaDoc['area'] : null;

    if (empty($_FILES['archivo']['tmp_name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        volver_documento($volver, 'error', $area, $carpetaVolver);
    }
    if ($_FILES['archivo']['size'] > $TAMANO_MAXIMO) {
        volver_documento($volver, 'tamano', $area, $carpetaVolver);
    }

    $tmp = $_FILES['archivo']['tmp_name'];

    // El mimetype real del contenido, no el que manda el navegador (ese lo
    // puede falsificar cualquiera con solo renombrar el archivo) — mismo
    // criterio que ya usa PerfilController.php con getimagesize() para fotos.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset($TIPOS_PERMITIDOS[$mime])) {
        volver_documento($volver, 'formato', $area, $carpetaVolver);
    }

    if (!is_dir($carpetaArchivos)) {
        mkdir($carpetaArchivos, 0775, true);
    }

    // Nombre fijo armado por el servidor a partir de la tabla + id de la
    // fila — nunca del nombre que mande el navegador, para no arriesgar
    // path traversal ni pisar el archivo de otro documento.
    $nombreArchivo = $PREFIJO . '_' . $archivoId . '.' . $TIPOS_PERMITIDOS[$mime];
    if (!move_uploaded_file($tmp, $carpetaArchivos . '/' . $nombreArchivo)) {
        volver_documento($volver, 'error', $area, $carpetaVolver);
    }

    $stmt = $pdo->prepare("UPDATE {$TABLA} SET archivo = :archivo WHERE id = :id");
    $stmt->execute([':archivo' => $nombreArchivo, ':id' => $archivoId]);

    google_drive_oauth_sincronizar_archivo($pdo, (int) $_SESSION['usuario_id'], $TABLA, $archivoId, $carpetaArchivos . '/' . $nombreArchivo, $nombreArchivo, $mime);

    volver_documento($volver, '1', $area, $carpetaVolver);
}

// ---- /documentos/visibilidad ----
// Alterna público/privado de un documento de Gestión Documental (ver
// migración 053_gestion_documental_publico.sql) — solo archivos_documentales
// tiene este concepto, direccion_documentos no, así que esta acción no pasa
// por la mecánica $esDireccion/$TABLA de arriba: siempre opera sobre
// archivos_documentales.
if ($uri === '/documentos/visibilidad') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/gestion-documental');
        exit;
    }
    if (!usuario_admin_de(null) || !usuario_puede_ver_archivo_de('gestion-documental')) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/gestion-documental?doc=error');
        exit;
    }

    $archivoId = (int) ($_POST['archivo_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, carpeta_id, visibilidad FROM archivos_documentales WHERE id = :id');
    $stmt->execute([':id' => $archivoId]);
    $filaDoc = $stmt->fetch();
    if (!$filaDoc) {
        header('Location: ' . BASE_URL . '/gestion-documental?doc=error');
        exit;
    }

    $nuevaVisibilidad = $filaDoc['visibilidad'] === 'publico' ? 'privado' : 'publico';
    $pdo->prepare('UPDATE archivos_documentales SET visibilidad = :v WHERE id = :id')
        ->execute([':v' => $nuevaVisibilidad, ':id' => $archivoId]);

    header('Location: ' . BASE_URL . '/gestion-documental?carpeta=' . (int) $filaDoc['carpeta_id'] . '&doc=visibilidad');
    exit;
}

// ---- /documentos/editar ----
// Edita nombre/tipo/versión/responsable/fecha de un documento ya existente
// de Gestión Documental (no toca el archivo adjunto, solo su metadata).
if ($uri === '/documentos/editar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/gestion-documental');
        exit;
    }
    if (!usuario_admin_de(null) || !usuario_puede_ver_archivo_de('gestion-documental')) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/gestion-documental?doc=error');
        exit;
    }

    $archivoId = (int) ($_POST['archivo_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, carpeta_id FROM archivos_documentales WHERE id = :id');
    $stmt->execute([':id' => $archivoId]);
    $filaDoc = $stmt->fetch();
    if (!$filaDoc) {
        header('Location: ' . BASE_URL . '/gestion-documental?doc=error');
        exit;
    }

    $nombreEdit = trim($_POST['nombre'] ?? '');
    $tipoEdit = trim($_POST['tipo_doc'] ?? '');
    $versionEdit = trim($_POST['version'] ?? '') ?: 'v1.0';
    $responsableEdit = trim($_POST['responsable'] ?? '');
    $fechaEdit = $_POST['fecha'] ?? '';
    $fechaEditValida = DateTime::createFromFormat('Y-m-d', $fechaEdit);
    if ($nombreEdit === '' || !$fechaEditValida || $fechaEditValida->format('Y-m-d') !== $fechaEdit) {
        header('Location: ' . BASE_URL . '/gestion-documental?carpeta=' . (int) $filaDoc['carpeta_id'] . '&doc=nombre');
        exit;
    }

    $pdo->prepare('UPDATE archivos_documentales SET nombre = :nombre, tipo = :tipo, version = :version, responsable = :responsable, fecha = :fecha WHERE id = :id')
        ->execute([':nombre' => $nombreEdit, ':tipo' => $tipoEdit, ':version' => $versionEdit, ':responsable' => $responsableEdit, ':fecha' => $fechaEdit, ':id' => $archivoId]);

    header('Location: ' . BASE_URL . '/gestion-documental?carpeta=' . (int) $filaDoc['carpeta_id'] . '&doc=editado');
    exit;
}

// ---- /documentos/eliminar ----
// "Eliminar" acá es soft delete (activo = 0, ver migración 053) — el
// registro se queda en la base y el archivo en disco, solo deja de
// aparecer en el listado (público o de administración). No hay forma de
// restaurarlo desde la interfaz todavía.
if ($uri === '/documentos/eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/gestion-documental');
        exit;
    }
    if (!usuario_admin_de(null) || !usuario_puede_ver_archivo_de('gestion-documental')) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/gestion-documental?doc=error');
        exit;
    }

    $archivoId = (int) ($_POST['archivo_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, carpeta_id FROM archivos_documentales WHERE id = :id');
    $stmt->execute([':id' => $archivoId]);
    $filaDoc = $stmt->fetch();
    if (!$filaDoc) {
        header('Location: ' . BASE_URL . '/gestion-documental?doc=error');
        exit;
    }

    $pdo->prepare('UPDATE archivos_documentales SET activo = 0 WHERE id = :id')->execute([':id' => $archivoId]);

    header('Location: ' . BASE_URL . '/gestion-documental?carpeta=' . (int) $filaDoc['carpeta_id'] . '&doc=eliminado');
    exit;
}

// ---- /documentos/descargar ----
if ($uri === '/documentos/descargar') {
    $archivoId = (int) ($_GET['id'] ?? 0);
    // visibilidad/activo (ver migración 053_gestion_documental_publico.sql)
    // solo existen en archivos_documentales — hace falta traerlas para que
    // "privado"/"eliminado" en Gestión Documental también bloquee la
    // descarga directa por id, no solo el listado.
    $campoDireccion = $esDireccion ? ', direccion_id' : ', visibilidad, activo';
    $stmt = $pdo->prepare("SELECT nombre, archivo{$campoDireccion} FROM {$TABLA} WHERE id = :id");
    $stmt->execute([':id' => $archivoId]);
    $fila = $stmt->fetch();

    // Quien no es admin global necesita permiso sobre el módulo DEL ARCHIVO:
    // la dirección de la fila (direccion_documentos) o 'gestion-documental'
    // (archivos_documentales, que no tiene dirección) — no sobre la URL. Más el
    // bloqueo por área de PortalController.php/CarpetaController.php (ver
    // usuario_area_asignada(), solo aplica a direccion_documentos), más —
    // archivos_documentales únicamente— que el documento esté publicado y
    // activo (si no, ni el link directo por id debe servirlo, igual que ya
    // no aparece en el listado). Todo deniego —no existe, módulo sin
    // resolver, vetado, otra área, privado/eliminado— da el mismo 403, para
    // no revelar qué ids existen ni cuáles son privados.
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        $areaAsignada = usuario_area_asignada();
        $moduloArchivo = !$fila ? null : ($esDireccion ? modulo_de_direccion((int) $fila['direccion_id']) : 'gestion-documental');
        $noPublicadoDoc = !$esDireccion && $fila
            && (($fila['visibilidad'] ?? 'privado') !== 'publico' || (int) ($fila['activo'] ?? 0) !== 1);
        if (!$fila
            || !usuario_puede_ver_archivo_de($moduloArchivo)
            || ($esDireccion && $areaAsignada !== null && $areaAsignada !== (int) $fila['direccion_id'])
            || $noPublicadoDoc) {
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
