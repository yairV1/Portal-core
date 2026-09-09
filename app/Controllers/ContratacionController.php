<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/ContratacionController.php
//  Formulario de contratación — acceso SIEMPRE por token único
//  (nunca listado ni adivinable, ver migración 016), sin sesión.
//  Panel admin (/contrataciones*) para generar enlaces y revisar lo
//  que llega — con sesión + rol admin.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

// Los 18 documentos del formulario — "tipo" es un valor en BD, no una
// columna (ver migración 016), así que agregar uno nuevo el día de
// mañana es solo agregar una línea acá, sin migración. Entrevista y
// Microclase NO están acá: son etapas presenciales, no documentos.
$TIPOS_DOCUMENTO = [
    'hv_coreducacion'       => ['label' => 'HV COREDUCACIÓN', 'requerido' => true],
    'hv_normal'             => ['label' => 'HV normal', 'requerido' => true],
    'doc_cedula'            => ['label' => 'Cédula de ciudadanía', 'requerido' => true],
    'tarjeta_profesional'   => ['label' => 'Tarjeta profesional', 'requerido' => true],
    'rut'                   => ['label' => 'RUT', 'requerido' => true],
    'cert_seguridad_social' => ['label' => 'Certificado de seguridad social', 'requerido' => true],
    'cert_pension'          => ['label' => 'Certificado de pensión', 'requerido' => true],
    'cert_arl'              => ['label' => 'Certificado ARL', 'requerido' => true],
    'cert_cuenta_bancaria'  => ['label' => 'Certificado cuenta bancaria', 'requerido' => true],
    'cert_procuraduria'     => ['label' => 'Certificado Procuraduría', 'requerido' => true],
    'cert_policia'          => ['label' => 'Certificado Policía', 'requerido' => true],
    'cert_contraloria'      => ['label' => 'Certificado Contraloría', 'requerido' => true],
    'cert_rnmc'             => ['label' => 'Certificado RNMC', 'requerido' => true],
    'ruaf'                  => ['label' => 'RUAF', 'requerido' => true],
    'diploma_posgrado'      => ['label' => 'Acta/diploma de especializaciones o maestrías', 'requerido' => false],
    'cert_laboral'          => ['label' => 'Certificado laboral', 'requerido' => true],
    'carne_vacunas'         => ['label' => 'Carné o certificado de vacunas', 'requerido' => true],
    'otros'                 => ['label' => 'Otros', 'requerido' => false],
];

$CAMPOS_TEXTO = ['nombre', 'cedula', 'celular', 'email', 'estado_civil', 'direccion', 'profesion', 'ciudad', 'cuenta_bancaria', 'nivel_academico', 'eps', 'fondo_pension', 'arl', 'fondo_cesantias'];

$carpetaContrataciones = ROOT_PATH . '/storage/contrataciones';

// ---- /contratacion (pública, por token) ----
if ($uri === '/contratacion') {
    $token = $_GET['token'] ?? '';
    $stmt = $pdo->prepare('SELECT id, usado, expira_en FROM contrataciones WHERE token = :token');
    $stmt->execute([':token' => $token]);
    $fila = $stmt->fetch();

    if (!$fila) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $yaCompletado = (bool) $fila['usado'];
    // Expirado solo importa si todavía no se completó — uno ya usado
    // muestra "ya fue utilizado" sin importar si además venció.
    $expirado = !$yaCompletado && strtotime($fila['expira_en']) < time();
    $reciente = isset($_GET['ok']);
    $errorEnvio = $_GET['error'] ?? null;

    require ROOT_PATH . '/app/Views/Landing/Contratacion.php';
    exit;
}

