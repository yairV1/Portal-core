<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/TrabajoController.php
//  "Trabaja con nosotros": landing pública de vacantes + postulación
//  (sin sesión), y panel admin para revisarlas (con sesión + rol admin).
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

$carpetaPostulaciones = ROOT_PATH . '/storage/postulaciones';

// ---- /trabaja-con-nosotros (pública) ----
if ($uri === '/trabaja-con-nosotros') {
    $vacantes = $pdo->query('SELECT id, titulo, area, tipo_contrato, descripcion FROM vacantes WHERE activa = 1 ORDER BY orden')->fetchAll();
    require ROOT_PATH . '/app/Views/Landing/Trabajo.php';
    exit;
}

// ---- /trabaja-con-nosotros/postular (pública, POST) ----
if ($uri === '/trabaja-con-nosotros/postular') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros');
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=error');
        exit;
    }

    // Honeypot: campo oculto vía CSS que ningún humano llena, pero un bot
    // que autocompleta todo sí. Si llega con contenido, se responde como
    // si hubiera funcionado — sin guardar nada — para no darle pistas de
    // que lo detectamos (si le devolviéramos un error, ajustaría el bot).
    if (trim($_POST['sitio_web'] ?? '') !== '') {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=1');
        exit;
    }

    // Límite por IP: este formulario no tiene sesión ni captcha, así que
    // es la única barrera contra el envío masivo automatizado. 3 por hora
    // deja margen para reintentos legítimos (ej. se equivocó de archivo)
    // sin permitir un bombardeo.
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM postulaciones WHERE ip = :ip AND creado_en > NOW() - INTERVAL 1 HOUR');
    $stmt->execute([':ip' => $ip]);
    if ((int) $stmt->fetchColumn() >= 3) {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=limite');
        exit;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cargoAplicado = trim($_POST['cargo_aplicado'] ?? '');
    $vacanteId = (($_POST['vacante_id'] ?? '') !== '') ? (int) $_POST['vacante_id'] : null;

    $valido = $nombre !== '' && mb_strlen($nombre) <= 150
        && filter_var($correo, FILTER_VALIDATE_EMAIL)
        && preg_match('/^[0-9+\-\s]{7,30}$/', $telefono)
        && $cargoAplicado !== '' && mb_strlen($cargoAplicado) <= 150
        && !empty($_FILES['hoja_vida']['tmp_name']) && $_FILES['hoja_vida']['error'] === UPLOAD_ERR_OK
        && !empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK;

    if (!$valido) {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=error');
        exit;
    }

    if ($_FILES['hoja_vida']['size'] > 5 * 1024 * 1024 || $_FILES['foto']['size'] > 2 * 1024 * 1024) {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=tamano');
        exit;
    }

    // Hoja de vida: solo PDF real (contenido, no la extensión que manda
    // el navegador — mismo criterio que DocumentoController.php).
    $mimeCv = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['hoja_vida']['tmp_name']);
    if ($mimeCv !== 'application/pdf') {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=formato');
        exit;
    }

    // Foto: mismo criterio que PerfilController.php (getimagesize real).
    $infoFoto = @getimagesize($_FILES['foto']['tmp_name']);
    $extensionesFoto = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if ($infoFoto === false || !isset($extensionesFoto[$infoFoto[2]])) {
        header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=formato');
        exit;
    }

    // Inserta primero (sin archivo todavía) para tener el id con el que
    // se nombran los archivos en disco — mismo orden que ya usan
    // DocumentoController.php/PerfilController.php.
    $stmt = $pdo->prepare('
        INSERT INTO postulaciones (vacante_id, nombre, correo, telefono, cargo_aplicado, ip)
        VALUES (:vacante_id, :nombre, :correo, :telefono, :cargo_aplicado, :ip)
    ');
    $stmt->execute([
        ':vacante_id'     => $vacanteId,
        ':nombre'         => $nombre,
        ':correo'         => $correo,
        ':telefono'       => $telefono,
        ':cargo_aplicado' => $cargoAplicado,
        ':ip'             => $ip,
    ]);
    $postulacionId = (int) $pdo->lastInsertId();

    if (!is_dir($carpetaPostulaciones)) {
        mkdir($carpetaPostulaciones, 0775, true);
    }

    $nombreCv = 'postulacion_' . $postulacionId . '_cv.pdf';
    move_uploaded_file($_FILES['hoja_vida']['tmp_name'], $carpetaPostulaciones . '/' . $nombreCv);

    $nombreFoto = 'postulacion_' . $postulacionId . '_foto.' . $extensionesFoto[$infoFoto[2]];
    move_uploaded_file($_FILES['foto']['tmp_name'], $carpetaPostulaciones . '/' . $nombreFoto);

    $stmt = $pdo->prepare('UPDATE postulaciones SET hoja_vida_archivo = :cv, foto_archivo = :foto WHERE id = :id');
    $stmt->execute([':cv' => $nombreCv, ':foto' => $nombreFoto, ':id' => $postulacionId]);

    header('Location: ' . BASE_URL . '/trabaja-con-nosotros?postulacion=1');
    exit;
}

// ---- /postulaciones y /postulaciones/descargar (admin) ----
if ($uri === '/postulaciones' || $uri === '/postulaciones/descargar') {
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

if ($uri === '/postulaciones') {
    $postulaciones = $pdo->query('
        SELECT p.id, p.nombre, p.correo, p.telefono, p.cargo_aplicado,
               p.hoja_vida_archivo, p.foto_archivo, p.creado_en, v.titulo AS vacante_titulo
        FROM postulaciones p
        LEFT JOIN vacantes v ON v.id = p.vacante_id
        ORDER BY p.creado_en DESC
    ')->fetchAll();
    require ROOT_PATH . '/app/Views/Portal/Postulaciones/Postulaciones.php';
    exit;
}

if ($uri === '/postulaciones/descargar') {
    $tipo = $_GET['tipo'] ?? '';
    $id = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT nombre, hoja_vida_archivo, foto_archivo FROM postulaciones WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    $archivo = $tipo === 'foto' ? ($fila['foto_archivo'] ?? null) : ($fila['hoja_vida_archivo'] ?? null);

    if (!$fila || !$archivo) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $ruta = $carpetaPostulaciones . '/' . $archivo;
    if (!is_file($ruta)) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    $extension = pathinfo($archivo, PATHINFO_EXTENSION);
    $sufijo = $tipo === 'foto' ? 'foto' : 'CV';
    $nombreDescarga = preg_replace('/[^A-Za-z0-9 _.-]/', '', $fila['nombre']) . ' - ' . $sufijo . '.' . $extension;

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
    header('Content-Length: ' . filesize($ruta));
    header('Cache-Control: no-store');
    readfile($ruta);
    exit;
}
