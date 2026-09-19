<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/UsuariosController.php
//  Panel de usuarios y roles — solo para el admin global (ver migración
//  036_roles_por_direccion.sql). Crea/edita/elimina cuentas y decide su
//  rol: 'usuario' (solo lectura, como siempre), 'admin_direccion'
//  (administra una única dirección — carpetas/documentos de ese módulo,
//  nada más) o 'admin' (todo el portal, igual que hoy).
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    mostrar_error(403);
    exit;
}

const ROLES_VALIDOS = ['usuario', 'admin_direccion', 'admin'];

// Resuelve a qué catalogo_cargos.id corresponde lo que mandó el formulario:
// '__nuevo__' + texto -> crea el cargo si no existía (o reusa el que ya
// existe con ese nombre exacto) y devuelve su id; un id existente -> se
// castea tal cual; vacío ("Sin cargo") -> null. Ver migración
// 046_catalogo_cargos.sql — cargo dejó de ser texto libre en `usuarios`
// justamente para poder usarlo como llave de permisos sin typos.
function resolver_cargo_id(PDO $pdo, string $cargoIdPost, string $cargoNuevo): ?int
{
    if ($cargoIdPost === '__nuevo__') {
        $nombre = trim($cargoNuevo);
        if ($nombre === '') {
            return null;
        }
        $pdo->prepare('INSERT IGNORE INTO catalogo_cargos (nombre) VALUES (:nombre)')->execute([':nombre' => $nombre]);
        $stmt = $pdo->prepare('SELECT id FROM catalogo_cargos WHERE nombre = :nombre');
        $stmt->execute([':nombre' => $nombre]);
        return (int) $stmt->fetchColumn();
    }
    return $cargoIdPost !== '' ? (int) $cargoIdPost : null;
}

function usuarios_volver(string $resultado): void
{
    header('Location: ' . BASE_URL . '/usuarios?usuarios=' . $resultado);
    exit;
}

function usuarios_csrf_ok(): bool
{
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '');
}

// ---- /usuarios/crear ----
if ($uri === '/usuarios/crear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !usuarios_csrf_ok()) {
        usuarios_volver('error');
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim(strtolower($_POST['correo'] ?? ''));
    $cargoId = resolver_cargo_id($pdo, $_POST['cargo_id'] ?? '', $_POST['cargo_nuevo'] ?? '');
    $rol = in_array($_POST['rol'] ?? '', ROLES_VALIDOS, true) ? $_POST['rol'] : 'usuario';
    // Área de trabajo: obligatoria para admin_direccion (administra esa
    // dirección), opcional para 'usuario' (si se la asignas, ve SOLO esa
    // dirección — ver usuario_area_asignada() en public/index.php; sin
    // asignar, sigue viendo todo el portal como siempre). 'admin' nunca
    // queda atado a ninguna.
    $direccionIdPedida = (int) ($_POST['direccion_id'] ?? 0);
    $direccionId = in_array($rol, ['admin_direccion', 'usuario'], true) && $direccionIdPedida ? $direccionIdPedida : null;
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        usuarios_volver('datos');
    }
    if ($rol === 'admin_direccion' && !$direccionId) {
        usuarios_volver('direccion');
    }
    // La dirección tiene que existir de verdad — el <select> del formulario
    // solo ofrece direcciones reales, pero nada impide un POST directo con
    // un id inventado (mismo criterio que ya usa DocumentoController.php al
    // crear un direccion_documentos). Sí existe usuarios_direccion_fk
    // (migración 036), así que sin este chequeo un id inventado ya fallaba
    // — pero como una PDOException sin capturar (error 500 en blanco) en
    // vez de un aviso claro para quien administra usuarios.
    if ($direccionId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM direcciones WHERE id = :id');
        $stmt->execute([':id' => $direccionId]);
        if (!$stmt->fetch()) {
            usuarios_volver('direccion');
        }
    }
    // Mismo criterio que direccion_id arriba: resolver_cargo_id() ya
    // garantiza un id válido cuando viene de "+ Agregar nuevo cargo..."
    // (lo acaba de crear/reusar), pero un id existente pedido por el
    // <select> puede haberse borrado justo antes del submit — sin este
    // chequeo, usuarios_cargo_fk (migración 046) igual lo hubiera evitado,
    // pero como una PDOException sin capturar en vez de un aviso claro.
    if ($cargoId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM catalogo_cargos WHERE id = :id');
        $stmt->execute([':id' => $cargoId]);
        if (!$stmt->fetch()) {
            usuarios_volver('cargo');
        }
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE correo = :correo');
    $stmt->execute([':correo' => $correo]);
    if ($stmt->fetch()) {
        usuarios_volver('correo_existente');
    }

    $pdo->prepare('INSERT INTO usuarios (nombre, correo, password_hash, cargo_id, rol, direccion_id) VALUES (:nombre, :correo, :hash, :cargo, :rol, :did)')
        ->execute([
            ':nombre' => $nombre, ':correo' => $correo, ':hash' => password_hash($password, PASSWORD_DEFAULT),
            ':cargo' => $cargoId, ':rol' => $rol, ':did' => $direccionId,
        ]);

    usuarios_volver('creado');
}

