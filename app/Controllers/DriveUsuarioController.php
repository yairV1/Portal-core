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
    // state anti-CSRF: se genera acá y se guarda en sesión para compararlo
    // en el callback (mismo criterio que cualquier flujo OAuth del portal).
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
    // exportarlos, siguen bloqueados. Ver google_drive_oauth_resolver_descarga()
    // en GoogleDrive.php — misma función que usa el import masivo, para no
    // mantener la tabla de mimes en dos lugares.
    $resuelto = google_drive_oauth_resolver_descarga($meta);
    if (!$resuelto) {
        $motivo = ((int) ($meta['size'] ?? 0) > 15 * 1024 * 1024) ? 'tamano' : 'formato';
        header('Location: ' . BASE_URL . '/gestion-documental?drive_personal=' . $motivo);
        exit;
    }
    $mimeFinal = $resuelto['mime_final'];
    $esGoogleNativo = $resuelto['es_google_nativo'];

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

    $nombreArchivo = 'archivo_' . $archivoId . '.' . $resuelto['extension'];
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

// Cuántos items pedir a Drive por lote en la importación masiva — chico a
// propósito (ver migración 047_drive_personal_import_masivo.sql): la idea
// es traer TODO el Drive de la persona, pero de a poco, sin una petición
// larga que bloquee nada; el ritmo real lo pone el JS de Documental.php
// llamando a este endpoint cada rato mientras la pantalla está abierta.
const DRIVE_IMPORT_MASIVO_QUERY_CARPETAS = "mimeType='application/vnd.google-apps.folder' and trashed=false";
const DRIVE_IMPORT_MASIVO_QUERY_ARCHIVOS = "mimeType!='application/vnd.google-apps.folder' and trashed=false";

