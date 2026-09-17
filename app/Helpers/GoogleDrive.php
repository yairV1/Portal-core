<?php
// ══════════════════════════════════════════════════════════
//  app/Helpers/GoogleDrive.php
//  Acceso de solo lectura a Google Drive con una cuenta de servicio —
//  sin el SDK oficial de Google (pesado, con muchas dependencias de
//  Composer que este proyecto no usa): se arma y firma el JWT a mano con
//  openssl_sign() y se habla con la API por cURL, mismo estilo que ya usa
//  AuthController.php para el login con Google.
//
//  Requiere GOOGLE_DRIVE_SERVICE_ACCOUNT_FILE (ver .env.example) apuntando
//  al .json que Google Cloud entrega al crear la cuenta de servicio. Sin
//  esa variable, todas las funciones devuelven null/false — quien las usa
//  decide qué mensaje mostrar (ver CarpetaController.php).
// ══════════════════════════════════════════════════════════

function google_drive_configurado(): bool
{
    $archivo = getenv('GOOGLE_DRIVE_SERVICE_ACCOUNT_FILE') ?: '';
    return $archivo !== '' && is_file($archivo);
}

// Codificación base64url que pide JWT (RFC 7519) — distinta del
// base64_encode normal en los caracteres +/ y en no rellenar con "=".
function google_drive_b64url(string $datos): string
{
    return rtrim(strtr(base64_encode($datos), '+/', '-_'), '=');
}

// Token de acceso vía "JWT Bearer flow" para cuentas de servicio (RFC
// 7523) — se cachea en memoria del proceso (dura un solo request de PHP,
// no hay APCu/Redis en este proyecto) para no firmar un JWT nuevo si se
// llama más de una vez en el mismo request.
function google_drive_access_token(): ?string
{
    static $token = null;
    static $expiraEn = 0;

    if ($token !== null && time() < $expiraEn - 30) {
        return $token;
    }

    $archivo = getenv('GOOGLE_DRIVE_SERVICE_ACCOUNT_FILE') ?: '';
    if ($archivo === '' || !is_file($archivo)) {
        return null;
    }
    $credenciales = json_decode((string) file_get_contents($archivo), true);
    if (empty($credenciales['private_key']) || empty($credenciales['client_email'])) {
        return null;
    }

    $ahora = time();
    $header = google_drive_b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claims = google_drive_b64url(json_encode([
        'iss'   => $credenciales['client_email'],
        'scope' => 'https://www.googleapis.com/auth/drive.readonly',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $ahora,
        'exp'   => $ahora + 3600,
    ]));
    $sinFirmar = $header . '.' . $claims;

    $firma = '';
    if (!openssl_sign($sinFirmar, $firma, $credenciales['private_key'], 'SHA256')) {
        return null;
    }
    $jwt = $sinFirmar . '.' . google_drive_b64url($firma);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode((string) $respuesta, true);
    if ($codigo !== 200 || empty($data['access_token'])) {
        return null;
    }

    $token = $data['access_token'];
    $expiraEn = $ahora + (int) ($data['expires_in'] ?? 3600);
    return $token;
}

// Acepta un link completo de Google Drive ("...?id=XXX" o ".../d/XXX/...")
// o el ID pelado — devuelve null si no reconoce nada parecido a un ID de
// Drive (evita mandar basura a la API).
function google_drive_extraer_id(string $entrada): ?string
{
    $entrada = trim($entrada);
    if ($entrada === '') {
        return null;
    }
    if (preg_match('#/d/([a-zA-Z0-9_-]{10,})#', $entrada, $m)) {
        return $m[1];
    }
    if (preg_match('#[?&]id=([a-zA-Z0-9_-]{10,})#', $entrada, $m)) {
        return $m[1];
    }
    if (preg_match('#^[a-zA-Z0-9_-]{10,}$#', $entrada)) {
        return $entrada;
    }
    return null;
}

