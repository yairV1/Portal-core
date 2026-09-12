<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/AuthController.php
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

// ---- /logout ----
if ($uri === '/logout') {
    $_SESSION = [];
    session_destroy();
    // El ?salida=1 le dice a login.php que muestre el aviso de "sesión
    // cerrada" con SweetAlert2 (ver el <script> al final de esa vista).
    header('Location: ' . BASE_URL . '/login?salida=1');
    exit;
}

// ---- /auth/google ----
// Inicia el flujo: arma la URL de consentimiento de Google y redirige.
// El client_secret NUNCA viaja acá — solo se usa en el intercambio
// servidor-a-servidor de /auth/google/callback.
if ($uri === '/auth/google') {
    $googleClientId = getenv('GOOGLE_CLIENT_ID') ?: '';
    if ($googleClientId === '') {
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
        'client_id'     => $googleClientId,
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
// access_token (llamada servidor-a-servidor) y se busca la cuenta por
// correo — no se crean usuarios nuevos desde acá, el alta la sigue
// haciendo un administrador (mismo modelo que el login con contraseña).
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

    $googleClientId     = getenv('GOOGLE_CLIENT_ID') ?: '';
    $googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
    $code = $_GET['code'] ?? '';

    if ($googleClientId === '' || $googleClientSecret === '' || $code === '') {
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
            'client_id'     => $googleClientId,
            'client_secret' => $googleClientSecret,
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
    // (nunca su contraseña — eso vive solo en Google).
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

    $stmt = $pdo->prepare('SELECT id, nombre, correo, cargo, rol, foto FROM usuarios WHERE correo = :correo');
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        header('Location: ' . BASE_URL . '/login?google_error=no_registrado');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id']       = $usuario['id'];
    $_SESSION['usuario_nombre']   = $usuario['nombre'];
    $_SESSION['usuario_correo']   = $usuario['correo'];
    $_SESSION['usuario_cargo']    = $usuario['cargo'];
    $_SESSION['usuario_rol']      = $usuario['rol'];
    $_SESSION['usuario_foto']     = $usuario['foto'];
    $_SESSION['ultima_actividad'] = time();

    header('Location: ' . BASE_URL . '/?bienvenida=1');
    exit;
}

// ---- /login ----
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

        $stmt = $pdo->prepare("SELECT id, nombre, correo, password_hash, cargo, rol, foto FROM usuarios WHERE correo = :correo");
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
            $_SESSION['usuario_id']       = $usuario['id'];
            $_SESSION['usuario_nombre']   = $usuario['nombre'];
            $_SESSION['usuario_correo']   = $usuario['correo'];
            $_SESSION['usuario_cargo']    = $usuario['cargo'];
            $_SESSION['usuario_rol']      = $usuario['rol'];
            $_SESSION['usuario_foto']     = $usuario['foto'];
            $_SESSION['ultima_actividad'] = time();

            // El ?bienvenida=1 le dice a portal-footer.php que muestre el
            // aviso de bienvenida con SweetAlert2 (ver ese archivo).
            header('Location: ' . BASE_URL . '/?bienvenida=1');
            exit;
        }
    }
}

require ROOT_PATH . '/app/Views/Auth/login.php';