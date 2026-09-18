<?php
// ══════════════════════════════════════════════════════════
//  app/Helpers/OnlyOffice.php
//  Editor de Word/Excel/PowerPoint DENTRO del portal — ver
//  EditorController.php y el servicio "onlyoffice" en docker-compose.yml
//  (OnlyOffice Docs autoalojado, nada sale del servidor).
//
//  JWT propio (HS256), sin librería externa — mismo criterio que
//  GoogleDrive.php (RS256 con openssl_sign en vez de firebase/php-jwt).
//  Sirve para DOS cosas distintas con la misma firma:
//   1. Firmar el "config" que el navegador le manda al editor (para que
//      OnlyOffice confíe en que no se alteró en el camino).
//   2. Firmar los "clave=" de las URLs internas (/editor/archivo,
//      /editor/callback) que llama el propio contenedor OnlyOffice, que no
//      tiene cookie de sesión — la firma es lo único que las protege.
// ══════════════════════════════════════════════════════════

function onlyoffice_configurado(): bool
{
    return !empty(getenv('ONLYOFFICE_JWT_SECRET'));
}

// ¿Este archivo se puede abrir con el editor de Office (ver
// EditorController.php)? Mismas 6 extensiones que ahí — un solo lugar para
// no repetir la lista en cada vista que muestra un botón "Editar"/"Abrir".
function onlyoffice_editable(string $nombreArchivo): bool
{
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    return in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'], true);
}

function onlyoffice_base64url_codificar(string $datos): string
{
    return rtrim(strtr(base64_encode($datos), '+/', '-_'), '=');
}

function onlyoffice_base64url_decodificar(string $datos): string
{
    return base64_decode(strtr($datos, '-_', '+/'));
}

function onlyoffice_jwt_firmar(array $payload): string
{
    $secreto = getenv('ONLYOFFICE_JWT_SECRET') ?: '';
    $header = onlyoffice_base64url_codificar(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $cuerpo = onlyoffice_base64url_codificar(json_encode($payload));
    $firma = onlyoffice_base64url_codificar(hash_hmac('sha256', "{$header}.{$cuerpo}", $secreto, true));
    return "{$header}.{$cuerpo}.{$firma}";
}

// Devuelve el payload si la firma cuadra con nuestro secreto, null si no
// (JWT ajeno, alterado, o el secreto no coincide) — hash_equals evita que
// una comparación normal filtre por timing cuánto de la firma acertó.
function onlyoffice_jwt_verificar(string $jwt): ?array
{
    $secreto = getenv('ONLYOFFICE_JWT_SECRET') ?: '';
    $partes = explode('.', $jwt);
    if (count($partes) !== 3) {
        return null;
    }
    [$header, $cuerpo, $firma] = $partes;
    $firmaEsperada = onlyoffice_base64url_codificar(hash_hmac('sha256', "{$header}.{$cuerpo}", $secreto, true));
    if (!hash_equals($firmaEsperada, $firma)) {
        return null;
    }
    $payload = json_decode(onlyoffice_base64url_decodificar($cuerpo), true);
    return is_array($payload) ? $payload : null;
}

// Un token interno nuestro (tipo+id+acción+vencimiento) — no es el JWT que
// manda OnlyOffice, es el "clave=" que nosotros mismos firmamos para las
// URLs que llama el contenedor sin sesión (ver arriba). $vigenciaSeg por
// defecto 6 horas: de sobra para una sesión de edición normal, sin dejar
// un enlace de descarga directa vigente para siempre.
//
// $editable viaja SOLO en el token de acción 'callback' (ver
// EditorController.php: se firma con el mismo $puedeEditar que decide si
// esa persona abrió el documento en modo edición o solo lectura) — así el
// propio callback puede negarse a escribir sin depender de una sesión que
// no existe en esa llamada (la hace el contenedor OnlyOffice, no un
// navegador). Sin esto, cualquiera con acceso de solo lectura a un
// documento podía usar la "clave" legítima que le tocó al abrirlo (válida
// por su propio diseño, solo para ESE documento) para forjar un callback y
// sobrescribirlo igual, porque nada distinguía "esta clave se emitió para
// alguien que solo podía ver" de "para alguien que podía editar".
function onlyoffice_token_interno(string $tipo, int $id, string $accion, int $vigenciaSeg = 6 * 3600, ?bool $editable = null): string
{
    $payload = ['tipo' => $tipo, 'id' => $id, 'accion' => $accion, 'exp' => time() + $vigenciaSeg];
    if ($editable !== null) {
        $payload['editable'] = $editable;
    }
    return onlyoffice_jwt_firmar($payload);
}

function onlyoffice_token_interno_valido(string $clave, string $tipo, int $id, string $accion): bool
{
    $payload = onlyoffice_jwt_verificar($clave);
    return $payload
        && ($payload['tipo'] ?? null) === $tipo
        && (int) ($payload['id'] ?? 0) === $id
        && ($payload['accion'] ?? null) === $accion
        && (int) ($payload['exp'] ?? 0) > time();
}

// Verdadero solo si la "clave" es válida para ESTE documento/acción Y
// además se emitió para alguien con permiso de edición — usado por
// POST /editor/callback antes de escribir nada en disco (ver
// EditorController.php). No confundir con onlyoffice_token_interno_valido():
// esa solo confirma "esta clave es de este documento", no "quien la tiene
// podía modificarlo".
function onlyoffice_token_interno_editable(string $clave, string $tipo, int $id, string $accion): bool
{
    $payload = onlyoffice_jwt_verificar($clave);
    return $payload
        && ($payload['tipo'] ?? null) === $tipo
        && (int) ($payload['id'] ?? 0) === $id
        && ($payload['accion'] ?? null) === $accion
        && (int) ($payload['exp'] ?? 0) > time()
        && ($payload['editable'] ?? false) === true;
}

// El JWT que OnlyOffice manda de vuelta (header Authorization: Bearer, o a
// veces un campo "token" en el body — varía por versión) siempre firma
// {"payload": <el body real>} cuando viene de verdad del callback, así que
// se desenvuelve acá en vez de repetir esta lógica en el controlador.
//
// A propósito NO hay fallback a "devolver el JWT completo si no tiene
// 'payload'": nuestro propio token de configuración del editor
// (onlyoffice_jwt_firmar($configEditor), ver EditorController.php) está
// firmado con el MISMO secreto y viaja embebido en el HTML de cualquiera
// que abra /editor — incluso en modo solo lectura. Sin este chequeo
// estricto, ese token (visible con "ver código fuente") pasaba la
// verificación de firma igual que un callback real de OnlyOffice, porque
// ambos usan el mismo HMAC — la única diferencia real entre ambos es la
// forma del payload, así que es lo único que puede distinguirlos acá.
function onlyoffice_jwt_extraer_payload(string $jwt): ?array
{
    $decodificado = onlyoffice_jwt_verificar($jwt);
    if ($decodificado === null || !isset($decodificado['payload']) || !is_array($decodificado['payload'])) {
        return null;
    }
    return $decodificado['payload'];
}