// Metadatos del archivo (nombre, tipo, tamaño) — se piden ANTES de
// descargar para poder rechazar formato/tamaño sin gastar ancho de banda
// bajando un archivo que de todos modos se va a borrar.
function google_drive_metadata(string $fileId): ?array
{
    $token = google_drive_access_token();
    if (!$token) {
        return null;
    }
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?fields=id,name,mimeType,size&supportsAllDrives=true');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($codigo !== 200) {
        return null;
    }
    return json_decode((string) $respuesta, true);
}

// Descarga el contenido real del archivo (alt=media) directo a $destino.
// Solo sirve para archivos binarios "normales" (PDF, Word, imágenes...);
// los Google Docs/Sheets/Slides nativos no tienen bytes descargables así
// (habría que exportarlos a otro formato) — eso se filtra antes, viendo
// el mimeType en google_drive_metadata().
function google_drive_descargar(string $fileId, string $destino): bool
{
    $token = google_drive_access_token();
    if (!$token) {
        return false;
    }
    $fp = fopen($destino, 'wb');
    if (!$fp) {
        return false;
    }
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?alt=media&supportsAllDrives=true');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_FILE           => $fp,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $ok = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if (!$ok || $codigo !== 200) {
        @unlink($destino);
        return false;
    }
    return true;
}

// Los Google Docs/Sheets/Slides NATIVOS (mimeType "application/vnd.google-
// apps.*") no tienen bytes propios que bajar con google_drive_descargar()
// — hay que pedirle a Drive que los CONVIERTA al vuelo a un formato real
// (Word/Excel/PowerPoint) con este otro endpoint. $mimeDestino es el
// mimeType de Office al que se exporta (ver CarpetaController.php).
function google_drive_exportar(string $fileId, string $mimeDestino, string $destino): bool
{
    $token = google_drive_access_token();
    if (!$token) {
        return false;
    }
    $fp = fopen($destino, 'wb');
    if (!$fp) {
        return false;
    }
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '/export?mimeType=' . rawurlencode($mimeDestino));
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_FILE           => $fp,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $ok = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if (!$ok || $codigo !== 200) {
        @unlink($destino);
        return false;
    }
    return true;
}

// google_drive_refresh_token_cifrar()/_descifrar(): el refresh_token que
// Google entrega (ver más abajo) es un acceso de LARGA duración al Drive
// personal completo de quien lo conectó (scope drive.readonly sobre todo
// su Drive, no solo lo que suba el portal) — guardarlo en texto plano en
// usuarios.google_drive_refresh_token significa que cualquiera con acceso
// de lectura a la BD (un volcado, un backup filtrado, otra vulnerabilidad
// de por medio) se lo lleva tal cual, sin tener que pasar nunca por
// Google. AES-256-GCM con una clave propia (APP_ENCRYPTION_KEY, ver
// .env.example) — no se reutiliza ONLYOFFICE_JWT_SECRET/GOOGLE_CLIENT_SECRET
// para no mezclar secretos de propósitos distintos; si algún día uno de
// los dos se filtra, el otro sigue protegiendo lo suyo.
function google_drive_clave_cifrado(): ?string
{
    $hex = getenv('APP_ENCRYPTION_KEY') ?: '';
    if (strlen($hex) !== 64 || !ctype_xdigit($hex)) {
        return null;
    }
    return hex2bin($hex);
}

function google_drive_refresh_token_cifrar(string $token): ?string
{
    $clave = google_drive_clave_cifrado();
    if ($clave === null) {
        return null;
    }
    $iv = random_bytes(12);
    $tag = '';
    $cifrado = openssl_encrypt($token, 'aes-256-gcm', $clave, OPENSSL_RAW_DATA, $iv, $tag);
    return $cifrado === false ? null : base64_encode($iv . $tag . $cifrado);
}

// null tanto si no hay clave configurada como si el valor guardado no se
// puede descifrar (clave rotada, dato corrupto) — quien la usa ya trata
// "sin refresh_token utilizable" como "Drive personal no conectado", el
// mismo estado que si nunca se hubiera conectado.
function google_drive_refresh_token_descifrar(?string $valorGuardado): ?string
{
    if (!$valorGuardado) {
        return null;
    }
    $clave = google_drive_clave_cifrado();
    if ($clave === null) {
        return null;
    }
    $crudo = base64_decode($valorGuardado, true);
    if ($crudo === false || strlen($crudo) < 29) {
        return null;
    }
    $iv     = substr($crudo, 0, 12);
    $tag    = substr($crudo, 12, 16);
    $cifrado = substr($crudo, 28);
    $token = openssl_decrypt($cifrado, 'aes-256-gcm', $clave, OPENSSL_RAW_DATA, $iv, $tag);
    return $token === false ? null : $token;
}