// ---- /contratacion/enviar (pública, POST) ----
if ($uri === '/contratacion/enviar') {
    $token = $_POST['token'] ?? '';
    $volver = BASE_URL . '/contratacion?token=' . urlencode($token);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . $volver);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, usado, expira_en FROM contrataciones WHERE token = :token');
    $stmt->execute([':token' => $token]);
    $fila = $stmt->fetch();

    // Token inválido, ya usado, o expirado: no hay a dónde volver con ese
    // token (evita reenvíos), manda a la home. La verificación real de
    // "usado" es la del UPDATE atómico más abajo — esta es solo para no
    // hacer trabajo de más con un token que ya se sabe inválido.
    if (!$fila || $fila['usado'] || strtotime($fila['expira_en']) < time()) {
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . $volver . '&error=1');
        exit;
    }

    $contratacionId = (int) $fila['id'];

    $datos = [];
    foreach ($CAMPOS_TEXTO as $campo) {
        $valor = trim($_POST[$campo] ?? '');
        if ($valor === '' || mb_strlen($valor) > 200) {
            header('Location: ' . $volver . '&error=1');
            exit;
        }
        $datos[$campo] = $valor;
    }
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . $volver . '&error=1');
        exit;
    }

    // Documentos reales o PDF real (muchos certificados llegan
    // escaneados/fotografiados) — mismo criterio que
    // DocumentoController.php: se valida el contenido, no la extensión ni
    // el mimetype que manda el navegador.
    $TIPOS_PERMITIDOS = [
        'application/pdf' => 'pdf',
        'image/jpeg'       => 'jpg',
        'image/png'        => 'png',
        'image/webp'       => 'webp',
    ];
    $archivosValidos = [];
    foreach ($TIPOS_DOCUMENTO as $clave => $info) {
        $file = $_FILES[$clave] ?? null;
        $tieneArchivo = !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK;

        if (!$tieneArchivo) {
            if ($info['requerido']) {
                header('Location: ' . $volver . '&error=1');
                exit;
            }
            continue;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            header('Location: ' . $volver . '&error=tamano');
            exit;
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($TIPOS_PERMITIDOS[$mime])) {
            header('Location: ' . $volver . '&error=formato');
            exit;
        }
        $archivosValidos[$clave] = ['tmp' => $file['tmp_name'], 'ext' => $TIPOS_PERMITIDOS[$mime]];
    }

    // Todo válido — se guarda. El "WHERE ... AND usado = 0" es lo que
    // realmente evita el doble envío: si dos peticiones con el mismo
    // token llegan casi al mismo tiempo (doble clic, reintento de red),
    // solo una consigue poner usado=1 acá — la otra ve rowCount() = 0 y
    // se corta antes de tocar archivos, sin depender solo del SELECT de
    // arriba (que por sí solo no evita la carrera).
    $set = implode(', ', array_map(fn($c) => "$c = :$c", $CAMPOS_TEXTO));
    $stmt = $pdo->prepare("UPDATE contrataciones SET {$set}, usado = 1, completado_en = NOW(), ip = :ip WHERE id = :id AND usado = 0");
    $stmt->execute($datos + [':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', ':id' => $contratacionId]);

    if ($stmt->rowCount() === 0) {
        // Otra petición con este mismo token ganó la carrera justo antes.
        header('Location: ' . $volver);
        exit;
    }

    // ...luego los archivos (con el id ya confirmado, para nombrarlos).
    if (!is_dir($carpetaContrataciones)) {
        mkdir($carpetaContrataciones, 0775, true);
    }
    $stmtDoc = $pdo->prepare('INSERT INTO contratacion_documentos (contratacion_id, tipo, archivo) VALUES (:cid, :tipo, :archivo)');
    foreach ($archivosValidos as $clave => $a) {
        $nombreArchivo = 'contratacion_' . $contratacionId . '_' . $clave . '.' . $a['ext'];
        move_uploaded_file($a['tmp'], $carpetaContrataciones . '/' . $nombreArchivo);
        $stmtDoc->execute([':cid' => $contratacionId, ':tipo' => $clave, ':archivo' => $nombreArchivo]);
    }

    header('Location: ' . $volver . '&ok=1');
    exit;
}

// ---- /contrataciones y demás rutas admin ----
if (in_array($uri, ['/contrataciones', '/contrataciones/generar', '/contrataciones/eliminar', '/contrataciones/descargar'], true)) {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
}

