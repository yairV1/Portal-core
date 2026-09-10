<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/PendienteController.php
//  "Mis pendientes" es personal — cada usuario crea, ve y borra solo los
//  suyos (ver HomeController.php, que filtra por usuario_id). No hay
//  asignación entre usuarios todavía (eso necesita el módulo de áreas y
//  permisos, ver roadmap) — cada quien administra su propia lista.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/');
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header('Location: ' . BASE_URL . '/?pendiente=error');
    exit;
}

// ---- /pendientes/crear ----
if ($uri === '/pendientes/crear') {
    $titulo = trim($_POST['titulo'] ?? '');
    $meta   = trim($_POST['meta'] ?? '');

    if ($titulo === '') {
        header('Location: ' . BASE_URL . '/?pendiente=error');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO pendientes (usuario_id, titulo, meta) VALUES (:usuario_id, :titulo, :meta)');
    $stmt->execute([
        ':usuario_id' => $_SESSION['usuario_id'],
        ':titulo'     => $titulo,
        ':meta'       => $meta !== '' ? $meta : null,
    ]);

    header('Location: ' . BASE_URL . '/?pendiente=1');
    exit;
}

// ---- /pendientes/completar ----
// Tilda/destilda sin borrar — a diferencia de eliminar, esto se puede
// deshacer (desmarcar la casilla). El checkbox manda "0" (input oculto)
// y, si está marcado, además "1" — el navegador solo se queda con el
// último valor de ese nombre, así que llega "1" marcado o "0" sin marcar.
if ($uri === '/pendientes/completar') {
    $id = (int) ($_POST['id'] ?? 0);
    $completado = ($_POST['completado'] ?? '0') === '1' ? 1 : 0;
    // Se guarda cuándo se marcó como hecho (no solo el booleano) para
    // poder borrarlo automáticamente al mes — ver HomeController.php.
    // Al desmarcarlo se limpia, no tendría sentido dejar una fecha vieja
    // en un pendiente que volvió a estar activo.
    $completadoEn = $completado ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare('UPDATE pendientes SET completado = :completado, completado_en = :completado_en WHERE id = :id AND usuario_id = :usuario_id');
    $stmt->execute([':completado' => $completado, ':completado_en' => $completadoEn, ':id' => $id, ':usuario_id' => $_SESSION['usuario_id']]);

    header('Location: ' . BASE_URL . '/');
    exit;
}

// ---- /pendientes/eliminar ----
if ($uri === '/pendientes/eliminar') {
    $id = (int) ($_POST['id'] ?? 0);

    // Borra solo si es dueño — un id de otro usuario simplemente no
    // afecta ninguna fila (no hace falta un 403 aparte para esto).
    $stmt = $pdo->prepare('DELETE FROM pendientes WHERE id = :id AND usuario_id = :usuario_id');
    $stmt->execute([':id' => $id, ':usuario_id' => $_SESSION['usuario_id']]);

    header('Location: ' . BASE_URL . '/?pendiente=eliminado');
    exit;
}

header('Location: ' . BASE_URL . '/');