// ══════════════════════════════════════════════════════════
//  Drive PERSONAL de cada usuario (OAuth) — distinto de todo lo de arriba
//  (que habla con Drive como la cuenta de servicio, para "Traer de Drive"
//  por link). Acá cada usuario conecta SU PROPIA cuenta de Google (mismo
//  Client ID/Secret que ya usa el login, ver AuthController.php, pero con
//  el scope de Drive) y ve su propia lista de archivos — ver
//  DriveUsuarioController.php. Reusa GOOGLE_CLIENT_ID/GOOGLE_CLIENT_SECRET,
//  así que solo aplica si el login con Google ya está configurado.
// ══════════════════════════════════════════════════════════

function google_drive_oauth_configurado(): bool
{
    return (getenv('GOOGLE_CLIENT_ID') ?: '') !== '' && (getenv('GOOGLE_CLIENT_SECRET') ?: '') !== '';
}

// URL de consentimiento — access_type=offline + prompt=consent son los que
// hacen que Google entregue un refresh_token (sin prompt=consent, si la
// persona ya autorizó antes, Google no lo vuelve a mandar).
function google_drive_oauth_url(string $redirectUri, string $state): string
{
    $parametros = http_build_query([
        'client_id'              => getenv('GOOGLE_CLIENT_ID') ?: '',
        'redirect_uri'           => $redirectUri,
        'response_type'          => 'code',
        // .readonly: para listar/traer archivos que YA tenías en tu Drive
        // (ver google_drive_oauth_listar()). .file: para que el portal
        // pueda crear/actualizar SUS PROPIOS archivos dentro de tu Drive
        // (ver google_drive_oauth_sincronizar_archivo() más abajo) — no le
        // da acceso de escritura a nada que no haya creado él mismo.
        'scope'                  => 'https://www.googleapis.com/auth/drive.readonly https://www.googleapis.com/auth/drive.file',
        'access_type'            => 'offline',
        'prompt'                 => 'consent',
        'include_granted_scopes' => 'true',
        'state'                  => $state,
    ]);
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . $parametros;
}

// Cambia el "code" que Google mandó de vuelta por un access_token +
// refresh_token — este último es el que se guarda (ver usuarios.
// google_drive_refresh_token, migración 041) para no pedir consentimiento
// cada vez.
function google_drive_oauth_intercambiar(string $code, string $redirectUri): ?array
{
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'code'          => $code,
            'client_id'     => getenv('GOOGLE_CLIENT_ID') ?: '',
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode((string) $respuesta, true);
    return ($codigo === 200 && !empty($data['access_token'])) ? $data : null;
}

// Un access_token de OAuth "normal" dura ~1 hora — con el refresh_token
// guardado se pide uno nuevo sin que la persona tenga que volver a
// autorizar nada (a diferencia del refresh_token, este NO se guarda en
// BD, se pide de nuevo en cada request que lo necesite).
function google_drive_oauth_refrescar(string $refreshToken): ?string
{
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'refresh_token' => $refreshToken,
            'client_id'     => getenv('GOOGLE_CLIENT_ID') ?: '',
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
            'grant_type'    => 'refresh_token',
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode((string) $respuesta, true);
    return ($codigo === 200 && !empty($data['access_token'])) ? $data['access_token'] : null;
}

// Lista de archivos del Drive de la persona (paginada) — "trashed=false"
// para no mostrar lo que ya borró en su propio Drive.
function google_drive_oauth_listar(string $accessToken, ?string $pageToken = null): ?array
{
    $parametros = [
        'q'         => 'trashed = false',
        'fields'    => 'nextPageToken,files(id,name,mimeType,size,modifiedTime,webViewLink)',
        'pageSize'  => 30,
        'orderBy'   => 'modifiedTime desc',
        'spaces'    => 'drive',
    ];
    if ($pageToken) {
        $parametros['pageToken'] = $pageToken;
    }
    $ch = curl_init('https://www.googleapis.com/drive/v3/files?' . http_build_query($parametros));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $codigo === 200 ? json_decode((string) $respuesta, true) : null;
}

