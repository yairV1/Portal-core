<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/DriveUsuarioController.php
//  Conecta el Google Drive PERSONAL de cada usuario a Gestión Documental
//  — distinto del "Traer de Drive" por link de CarpetaController.php (que
//  habla con Drive como una cuenta de servicio compartida): acá cada quien
//  conecta SU PROPIA cuenta (OAuth de solo lectura) y ve su propia lista
//  de archivos para llevarlos a una carpeta real del portal. Ver
//  GoogleDrive.php (funciones google_drive_oauth_*) y migración
//  041_drive_personal_gestion_documental.sql.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

require_once ROOT_PATH . '/app/Helpers/GoogleDrive.php';

// El mismo Client ID/Secret del login con Google (ver AuthController.php)
// necesita esta URL agregada a "URI de redirección autorizados" en Google
// Cloud Console — es una ruta distinta a la del login, así que hay que
// agregarla aparte (ver .env.example).
function drive_usuario_redirect_uri(): string
{
    $porHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    return ($porHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . BASE_URL . '/gestion-documental/drive/callback';
}

// ---- /gestion-documental/drive/conectar ----
if ($uri === '/gestion-documental/drive/conectar') {
    if (!google_drive_oauth_configurado()) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=no_configurado');
        exit;
    }
    // state anti-CSRF, mismo criterio que /auth/google en AuthController.php.
    $state = bin2hex(random_bytes(16));
    $_SESSION['drive_oauth_state'] = $state;
    header('Location: ' . google_drive_oauth_url(drive_usuario_redirect_uri(), $state));
    exit;
}

// ---- /gestion-documental/drive/callback ----
if ($uri === '/gestion-documental/drive/callback') {
    if (!empty($_GET['error'])) {
        // Canceló el consentimiento en Google — no es un error real.
        header('Location: ' . BASE_URL . '/gestion-documental');
        exit;
    }

    $stateRecibido = $_GET['state'] ?? '';
    $stateEsperado = $_SESSION['drive_oauth_state'] ?? '';
    unset($_SESSION['drive_oauth_state']);
    if ($stateEsperado === '' || !hash_equals($stateEsperado, $stateRecibido)) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=error');
        exit;
    }

    $code = $_GET['code'] ?? '';
    $tokenData = $code !== '' ? google_drive_oauth_intercambiar($code, drive_usuario_redirect_uri()) : null;
    if (!$tokenData || empty($tokenData['refresh_token'])) {
        // Sin refresh_token no hay forma de mantener la conexión sin pedir
        // consentimiento cada vez — con prompt=consent en la URL de arriba
        // esto no debería pasar, pero se avisa claro en vez de dejarlo a medias.
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=sin_refresh');
        exit;
    }

    // Nunca se guarda el refresh_token tal cual — ver
    // google_drive_refresh_token_cifrar() en GoogleDrive.php (AES-256-GCM,
    // APP_ENCRYPTION_KEY). Sin esa clave configurada no hay forma segura de
    // guardarlo, así que se corta acá en vez de guardarlo en texto plano.
    $tokenCifrado = google_drive_refresh_token_cifrar($tokenData['refresh_token']);
    if ($tokenCifrado === null) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=no_configurado');
        exit;
    }
    $pdo->prepare('UPDATE usuarios SET google_drive_refresh_token = :token, google_drive_conectado_en = NOW() WHERE id = :id')
        ->execute([':token' => $tokenCifrado, ':id' => $_SESSION['usuario_id']]);

    header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=conectado');
    exit;
}

// ---- /gestion-documental/drive/desconectar ----
if ($uri === '/gestion-documental/drive/desconectar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/gestion-documental');
        exit;
    }
    $pdo->prepare('UPDATE usuarios SET google_drive_refresh_token = NULL, google_drive_conectado_en = NULL WHERE id = :id')
        ->execute([':id' => $_SESSION['usuario_id']]);
    header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=desconectado');
    exit;
}

