<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/EditorController.php
//  Abre/edita Word, Excel y PowerPoint DENTRO del portal — sin descargar
//  el archivo ni salir a Google/Microsoft — vía OnlyOffice Docs
//  autoalojado (ver docker-compose.yml, servicio "onlyoffice", y
//  .env.example). Reusa el mismo "tipo" (carpeta|direccion|documental) que
//  ya distinguen CarpetaController.php/DocumentoController.php para las 3
//  tablas que guardan un archivo — este archivo no inventa una cuarta.
//
//  Tres rutas en un solo controlador (mismo patrón que CarpetaController.php):
//   GET  /editor           — la página con el editor embebido (con sesión)
//   GET  /editor/archivo   — el archivo crudo, para que OnlyOffice lo lea
//                             (sin sesión: lo pide el contenedor, no el
//                             navegador — se autoriza con "clave", ver
//                             OnlyOffice.php)
//   POST /editor/callback  — OnlyOffice avisa acá cuando alguien guarda
//                             (sin sesión, mismo criterio; además verifica
//                             el JWT propio de OnlyOffice, ver abajo)
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

require_once ROOT_PATH . '/app/Helpers/OnlyOffice.php';
require_once ROOT_PATH . '/app/Helpers/GoogleDrive.php';

// mimeType real por extensión — para google_drive_oauth_sincronizar_archivo()
// tras un guardado (ver el callback más abajo).
$EXTENSION_A_MIME = [
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
];

// Las 3 tablas que ya guardan un archivo real — cada una con su propia
// carpeta en storage/ y su propia forma de saber a qué dirección
// pertenece (o ninguna, ver 'documental'), para el bloqueo por área (ver
// usuario_area_asignada() en public/index.php).
$EDITOR_FUENTES = [
    'carpeta' => [
        'tabla' => 'direccion_carpeta_archivos',
        'storage' => 'direccion_carpetas',
        'join' => 'JOIN direccion_carpetas c ON c.id = t.carpeta_id',
        'campo_direccion' => 'c.direccion_id',
    ],
    'direccion' => [
        'tabla' => 'direccion_documentos',
        'storage' => 'documentos',
        'join' => '',
        'campo_direccion' => 't.direccion_id',
    ],
    'documental' => [
        'tabla' => 'archivos_documentales',
        'storage' => 'documentos',
        'join' => '',
        'campo_direccion' => 'NULL',
    ],
];

// fileType de OnlyOffice por extensión — solo Office; PDF/imágenes siguen
// con el "Ver" de siempre (visor nativo del navegador, ya andaba bien).
$EXTENSION_A_TIPO_DOC = [
    'doc' => 'word', 'docx' => 'word',
    'xls' => 'cell', 'xlsx' => 'cell',
    'ppt' => 'slide', 'pptx' => 'slide',
];

