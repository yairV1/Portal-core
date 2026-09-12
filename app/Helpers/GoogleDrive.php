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
