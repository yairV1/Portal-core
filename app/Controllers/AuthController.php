<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/AuthController.php
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

// ---- /logout ----
if ($uri === '/logout') {
    // POST + CSRF, igual que cualquier otra acción que cambia estado — sin
    // esto, un GET simple (ej. un <img>/link en un sitio externo) podía
    // cerrarle la sesión a un usuario logueado sin que él lo pidiera.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/');
        exit;
    }
    $_SESSION = [];
    session_destroy();
    // El ?salida=1 le dice a login.php que muestre el aviso de "sesión
    // cerrada" con SweetAlert2 (ver el <script> al final de esa vista).
    header('Location: ' . BASE_URL . '/login?salida=1');
    exit;
}

// ---- /auth/google ----
// Inicia el flujo del botón "Continuar con Google": arma la URL de
// consentimiento de Google y redirige. El client_secret NUNCA viaja acá —
// solo se usa en el intercambio servidor-a-servidor de /auth/google/callback.
// Las credenciales salen de config/google.php (variable de entorno o
// config/google.local.php, ver GoogleDrive.php::google_oauth_credenciales).
if ($uri === '/auth/google') {
    require_once ROOT_PATH . '/app/Helpers/GoogleDrive.php';
    $credenciales = google_oauth_credenciales();

    if ($credenciales['client_id'] === '' || $credenciales['client_secret'] === '') {
        header('Location: ' . BASE_URL . '/login?google_error=no_configurado');
        exit;
    }

    // state anti-CSRF: si el callback llega con un state distinto al que
    // guardamos acá, alguien más disparó el flujo (o es un enlace viejo) —
    // no seguimos.
    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;

    $porHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    $redirectUri = ($porHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . BASE_URL . '/auth/google/callback';

    $parametros = http_build_query([
        'client_id'     => $credenciales['client_id'],
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'prompt'        => 'select_account',
    ]);

    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $parametros);
    exit;
}