// Metadatos de UN archivo puntual — se vuelve a pedir server-side antes de
// importar (nunca se confía en el nombre/mimeType/tamaño que mande el
// formulario, mismo criterio que el resto del portal).
function google_drive_oauth_metadata(string $accessToken, string $fileId): ?array
{
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?fields=id,name,mimeType,size');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $codigo === 200 ? json_decode((string) $respuesta, true) : null;
}

// Mismas dos operaciones que google_drive_descargar()/google_drive_exportar()
// de más arriba, pero con el access_token de la PERSONA en vez del de la
// cuenta de servicio — se separan para no arriesgar el flujo ya probado de
// "Traer de Drive" con un cambio de firma.
function google_drive_oauth_descargar(string $accessToken, string $fileId, string $destino): bool
{
    $fp = fopen($destino, 'wb');
    if (!$fp) {
        return false;
    }
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?alt=media');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_FILE           => $fp,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $ok = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if (!$ok || $codigo !== 200) {
        @unlink($destino);
        return false;
    }
    return true;
}

function google_drive_oauth_exportar(string $accessToken, string $fileId, string $mimeDestino, string $destino): bool
{
    $fp = fopen($destino, 'wb');
    if (!$fp) {
        return false;
    }
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '/export?mimeType=' . rawurlencode($mimeDestino));
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_FILE           => $fp,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $ok = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if (!$ok || $codigo !== 200) {
        @unlink($destino);
        return false;
    }
    return true;
}

// ══════════════════════════════════════════════════════════
//  Sincronización automática HACIA el Drive de la persona — decisión
//  explícita del cliente: TODO lo que se suba/cree/edite en el portal
//  (ver CarpetaController.php/DocumentoController.php/EditorController.php)
//  sube una copia al Drive de quien lo tocó, sin excepción — incluye
//  documentos con datos de otras personas (Talento Humano, etc.). Necesita
//  el scope "drive.file" (ver google_drive_oauth_url() arriba) — con
//  "drive.readonly" nada más, estas escrituras fallan con 403.
// ══════════════════════════════════════════════════════════

// Encuentra (o crea, la primera vez) la carpeta "Portal CORE" dentro del
// Drive de la persona — todo lo que sincroniza este portal cae ahí, para
// no desordenar el resto de su Drive personal. Con el scope "drive.file"
// el buscador solo ve carpetas que el propio portal creó antes, así que
// esto es autocontenido: nunca encuentra (ni toca) nada ajeno.
function google_drive_oauth_carpeta_portal(string $accessToken): ?string
{
    $q = "mimeType = 'application/vnd.google-apps.folder' and name = 'Portal CORE' and trashed = false";
    $ch = curl_init('https://www.googleapis.com/drive/v3/files?' . http_build_query(['q' => $q, 'fields' => 'files(id)', 'pageSize' => 1]));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = $codigo === 200 ? json_decode((string) $respuesta, true) : null;
    if (!empty($data['files'][0]['id'])) {
        return $data['files'][0]['id'];
    }

    $ch = curl_init('https://www.googleapis.com/drive/v3/files?fields=id');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode(['name' => 'Portal CORE', 'mimeType' => 'application/vnd.google-apps.folder']),
        CURLOPT_TIMEOUT        => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = $codigo === 200 ? json_decode((string) $respuesta, true) : null;
    return $data['id'] ?? null;
}