function editor_fila(PDO $pdo, array $fuente, int $id): ?array
{
    $sql = "SELECT t.id, t.nombre, t.archivo, {$fuente['campo_direccion']} AS direccion_id
            FROM {$fuente['tabla']} t {$fuente['join']} WHERE t.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

// null en direccion_id = documento sin dirección propia (Gestión
// Documental) — sigue abierto para todos, igual que esa página misma.
function editor_area_permitida(?int $direccionId): bool
{
    if ($direccionId === null) {
        return true;
    }
    $areaAsignada = usuario_area_asignada();
    return $areaAsignada === null || $areaAsignada === $direccionId;
}

$tipo = $_GET['tipo'] ?? '';
$fuente = $EDITOR_FUENTES[$tipo] ?? null;
$carpetaFisica = $fuente ? ROOT_PATH . '/storage/' . $fuente['storage'] : null;

// ---- GET /editor/archivo — lo pide el contenedor OnlyOffice, sin sesión ----
if ($uri === '/editor/archivo') {
    // Sin secreto configurado no hay forma de validar la "clave" — se corta
    // antes de leer nada, en vez de dejar que una firma trivial la acepte.
    if (!onlyoffice_configurado()) {
        http_response_code(503);
        exit;
    }
    $id = (int) ($_GET['id'] ?? 0);
    $clave = $_GET['clave'] ?? '';
    if (!$fuente || !onlyoffice_token_interno_valido($clave, $tipo, $id, 'archivo')) {
        http_response_code(403);
        exit;
    }
    $fila = editor_fila($pdo, $fuente, $id);
    if (!$fila || !$fila['archivo']) {
        http_response_code(404);
        exit;
    }
    $ruta = $carpetaFisica . '/' . $fila['archivo'];
    if (!is_file($ruta)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($ruta));
    readfile($ruta);
    exit;
}

// ---- POST /editor/callback — OnlyOffice avisa que alguien guardó ----
// Ver https://api.onlyoffice.com/editors/callback — status 2 (listo para
// guardar) y 6 (forcesave, autoguardado periódico) son los únicos que
// traen "url" con el archivo ya convertido a bytes reales; el resto (1
// edición en curso, 4 cerrado sin cambios...) solo se contesta error:0
// para que OnlyOffice no reintente, sin tocar nada en disco.
if ($uri === '/editor/callback') {
    header('Content-Type: application/json');
    if (!onlyoffice_configurado()) {
        http_response_code(503);
        echo json_encode(['error' => 1]);
        exit;
    }
    $id = (int) ($_GET['id'] ?? 0);
    $clave = $_GET['clave'] ?? '';
    // onlyoffice_token_interno_editable() (no _valido()): además de que la
    // "clave" sea de este documento/acción, exige que se haya emitido para
    // alguien con permiso de EDICIÓN (ver el "editable" que se firma más
    // abajo, al construir $tokenCallback). Sin esto, cualquiera con acceso
    // de solo lectura a un documento podía usar su propia "clave" legítima
    // (válida solo para ESE documento, pero sin distinguir para qué
    // permiso se emitió) para forjar un guardado y sobrescribirlo igual.
    if (!$fuente || !onlyoffice_token_interno_editable($clave, $tipo, $id, 'callback')) {
        http_response_code(403);
        echo json_encode(['error' => 1]);
        exit;
    }

    $crudo = file_get_contents('php://input');
    $body = json_decode($crudo, true) ?: [];

    // El propio OnlyOffice firma esta llamada con SU jwt (ver JWT_HEADER
    // en docker-compose.yml) — sin verificarlo, cualquiera que adivine la
    // URL con la "clave" correcta podría mandar un "guardado" falso. Según
    // la versión llega como header Authorization, o como "token" dentro
    // del body — se acepta cualquiera de los dos que sí firme.
    $encabezadoAuth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $tokenOO = preg_replace('/^Bearer\s+/i', '', trim($encabezadoAuth));
    $payloadOO = $tokenOO !== '' ? onlyoffice_jwt_extraer_payload($tokenOO) : null;
    if ($payloadOO === null && !empty($body['token'])) {
        $payloadOO = onlyoffice_jwt_extraer_payload($body['token']);
    }
    if ($payloadOO === null) {
        http_response_code(403);
        echo json_encode(['error' => 1]);
        exit;
    }

    // A partir de acá SOLO se usa $payloadOO (lo que la firma de OnlyOffice
    // realmente verificó), nunca $body directo — $body es JSON crudo sin
    // firmar, cualquiera que adivine "clave" podía mandar cualquier cosa
    // ahí (status/url/users) y antes se usaba tal cual, sin que la
    // verificación de arriba sirviera para nada real. onlyoffice_jwt_
    // extraer_payload() ya exige que el JWT firmado traiga un "payload"
    // (así es como OnlyOffice envuelve el body real) — si algún día una
    // versión distinta de OnlyOffice no lo envuelve así, esto empieza a
    // rechazar guardados reales (falla cerrado) en vez de aceptar
    // cualquier cosa (fallaba abierto); no se pudo probar contra el
    // contenedor real en esta sesión.
    $status = (int) ($payloadOO['status'] ?? 0);
    if (in_array($status, [2, 6], true) && !empty($payloadOO['url'])) {
        $fila = editor_fila($pdo, $fuente, $id);
        if ($fila && $fila['archivo']) {
            // OnlyOffice arma esta URL con la misma dirección pública por la
            // que el NAVEGADOR cargó su editor (ver ONLYOFFICE_PORT) — pero
            // quien la pide acá es el contenedor "app", que no puede resolver
            // "localhost" (ahí es él mismo) ni ese puerto externo. Como los
            // dos contenedores están en la misma red de Docker, se reescribe
            // el host por el nombre interno del servicio antes de pedirla.
            $urlDescarga = $payloadOO['url'];
            $partesUrl = parse_url($urlDescarga);
            if ($partesUrl) {
                $urlDescarga = 'http://onlyoffice' . ($partesUrl['path'] ?? '') . (isset($partesUrl['query']) ? '?' . $partesUrl['query'] : '');
            }

            // Mismo criterio que CarpetaController.php/DocumentoController.php:
            // nunca se confía en un nombre que venga de fuera — el archivo
            // en disco ya tiene su nombre fijo, esto solo reemplaza su
            // contenido con la versión que acaba de guardar OnlyOffice.
            $contenidoNuevo = @file_get_contents($urlDescarga);
            if ($contenidoNuevo !== false) {
                file_put_contents($carpetaFisica . '/' . $fila['archivo'], $contenidoNuevo);
                if ($fuente['tabla'] === 'direccion_carpeta_archivos') {
                    $pdo->prepare('UPDATE direccion_carpeta_archivos SET peso_bytes = :peso WHERE id = :id')
                        ->execute([':peso' => strlen($contenidoNuevo), ':id' => $id]);
                }

                // "users" en el payload firmado es quién estaba editando
                // cuando se guardó (ver OnlyOffice.php/EditorController.php
                // config "editorConfig.user.id") — sin sesión propia acá
                // (esto lo llama el contenedor OnlyOffice, no un navegador),
                // es la única forma de saber a quién sincronizarle el
                // guardado. Ya no se lee de $body: alguien con la "clave"
                // de un documento propio podía antes poner cualquier id
                // ajeno en users[0] y forzar la sincronización hacia el
                // Drive personal de otra persona.
                $usuarioQueEdito = (int) ($payloadOO['users'][0] ?? 0);
                if ($usuarioQueEdito > 0) {
                    $extensionGuardada = strtolower(pathinfo($fila['archivo'], PATHINFO_EXTENSION));
                    google_drive_oauth_sincronizar_archivo($pdo, $usuarioQueEdito, $fuente['tabla'], $id, $carpetaFisica . '/' . $fila['archivo'], $fila['archivo'], $EXTENSION_A_MIME[$extensionGuardada] ?? 'application/octet-stream');
                }
            }
        }
    }

    echo json_encode(['error' => 0]);
    exit;
}

// ---- GET /editor — la página con el editor embebido ----
if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if (!onlyoffice_configurado()) {
    http_response_code(404);
    mostrar_error(404);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$fila = $fuente ? editor_fila($pdo, $fuente, $id) : null;
$direccionId = $fila && $fila['direccion_id'] !== null ? (int) $fila['direccion_id'] : null;

// Permiso por el módulo DEL ARCHIVO (no por la URL): Gestión Documental
// ('documental') es su propio módulo; el resto sale de la dirección de la fila.
// Para quien no es admin global, "no existe", "tipo desconocido", "módulo sin
// resolver", "módulo vetado" y "otra área" dan TODOS el mismo 403 — así no se
// puede averiguar qué ids existen. El admin global siempre pasa.
if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    $moduloArchivo = !$fila ? null : ($tipo === 'documental' ? 'gestion-documental' : modulo_de_direccion($direccionId));
    if (!$fila || !usuario_puede_ver_archivo_de($moduloArchivo) || !editor_area_permitida($direccionId)) {
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

$extension = strtolower(pathinfo($fila['archivo'], PATHINFO_EXTENSION));
$tipoDocumento = $EXTENSION_A_TIPO_DOC[$extension] ?? null;
if (!$tipoDocumento) {
    // No es un Office editable (ej. alguien tocó la URL a mano con el id
    // de un PDF) — ese ya tiene su propio "Ver" nativo del navegador.
    http_response_code(404);
    mostrar_error(404);
    exit;
}

$ruta = $carpetaFisica . '/' . $fila['archivo'];
if (!is_file($ruta)) {
    http_response_code(404);
    mostrar_error(404);
    exit;
}

// Editar es una acción administrativa, igual que subir/reemplazar el
// archivo (ver usuario_admin_de() en public/index.php) — el resto puede
// abrirlo para consultarlo (modo lectura de OnlyOffice), no modificarlo.
$puedeEditar = usuario_admin_de($direccionId);

// La "key" le dice a OnlyOffice si el archivo cambió desde la última vez
// (para no reusar una versión vieja en caché) — con que cambie cuando el
// archivo cambia alcanza, no hace falta guardarla en la BD.
$claveDocumento = substr(md5($fila['archivo'] . filemtime($ruta) . filesize($ruta)), 0, 32);

$tokenArchivo = onlyoffice_token_interno($tipo, $id, 'archivo');
// $puedeEditar viaja firmado dentro del propio token — ver
// onlyoffice_token_interno_editable() en OnlyOffice.php: el callback lo
// exige antes de escribir nada, así que alguien que abrió este documento
// en modo solo lectura nunca tiene en sus manos una "clave" de callback
// que pase esa validación, sin importar qué Authorization mande.
$tokenCallback = onlyoffice_token_interno($tipo, $id, 'callback', 6 * 3600, $puedeEditar);
// null = sin secreto (ya filtrado arriba por onlyoffice_configurado(), pero
// no se asume: urlencode(null) armaría una URL con "clave=" vacía).
if ($tokenArchivo === null || $tokenCallback === null) {
    http_response_code(503);
    mostrar_error(500);
    exit;
}

// "app" es el nombre del servicio dentro de la red de Docker (ver
// docker-compose.yml) — estas dos URLs las llama el CONTENEDOR de
// OnlyOffice, nunca el navegador, así que van por la red interna en el
// puerto 80 de siempre, no por el ${APP_PORT} con el que el navegador
// entra desde afuera.
$urlArchivo = 'http://app' . BASE_URL . '/editor/archivo?tipo=' . urlencode($tipo) . '&id=' . $id . '&clave=' . urlencode($tokenArchivo);
$urlCallback = 'http://app' . BASE_URL . '/editor/callback?tipo=' . urlencode($tipo) . '&id=' . $id . '&clave=' . urlencode($tokenCallback);

$configEditor = [
    'document' => [
        'fileType' => $extension,
        'key' => $claveDocumento,
        'title' => $fila['nombre'] . '.' . $extension,
        'url' => $urlArchivo,
        'permissions' => [
            'edit' => $puedeEditar,
            'download' => true,
            'print' => true,
        ],
    ],
    'documentType' => $tipoDocumento,
    'editorConfig' => [
        'callbackUrl' => $urlCallback,
        'mode' => $puedeEditar ? 'edit' : 'view',
        'lang' => 'es',
        'user' => [
            'id' => (string) $_SESSION['usuario_id'],
            'name' => $_SESSION['usuario_nombre'] ?? 'Usuario',
        ],
        'customization' => [
            'forcesave' => true,
            'uiTheme' => 'theme-classic-light',
        ],
    ],
];
$configEditor['token'] = onlyoffice_jwt_firmar($configEditor);
if ($configEditor['token'] === null) {
    http_response_code(503);
    mostrar_error(500);
    exit;
}

// El navegador SÍ necesita la URL pública de OnlyOffice (con el puerto que
// mapeaste en tu .env) — ese script lo carga el navegador del usuario, no
// el contenedor, así que va por afuera, no por "http://app".
$porHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
$hostSinPuerto = explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0];
$onlyofficePuerto = getenv('ONLYOFFICE_PORT') ?: '8082';
$onlyofficeUrlPublica = ($porHttps ? 'https' : 'http') . '://' . $hostSinPuerto . ':' . $onlyofficePuerto;

// ¿$ruta es una ruta LOCAL de este sitio? e() escapa HTML pero no impide que
// un href sea "javascript:..." ni una URL externa — así que "volver" solo se
// acepta si empieza con "/", no con "//" (protocol-relative), no lleva "\" ni
// caracteres de control (los navegadores quitan tabs/saltos de línea, así que
// "/\t/evil.com" terminaría siendo "//evil.com") y no trae esquema ni host.
function editor_ruta_local_segura(string $ruta): bool
{
    if ($ruta === '' || $ruta[0] !== '/') {
        return false;
    }
    if (isset($ruta[1]) && ($ruta[1] === '/' || $ruta[1] === '\\')) {
        return false;
    }
    if (preg_match('/[\x00-\x1f\x7f\\\\]/', $ruta)) {
        return false;
    }
    $partes = parse_url($ruta);
    return $partes !== false && !isset($partes['scheme']) && !isset($partes['host']);
}

// Sin "volver" no hay botón (como siempre); con uno inválido se cae al
// listado de Gestión Documental en vez de armar un enlace peligroso.
$volverA = null;
if (isset($_GET['volver'])) {
    $volverA = (is_string($_GET['volver']) && editor_ruta_local_segura($_GET['volver']))
        ? $_GET['volver']
        : BASE_URL . '/gestion-documental';
}

require ROOT_PATH . '/app/Views/Portal/Editor/Editor.php';