// ---- /usuarios/editar ----
if ($uri === '/usuarios/editar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !usuarios_csrf_ok()) {
        usuarios_volver('error');
    }
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        usuarios_volver('error');
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim(strtolower($_POST['correo'] ?? ''));
    $cargoId = resolver_cargo_id($pdo, $_POST['cargo_id'] ?? '', $_POST['cargo_nuevo'] ?? '');
    $rol = in_array($_POST['rol'] ?? '', ROLES_VALIDOS, true) ? $_POST['rol'] : 'usuario';
    $direccionIdPedida = (int) ($_POST['direccion_id'] ?? 0);
    $direccionId = in_array($rol, ['admin_direccion', 'usuario'], true) && $direccionIdPedida ? $direccionIdPedida : null;
    $password = $_POST['password'] ?? ''; // vacío = no cambiar la contraseña

    if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        usuarios_volver('datos');
    }
    if ($rol === 'admin_direccion' && !$direccionId) {
        usuarios_volver('direccion');
    }
    // Misma validación que /usuarios/crear — ver el comentario ahí.
    if ($direccionId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM direcciones WHERE id = :id');
        $stmt->execute([':id' => $direccionId]);
        if (!$stmt->fetch()) {
            usuarios_volver('direccion');
        }
    }
    if ($cargoId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM catalogo_cargos WHERE id = :id');
        $stmt->execute([':id' => $cargoId]);
        if (!$stmt->fetch()) {
            usuarios_volver('cargo');
        }
    }
    if ($password !== '' && strlen($password) < 8) {
        usuarios_volver('datos');
    }
    // Nunca te puedes quitar a ti mismo el rol admin global — sin esto un
    // descuido de un clic puede dejar el portal sin ningún admin global.
    if ($id === (int) $_SESSION['usuario_id'] && $rol !== 'admin') {
        usuarios_volver('auto_rol');
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE correo = :correo AND id <> :id');
    $stmt->execute([':correo' => $correo, ':id' => $id]);
    if ($stmt->fetch()) {
        usuarios_volver('correo_existente');
    }

    $campos = 'nombre = :nombre, correo = :correo, cargo_id = :cargo, rol = :rol, direccion_id = :did';
    $params = [':nombre' => $nombre, ':correo' => $correo, ':cargo' => $cargoId, ':rol' => $rol, ':did' => $direccionId, ':id' => $id];
    if ($password !== '') {
        $campos .= ', password_hash = :hash';
        $params[':hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    $pdo->prepare("UPDATE usuarios SET {$campos} WHERE id = :id")->execute($params);

    // Si el usuario editado tiene una sesión activa en este mismo servidor
    // (PHP sessions en disco), no la vamos a invalidar a mitad de camino —
    // el próximo login ya toma el rol/dirección nuevos, mismo criterio que
    // el resto del portal (ver public/index.php, no hay invalidación
    // remota de sesiones todavía).

    usuarios_volver('actualizado');
}

// ---- /usuarios/eliminar ----
if ($uri === '/usuarios/eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !usuarios_csrf_ok()) {
        usuarios_volver('error');
    }
    $id = (int) ($_POST['id'] ?? 0);
    if ($id === (int) $_SESSION['usuario_id']) {
        usuarios_volver('auto_eliminar'); // no te puedes borrar a ti mismo
    }
    if ($id > 0) {
        $pdo->prepare('DELETE FROM usuarios WHERE id = :id')->execute([':id' => $id]);
    }
    usuarios_volver('eliminado');
}

// ---- /usuarios (panel) ----
$usuarios = $pdo->query('
    SELECT u.id, u.nombre, u.correo, u.cargo_id, cc.nombre AS cargo_nombre, u.rol, u.direccion_id, u.creado_en, d.titulo AS direccion_titulo
    FROM usuarios u
    LEFT JOIN direcciones d ON d.id = u.direccion_id
    LEFT JOIN catalogo_cargos cc ON cc.id = u.cargo_id
    ORDER BY u.nombre
')->fetchAll();

$direccionesDisponibles = $pdo->query('SELECT id, titulo FROM direcciones ORDER BY titulo')->fetchAll();
$cargosDisponibles = $pdo->query('SELECT id, nombre FROM catalogo_cargos ORDER BY nombre')->fetchAll();

require ROOT_PATH . '/app/Views/Portal/Usuarios/Usuarios.php';