// Crea un archivo NUEVO dentro de esa carpeta — multipart/related armado a
// mano (dos partes: metadata JSON + bytes del archivo) porque la API de
// subida de Drive no acepta el multipart/form-data de CURLFile normal.
function google_drive_oauth_crear(string $accessToken, string $carpetaDriveId, string $rutaLocal, string $nombreArchivo, string $mimeType): ?string
{
    $contenido = @file_get_contents($rutaLocal);
    if ($contenido === false) {
        return null;
    }
    $boundary = 'portalcore' . bin2hex(random_bytes(8));
    $metadata = json_encode(['name' => $nombreArchivo, 'parents' => [$carpetaDriveId]]);
    $cuerpo = "--{$boundary}\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n{$metadata}\r\n"
        . "--{$boundary}\r\nContent-Type: {$mimeType}\r\n\r\n{$contenido}\r\n--{$boundary}--";

    $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken, "Content-Type: multipart/related; boundary={$boundary}"],
        CURLOPT_POSTFIELDS     => $cuerpo,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = $codigo === 200 ? json_decode((string) $respuesta, true) : null;
    return $data['id'] ?? null;
}

// Reemplaza el CONTENIDO de un archivo que ya existe en Drive (mismo id de
// una sincronización anterior) — así una segunda edición actualiza el
// mismo archivo en vez de ir creando copias nuevas cada vez.
function google_drive_oauth_actualizar_contenido(string $accessToken, string $fileId, string $rutaLocal, string $mimeType): bool
{
    $contenido = @file_get_contents($rutaLocal);
    if ($contenido === false) {
        return false;
    }
    $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files/' . rawurlencode($fileId) . '?uploadType=media');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken, 'Content-Type: ' . $mimeType],
        CURLOPT_POSTFIELDS     => $contenido,
        CURLOPT_TIMEOUT        => 60,
    ]);
    curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $codigo === 200;
}

// La función que de verdad llaman los controladores tras guardar un
// archivo — todo lo demás de esta sección es detalle interno. Si la
// persona no tiene su Drive conectado, o el token venció, no hace nada
// (silencioso: la acción principal —subir/crear/editar— ya se completó
// bien, esto es solo el extra de sincronizarlo, nunca debe tumbar la
// acción principal si falla).
//
// $tabla/$columnaId identifican dónde vive el "google_drive_file_id" de
// ESTE archivo puntual (ver migración 042) — server-side siempre desde una
// lista fija adentro de esta misma función, nunca desde afuera, para no
// abrir la puerta a un nombre de tabla arbitrario.
function google_drive_oauth_sincronizar_archivo(PDO $pdo, int $usuarioId, string $tabla, int $filaId, string $rutaLocal, string $nombreArchivo, string $mimeType): void
{
    $TABLAS_VALIDAS = ['direccion_carpeta_archivos', 'direccion_documentos', 'archivos_documentales'];
    if (!in_array($tabla, $TABLAS_VALIDAS, true)) {
        return;
    }

    $stmt = $pdo->prepare('SELECT google_drive_refresh_token FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $usuarioId]);
    $refreshToken = google_drive_refresh_token_descifrar($stmt->fetchColumn() ?: null);
    if (!$refreshToken) {
        return;
    }
    $accessToken = google_drive_oauth_refrescar($refreshToken);
    if (!$accessToken) {
        return;
    }

    $stmt = $pdo->prepare("SELECT google_drive_file_id FROM {$tabla} WHERE id = :id");
    $stmt->execute([':id' => $filaId]);
    $driveFileId = $stmt->fetchColumn();

    if ($driveFileId) {
        if (google_drive_oauth_actualizar_contenido($accessToken, $driveFileId, $rutaLocal, $mimeType)) {
            return;
        }
        // El archivo pudo haberse borrado del lado de Drive sin que el
        // portal se enterara — si actualizar falla, se intenta crear uno
        // nuevo en vez de dejar la sincronización rota para siempre.
        $driveFileId = null;
    }

    $carpetaDriveId = google_drive_oauth_carpeta_portal($accessToken);
    if (!$carpetaDriveId) {
        return;
    }
    $nuevoId = google_drive_oauth_crear($accessToken, $carpetaDriveId, $rutaLocal, $nombreArchivo, $mimeType);
    if ($nuevoId) {
        $pdo->prepare("UPDATE {$tabla} SET google_drive_file_id = :fid WHERE id = :id")
            ->execute([':fid' => $nuevoId, ':id' => $filaId]);
    }
}