// ---- /gestion-documental/drive/importar-todo/avanzar ----
// Un lote de la importación completa del Drive personal — se llama
// repetidas veces (ver <script> en Documental.php) hasta que la respuesta
// diga "terminado". No exige ningún rol especial: es una acción sobre el
// propio Drive de quien la pide, igual que conectar/desconectar arriba.
if ($uri === '/gestion-documental/drive/importar-todo/avanzar') {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        echo json_encode(['error' => 'csrf']);
        exit;
    }

    $usuarioId = (int) $_SESSION['usuario_id'];
    $stmt = $pdo->prepare('SELECT google_drive_refresh_token, google_drive_import_estado, google_drive_import_fase, google_drive_import_page_token, google_drive_import_traidos FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $usuarioId]);
    $fila = $stmt->fetch();
    $refreshToken = google_drive_refresh_token_descifrar($fila['google_drive_refresh_token'] ?? null);
    if (!$refreshToken) {
        http_response_code(400);
        echo json_encode(['error' => 'no_conectado']);
        exit;
    }
    $accessToken = google_drive_oauth_refrescar($refreshToken);
    if (!$accessToken) {
        http_response_code(400);
        echo json_encode(['error' => 'token_vencido']);
        exit;
    }

    if ($fila['google_drive_import_estado'] === 'completo') {
        echo json_encode(['estado' => 'completo', 'fase' => null, 'traidos' => (int) $fila['google_drive_import_traidos'], 'terminado' => true]);
        exit;
    }

    // Primer lote de todos: arranca la máquina de estados.
    $fase = $fila['google_drive_import_fase'] ?? 'carpetas';
    $pageToken = $fila['google_drive_import_page_token'];
    if ($fila['google_drive_import_estado'] === 'no_iniciado') {
        $fase = 'carpetas';
        $pageToken = null;
        $pdo->prepare("UPDATE usuarios SET google_drive_import_estado = 'en_progreso', google_drive_import_fase = 'carpetas', google_drive_import_page_token = NULL WHERE id = :id")
            ->execute([':id' => $usuarioId]);
    }

    if ($fase === 'carpetas') {
        $resultado = google_drive_oauth_listar_todo($accessToken, DRIVE_IMPORT_MASIVO_QUERY_CARPETAS, $pageToken);
        if ($resultado === null) {
            http_response_code(502);
            echo json_encode(['error' => 'drive']);
            exit;
        }
        $stmtUpsert = $pdo->prepare('
            INSERT INTO drive_personal_carpetas (usuario_id, drive_folder_id, drive_parent_folder_id, nombre)
            VALUES (:uid, :fid, :pid, :nombre)
            ON DUPLICATE KEY UPDATE drive_parent_folder_id = VALUES(drive_parent_folder_id), nombre = VALUES(nombre)
        ');
        foreach ($resultado['files'] ?? [] as $carpeta) {
            $stmtUpsert->execute([
                ':uid'    => $usuarioId,
                ':fid'    => $carpeta['id'],
                ':pid'    => $carpeta['parents'][0] ?? null,
                ':nombre' => mb_substr($carpeta['name'] ?? 'Sin nombre', 0, 255),
            ]);
        }
        $siguientePagina = $resultado['nextPageToken'] ?? null;
        if ($siguientePagina) {
            $pdo->prepare('UPDATE usuarios SET google_drive_import_page_token = :token WHERE id = :id')
                ->execute([':token' => $siguientePagina, ':id' => $usuarioId]);
        } else {
            // Ya se trajeron todas las carpetas — ahora sí se puede resolver
            // parent_id de verdad (antes no se podía confiar en el orden:
            // Drive no garantiza que una carpeta llegue después de su padre).
            $pdo->prepare('
                UPDATE drive_personal_carpetas hijo
                JOIN drive_personal_carpetas padre
                  ON padre.usuario_id = hijo.usuario_id AND padre.drive_folder_id = hijo.drive_parent_folder_id
                SET hijo.parent_id = padre.id
                WHERE hijo.usuario_id = :uid
            ')->execute([':uid' => $usuarioId]);
            $pdo->prepare("UPDATE usuarios SET google_drive_import_fase = 'archivos', google_drive_import_page_token = NULL WHERE id = :id")
                ->execute([':id' => $usuarioId]);
            $fase = 'archivos';
        }

        $traidos = (int) $pdo->query('SELECT google_drive_import_traidos FROM usuarios WHERE id = ' . $usuarioId)->fetchColumn();
        echo json_encode(['estado' => 'en_progreso', 'fase' => $fase, 'traidos' => $traidos, 'terminado' => false]);
        exit;
    }

    // $fase === 'archivos'
    $resultado = google_drive_oauth_listar_todo($accessToken, DRIVE_IMPORT_MASIVO_QUERY_ARCHIVOS, $pageToken);
    if ($resultado === null) {
        http_response_code(502);
        echo json_encode(['error' => 'drive']);
        exit;
    }

    $carpetaStorage = ROOT_PATH . '/storage/drive_personal/' . $usuarioId;
    if (!is_dir($carpetaStorage)) {
        mkdir($carpetaStorage, 0775, true);
    }
    $stmtCarpetaLocal = $pdo->prepare('SELECT id FROM drive_personal_carpetas WHERE usuario_id = :uid AND drive_folder_id = :fid');
    $stmtInsertar = $pdo->prepare('INSERT IGNORE INTO drive_personal_archivos (usuario_id, carpeta_id, drive_file_id, nombre, tipo, archivo, peso_bytes) VALUES (:uid, :cid, :fid, :nombre, :tipo, :archivo, :peso)');
    $traidosEnEsteLote = 0;
    foreach ($resultado['files'] ?? [] as $archivoDrive) {
        // Formato no soportado o > 15MB: se omite y sigue con el siguiente,
        // no se aborta el lote completo por un solo archivo.
        $resuelto = google_drive_oauth_resolver_descarga($archivoDrive);
        if (!$resuelto) {
            continue;
        }
        $parentDriveId = $archivoDrive['parents'][0] ?? null;
        $carpetaLocalId = null;
        if ($parentDriveId) {
            $stmtCarpetaLocal->execute([':uid' => $usuarioId, ':fid' => $parentDriveId]);
            $carpetaLocalId = $stmtCarpetaLocal->fetchColumn() ?: null;
        }

        // Nombre de archivo temporal único por drive_file_id — se sabe el
        // id real recién tras el INSERT, pero necesitamos escribir a disco
        // antes para poder confirmar el tamaño real de los nativos
        // exportados (mismo orden que /gestion-documental/drive/importar).
        $nombreArchivoTmp = 'tmp_' . bin2hex(random_bytes(8)) . '.' . $resuelto['extension'];
        $rutaTmp = $carpetaStorage . '/' . $nombreArchivoTmp;
        $descargaOk = $resuelto['es_google_nativo']
            ? google_drive_oauth_exportar($accessToken, $archivoDrive['id'], $resuelto['mime_final'], $rutaTmp)
            : google_drive_oauth_descargar($accessToken, $archivoDrive['id'], $rutaTmp);
        if (!$descargaOk) {
            continue;
        }
        if (filesize($rutaTmp) > 15 * 1024 * 1024) {
            unlink($rutaTmp);
            continue;
        }

        $stmtInsertar->execute([
            ':uid'     => $usuarioId,
            ':cid'     => $carpetaLocalId,
            ':fid'     => $archivoDrive['id'],
            ':nombre'  => mb_substr($archivoDrive['name'] ?? 'Sin nombre', 0, 255),
            ':tipo'    => $resuelto['extension'],
            ':archivo' => $nombreArchivoTmp,
            ':peso'    => filesize($rutaTmp),
        ]);
        // rowCount(), no lastInsertId(): con INSERT IGNORE, lastInsertId()
        // NO vuelve a 0 cuando la fila se ignora por duplicado — conserva
        // el id del último insert que sí tuvo éxito en esta conexión, así
        // que con el mismo $stmtInsertar reutilizado en todo el foreach,
        // un duplicado que venga después de un insert real leería un id
        // "viejo" como si fuera propio. rowCount() sí es 0 cuando el
        // IGNORE descartó la fila.
        if ($stmtInsertar->rowCount() > 0) {
            $traidosEnEsteLote++;
        } else {
            // Ya se había importado antes (UNIQUE usuario_id+drive_file_id)
            // — no queremos un archivo huérfano duplicado en disco.
            unlink($rutaTmp);
        }
    }

    $siguientePagina = $resultado['nextPageToken'] ?? null;
    $terminado = !$siguientePagina;
    $pdo->prepare('UPDATE usuarios SET google_drive_import_page_token = :token, google_drive_import_traidos = google_drive_import_traidos + :n' . ($terminado ? ", google_drive_import_estado = 'completo', google_drive_import_fase = NULL" : '') . ' WHERE id = :id')
        ->execute([':token' => $siguientePagina, ':n' => $traidosEnEsteLote, ':id' => $usuarioId]);

    $traidos = (int) $pdo->query('SELECT google_drive_import_traidos FROM usuarios WHERE id = ' . $usuarioId)->fetchColumn();
    echo json_encode(['estado' => $terminado ? 'completo' : 'en_progreso', 'fase' => 'archivos', 'traidos' => $traidos, 'terminado' => $terminado]);
    exit;
}

// ---- /gestion-documental/drive/mi-drive/descargar ----
// Descarga un archivo ya importado del espejo personal — siempre filtrado
// por usuario_id de la sesión, nadie más puede ver el Drive personal de
// otra persona (no hay dirección/área de por medio acá, es 100% privado).
if ($uri === '/gestion-documental/drive/mi-drive/descargar') {
    $archivoId = (int) ($_GET['archivo_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT nombre, archivo FROM drive_personal_archivos WHERE id = :id AND usuario_id = :uid');
    $stmt->execute([':id' => $archivoId, ':uid' => $_SESSION['usuario_id']]);
    $fila = $stmt->fetch();
    if (!$fila) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }
    $ruta = ROOT_PATH . '/storage/drive_personal/' . (int) $_SESSION['usuario_id'] . '/' . $fila['archivo'];
    if (!is_file($ruta)) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . rawurlencode($fila['nombre']) . '.' . pathinfo($fila['archivo'], PATHINFO_EXTENSION) . '"');
    header('Content-Length: ' . filesize($ruta));
    readfile($ruta);
    exit;
}
