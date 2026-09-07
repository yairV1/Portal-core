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

        $stmt = $pdo->prepare("SELECT id, nombre, correo, password_hash, cargo, rol FROM usuarios WHERE correo = :correo");
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
            $_SESSION['ultima_actividad'] = time();

            // El ?bienvenida=1 le dice a portal-footer.php que muestre el
            // aviso de bienvenida con SweetAlert2 (ver ese archivo).
            header('Location: ' . BASE_URL . '/?bienvenida=1');
            exit;
        }
    }
}

require ROOT_PATH . '/app/Views/Auth/login.php';