// ---- /gestion-documental/drive/importar ----
// Trae UN archivo puntual del Drive de la persona hacia una carpeta real
// de Gestión Documental — mismas reglas de formato/tamaño que "Traer de
// Drive" (ver CarpetaController.php), pero con el access_token de la
// persona, no el de la cuenta de servicio.
if ($uri === '/gestion-documental/drive/importar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=error');
        exit;
    }

    $stmt = $pdo->prepare('SELECT google_drive_refresh_token FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $refreshToken = google_drive_refresh_token_descifrar($stmt->fetchColumn() ?: null);
    if (!$refreshToken) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=no_conectado');
        exit;
    }
    $accessToken = google_drive_oauth_refrescar($refreshToken);
    if (!$accessToken) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=token_vencido');
        exit;
    }

    $fileId = trim($_POST['file_id'] ?? '');
    $carpetaId = (int) ($_POST['carpeta_id'] ?? 0);
    if ($fileId === '' || !$carpetaId) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=error');
        exit;
    }
    // La carpeta destino tiene que ser una carpeta REAL de direccion_carpetas
    // (Financiera/Talento Humano/etc., ver CarpetaController.php) — no
    // carpetas_documentales (siempre vacía, nunca se llegó a usar). Llevar
    // un archivo ahí es una acción administrativa, igual que subir uno
    // cualquiera a esa misma carpeta desde su propio módulo.
    $stmt = $pdo->prepare('
        SELECT c.id, c.area, c.direccion_id, d.slug
        FROM direccion_carpetas c JOIN direcciones d ON d.id = c.direccion_id
        WHERE c.id = :id
    ');
    $stmt->execute([':id' => $carpetaId]);
    $carpetaDestino = $stmt->fetch();
    if (!$carpetaDestino || !usuario_admin_de((int) $carpetaDestino['direccion_id'])) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    // Esta acción cuelga de /gestion-documental pero ESCRIBE en una carpeta de
    // otro módulo (la de $carpetaDestino, por id): el módulo de esa carpeta no
    // puede estar vetado para este rol — el veto se suma a usuario_admin_de.
    if (!usuario_puede_ver_archivo_de(modulo_de_direccion((int) $carpetaDestino['direccion_id']))) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }
    // A dónde volver tras importar — a la carpeta real donde quedó el
    // archivo (mismo mapa de slug→ruta que ya usa CarpetaController.php),
    // para que la persona vea de una que sí llegó, en vez de quedarse en
    // Gestión Documental sin poder confirmarlo.
    $MAPA_SLUG_RUTA_DRIVE = [
        'financiera' => '/administrativa-financiera', 'talento-humano' => '/talento-humano',
        'institucional' => '/gestion-institucional', 'sgi' => '/sgi',
        'academica' => '/vicerrectoria-academica', 'investigacion' => '/investigacion-innovacion',
    ];
    $rutaModuloDestino = $MAPA_SLUG_RUTA_DRIVE[$carpetaDestino['slug']] ?? '/gestion-documental';
    $paramsVolver = ['carpeta' => $carpetaId];
    if ($carpetaDestino['area'] === 'finanzas') {
        $paramsVolver['area'] = 'finanzas';
    }
    $urlVolverCarpeta = BASE_URL . $rutaModuloDestino . '?' . http_build_query($paramsVolver);

    // Metadatos server-side, nunca lo que mande el formulario — mismo
    // criterio que el resto del portal.
    $meta = google_drive_oauth_metadata($accessToken, $fileId);
    if (!$meta) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=no_encontrado');
        exit;
    }

    // Un Doc/Sheet/Slide NATIVO de Google se exporta a su equivalente de
    // Office al vuelo (mismo mecanismo que CarpetaController.php) — el
    // resto de tipos nativos (Formularios, Dibujos...) no tienen a qué
    // exportarlos, siguen bloqueados.
    $EXPORTAR_GOOGLE_NATIVO = [
        'application/vnd.google-apps.document'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.google-apps.spreadsheet'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.google-apps.presentation' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];
    // Mismos 7 tipos que ya acepta DocumentoController.php para Gestión
    // Documental (sin imágenes — esas no encajan en este repositorio).
    $MIME_A_EXTENSION = [
        'application/pdf'                                                           => 'pdf',
        'application/msword'                                                        => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/vnd.ms-excel'                                                  => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'application/vnd.ms-powerpoint'                                             => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    ];

    $mimeOrigen = $meta['mimeType'] ?? '';
    $esGoogleNativo = str_starts_with($mimeOrigen, 'application/vnd.google-apps.');
    if ($esGoogleNativo) {
        if (!isset($EXPORTAR_GOOGLE_NATIVO[$mimeOrigen])) {
            header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=formato');
            exit;
        }
        $mimeFinal = $EXPORTAR_GOOGLE_NATIVO[$mimeOrigen];
    } else {
        $mimeFinal = $mimeOrigen;
        // El tamaño exportado de un nativo no se sabe de antemano (Drive lo
        // genera al vuelo) — para esos se revisa DESPUÉS, más abajo.
        if ((int) ($meta['size'] ?? 0) > 15 * 1024 * 1024) {
            header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=tamano');
            exit;
        }
    }
    if (!isset($MIME_A_EXTENSION[$mimeFinal])) {
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=formato');
        exit;
    }

    $carpetaArchivos = ROOT_PATH . '/storage/direccion_carpetas';
    if (!is_dir($carpetaArchivos)) {
        mkdir($carpetaArchivos, 0775, true);
    }

    $nombreOriginal = mb_substr($meta['name'] ?? 'Documento de Drive', 0, 150);
    $stmt = $pdo->prepare('INSERT INTO direccion_carpeta_archivos (carpeta_id, nombre, tipo, archivo, peso_bytes) VALUES (:cid, :nombre, :tipo, :archivo, :peso)');
    $stmt->execute([
        ':cid'     => $carpetaId,
        ':nombre'  => $nombreOriginal,
        ':tipo'    => 'Drive personal',
        ':archivo' => '',
        ':peso'    => (int) ($meta['size'] ?? 0),
    ]);
    $archivoId = (int) $pdo->lastInsertId();

    $nombreArchivo = 'archivo_' . $archivoId . '.' . $MIME_A_EXTENSION[$mimeFinal];
    $rutaDestino = $carpetaArchivos . '/' . $nombreArchivo;
    $descargaOk = $esGoogleNativo
        ? google_drive_oauth_exportar($accessToken, $fileId, $mimeFinal, $rutaDestino)
        : google_drive_oauth_descargar($accessToken, $fileId, $rutaDestino);
    if (!$descargaOk) {
        $pdo->prepare('DELETE FROM direccion_carpeta_archivos WHERE id = :id')->execute([':id' => $archivoId]);
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=error');
        exit;
    }
    // El tamaño exportado de un nativo no se sabe hasta que Drive ya lo
    // generó (ver el chequeo "size" de más arriba, que solo aplica al
    // archivo real) — mismo criterio que CarpetaController.php.
    if ($esGoogleNativo && filesize($rutaDestino) > 15 * 1024 * 1024) {
        unlink($rutaDestino);
        $pdo->prepare('DELETE FROM direccion_carpeta_archivos WHERE id = :id')->execute([':id' => $archivoId]);
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=tamano');
        exit;
    }

    $pdo->prepare('UPDATE direccion_carpeta_archivos SET archivo = :archivo, peso_bytes = :peso WHERE id = :id')
        ->execute([':archivo' => $nombreArchivo, ':peso' => filesize($rutaDestino), ':id' => $archivoId]);

    // A la carpeta real donde quedó (no de vuelta a Gestión Documental) —
    // así la persona ve de una que el archivo sí llegó, en la misma vista
    // donde vive el resto de esa carpeta.
    header('Location: ' . $urlVolverCarpeta . '&drive_personal=importado');
    exit;
}