if ($uri === '/contrataciones/generar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/contrataciones');
        exit;
    }
    $token = bin2hex(random_bytes(24));
    $nombreReferencia = trim($_POST['nombre_referencia'] ?? '') ?: null;
    $postulacionId = (($_POST['postulacion_id'] ?? '') !== '') ? (int) $_POST['postulacion_id'] : null;

    // Vigencia mínima 15 días — nunca menos, sin importar qué mande el
    // formulario (el "min" del HTML es solo una ayuda visual, esto es lo
    // que de verdad lo garantiza).
    $vigenciaMinimaDias = 15;
    $diasVigencia = (int) ($_POST['dias_vigencia'] ?? $vigenciaMinimaDias);
    if ($diasVigencia < $vigenciaMinimaDias) {
        $diasVigencia = $vigenciaMinimaDias;
    }
    $expiraEn = (new DateTime())->modify('+' . $diasVigencia . ' days')->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare('INSERT INTO contrataciones (token, nombre_referencia, postulacion_id, expira_en) VALUES (:token, :ref, :pid, :expira)');
    $stmt->execute([':token' => $token, ':ref' => $nombreReferencia, ':pid' => $postulacionId, ':expira' => $expiraEn]);

    header('Location: ' . BASE_URL . '/contrataciones?nuevoToken=' . $token);
    exit;
}

if ($uri === '/contrataciones/eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/contrataciones');
        exit;
    }
    // Solo borra invitaciones sin usar — nunca una fila con datos reales
    // de un candidato ya enviados.
    $stmt = $pdo->prepare('DELETE FROM contrataciones WHERE id = :id AND usado = 0');
    $stmt->execute([':id' => (int) ($_POST['id'] ?? 0)]);

    header('Location: ' . BASE_URL . '/contrataciones');
    exit;
}

if ($uri === '/contrataciones/descargar') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('
        SELECT d.archivo, d.tipo, c.nombre
        FROM contratacion_documentos d
        JOIN contrataciones c ON c.id = d.contratacion_id
        WHERE d.id = :id
    ');
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    if (!$fila) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }
    $ruta = $carpetaContrataciones . '/' . $fila['archivo'];
    if (!is_file($ruta)) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $etiquetaTipo = $TIPOS_DOCUMENTO[$fila['tipo']]['label'] ?? $fila['tipo'];
    $extension = pathinfo($fila['archivo'], PATHINFO_EXTENSION);
    $nombreDescarga = preg_replace('/[^A-Za-z0-9 _.-]/', '', $fila['nombre'] . ' - ' . $etiquetaTipo) . '.' . $extension;

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
    header('Content-Length: ' . filesize($ruta));
    header('Cache-Control: no-store');
    readfile($ruta);
    exit;
}

if ($uri === '/contrataciones') {
    $contrataciones = $pdo->query('
        SELECT c.*, p.nombre AS postulacion_nombre
        FROM contrataciones c
        LEFT JOIN postulaciones p ON p.id = c.postulacion_id
        ORDER BY c.generado_en DESC
    ')->fetchAll();

    $documentosPorContratacion = [];
    foreach ($pdo->query('SELECT id, contratacion_id, tipo, archivo FROM contratacion_documentos ORDER BY contratacion_id')->fetchAll() as $d) {
        $documentosPorContratacion[$d['contratacion_id']][] = $d;
    }

    $postulacionesParaVincular = $pdo->query('SELECT id, nombre FROM postulaciones ORDER BY creado_en DESC')->fetchAll();
    $nuevoToken = $_GET['nuevoToken'] ?? null;

    // Para armar el enlace completo que el admin copia y le manda al
    // candidato — BASE_URL es solo la ruta, hace falta esquema + host.
    $esquemaOrigen = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $origenAbsoluto = $esquemaOrigen . '://' . $_SERVER['HTTP_HOST'];

    require ROOT_PATH . '/app/Views/Portal/Contrataciones/Contrataciones.php';
    exit;
}