// ---- /auth/google/callback ----
// Google trae de vuelta ?code=...&state=...: acá se cambia ese code por un
// access_token (llamada servidor-a-servidor), se busca la cuenta por correo
// y —si no existe— se crea para que entre solo por Google (misma lógica que
// el login con Google anterior). El correo ya viene verificado por Google.
if ($uri === '/auth/google/callback') {
    if (!empty($_GET['error'])) {
        // El usuario canceló el consentimiento en Google — no es un error real.
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    $stateRecibido = $_GET['state'] ?? '';
    $stateEsperado = $_SESSION['google_oauth_state'] ?? '';
    unset($_SESSION['google_oauth_state']);

    if ($stateEsperado === '' || !hash_equals($stateEsperado, $stateRecibido)) {
        header('Location: ' . BASE_URL . '/login?google_error=1');
        exit;
    }

    require_once ROOT_PATH . '/app/Helpers/GoogleDrive.php';
    $credenciales = google_oauth_credenciales();
    $code = $_GET['code'] ?? '';

    if ($credenciales['client_id'] === '' || $credenciales['client_secret'] === '' || $code === '') {
        header('Location: ' . BASE_URL . '/login?google_error=1');
        exit;
    }

    $porHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    $redirectUri = ($porHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . BASE_URL . '/auth/google/callback';

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'code'          => $code,
            'client_id'     => $credenciales['client_id'],
            'client_secret' => $credenciales['client_secret'],
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $tokenRespuesta = curl_exec($ch);
    $tokenHttpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $tokenData = json_decode((string) $tokenRespuesta, true);
    if ($tokenHttpCode !== 200 || empty($tokenData['access_token'])) {
        header('Location: ' . BASE_URL . '/login?google_error=1');
        exit;
    }

    // Con el access_token pedimos los datos básicos de la cuenta de Google
    // (nunca su contraseña — eso vive solo en Google). email_verified viene
    // de Google: solo permitimos correos verificados.
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $tokenData['access_token']],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $perfilRespuesta = curl_exec($ch);
    $perfilHttpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $perfil = json_decode((string) $perfilRespuesta, true);
    if ($perfilHttpCode !== 200 || empty($perfil['email']) || empty($perfil['email_verified'])) {
        header('Location: ' . BASE_URL . '/login?google_error=1');
        exit;
    }

    $correo = trim(strtolower($perfil['email']));

    $consultaUsuario = $pdo->prepare('
        SELECT u.id, u.nombre, u.correo, u.cargo_id, cc.nombre AS cargo_nombre, u.rol, u.direccion_id, u.foto
        FROM usuarios u
        LEFT JOIN catalogo_cargos cc ON cc.id = u.cargo_id
        WHERE u.correo = :correo
    ');
    $consultaUsuario->execute([':correo' => $correo]);
    $usuario = $consultaUsuario->fetch();

    if (!$usuario) {
        // No existe: se crea con password_hash aleatorio e inutilizable para
        // que esa cuenta entre SOLO por Google, nunca por contraseña. foto
        // NULL: el portal arma las rutas con BASE_URL (ver portal-header.php).
        $nombre = trim((string) ($perfil['name'] ?? ''));
        if ($nombre === '') {
            $nombre = strstr($correo, '@', true) ?: $correo;
        }
        $hashAleatorio = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        try {
            $pdo->prepare('
                INSERT INTO usuarios (nombre, correo, password_hash, cargo_id, rol, direccion_id, foto)
                VALUES (:nombre, :correo, :hash, NULL, :rol, NULL, NULL)
            ')->execute([
                ':nombre' => $nombre,
                ':correo' => $correo,
                ':hash'   => $hashAleatorio,
                ':rol'    => 'usuario',
            ]);
        } catch (PDOException $e) {
            // Carrera: otra petición creó la cuenta entre el SELECT y el
            // INSERT (correo es UNIQUE). Se reintenta el SELECT.
            error_log('Google login: el INSERT del usuario nuevo falló (posible carrera): ' . $e->getMessage());
        }

        $consultaUsuario->execute([':correo' => $correo]);
        $usuario = $consultaUsuario->fetch();
        if (!$usuario) {
            header('Location: ' . BASE_URL . '/login?google_error=no_registrado');
            exit;
        }
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id']           = $usuario['id'];
    $_SESSION['usuario_nombre']       = $usuario['nombre'];
    $_SESSION['usuario_correo']       = $usuario['correo'];
    $_SESSION['usuario_cargo']        = $usuario['cargo_nombre'];
    $_SESSION['usuario_cargo_id']     = $usuario['cargo_id'];
    $_SESSION['usuario_rol']          = $usuario['rol'];
    $_SESSION['usuario_direccion_id'] = $usuario['direccion_id'];
    $_SESSION['usuario_foto']         = $usuario['foto'];
    $_SESSION['ultima_actividad']     = time();

    header('Location: ' . BASE_URL . '/?bienvenida=1');
    exit;
}

// ---- /login ----
// Login con correo y contraseña.
$error = null;

// Límite de intentos fallidos por IP (tabla intentos_login, ver
// database/migrations/001_intentos_login.sql): protege contra fuerza
// bruta sin necesitar saber de antemano qué correos existen — el mensaje
// de error ya es el mismo "correo o contraseña incorrectos" en ambos casos.
define('LOGIN_MAX_INTENTOS', 5);
define('LOGIN_BLOQUEO_MINUTOS', 15);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $stmt = $pdo->prepare('SELECT intentos, bloqueado_hasta FROM intentos_login WHERE ip = :ip');
    $stmt->execute([':ip' => $ip]);
    $registroIntentos = $stmt->fetch();
    $intentosPrevios  = $registroIntentos ? (int) $registroIntentos['intentos'] : 0;
    $bloqueadoHasta   = $registroIntentos['bloqueado_hasta'] ?? null;

    if ($bloqueadoHasta && strtotime($bloqueadoHasta) > time()) {
        $minutos = (int) ceil((strtotime($bloqueadoHasta) - time()) / 60);
        $error = 'Demasiados intentos fallidos. Intenta de nuevo en ' . $minutos . ' minuto' . ($minutos === 1 ? '' : 's') . '.';
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Tu sesión de formulario expiró, intenta de nuevo.';
    } else {
        $correo   = trim(strtolower($_POST['correo'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre, u.correo, u.password_hash, u.cargo_id, cc.nombre AS cargo_nombre, u.rol, u.direccion_id, u.foto
            FROM usuarios u
            LEFT JOIN catalogo_cargos cc ON cc.id = u.cargo_id
            WHERE u.correo = :correo
        ");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            $intentos = $intentosPrevios + 1;
            $bloqueo  = null;
            if ($intentos >= LOGIN_MAX_INTENTOS) {
                $bloqueo = date('Y-m-d H:i:s', time() + LOGIN_BLOQUEO_MINUTOS * 60);
                $error   = 'Demasiados intentos fallidos. Intenta de nuevo en ' . LOGIN_BLOQUEO_MINUTOS . ' minutos.';
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }

            $pdo->prepare('
                INSERT INTO intentos_login (ip, intentos, bloqueado_hasta, ultimo_intento)
                VALUES (:ip1, :intentos1, :bloqueo1, NOW())
                ON DUPLICATE KEY UPDATE intentos = :intentos2, bloqueado_hasta = :bloqueo2, ultimo_intento = NOW()
            ')->execute([
                ':ip1' => $ip, ':intentos1' => $intentos, ':bloqueo1' => $bloqueo,
                ':intentos2' => $intentos, ':bloqueo2' => $bloqueo,
            ]);
        } else {
            // Login correcto: limpia el contador de intentos de esta IP.
            $pdo->prepare('DELETE FROM intentos_login WHERE ip = :ip')->execute([':ip' => $ip]);

            session_regenerate_id(true);
            $_SESSION['usuario_id']          = $usuario['id'];
            $_SESSION['usuario_nombre']      = $usuario['nombre'];
            $_SESSION['usuario_correo']      = $usuario['correo'];
            $_SESSION['usuario_cargo']       = $usuario['cargo_nombre'];
            $_SESSION['usuario_cargo_id']    = $usuario['cargo_id'];
            $_SESSION['usuario_rol']         = $usuario['rol'];
            $_SESSION['usuario_direccion_id'] = $usuario['direccion_id'];
            $_SESSION['usuario_foto']        = $usuario['foto'];
            $_SESSION['ultima_actividad']    = time();

            // El ?bienvenida=1 le dice a portal-footer.php que muestre el
            // aviso de bienvenida con SweetAlert2 (ver ese archivo).
            header('Location: ' . BASE_URL . '/?bienvenida=1');
            exit;
        }
    }
}

require ROOT_PATH . '/app/Views/Auth/login.php';
