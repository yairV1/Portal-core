    <?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/AuthController.php
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

// ---- /logout ----
if ($uri === '/logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// ---- /login ----
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificación del token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Tu sesión de formulario expiró, intenta de nuevo.';
    } else {
        $correo   = trim(strtolower($_POST['correo'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT id, nombre, correo, password_hash, cargo, rol FROM usuarios WHERE correo = :correo");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            $error = 'Correo o contraseña incorrectos.';
        } else {
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            $_SESSION['usuario_cargo']  = $usuario['cargo'];
            $_SESSION['usuario_rol']    = $usuario['rol'];

            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
}

require ROOT_PATH . '/app/Views/Auth/login.php';