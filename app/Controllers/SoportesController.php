<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/SoportesController.php
//  "Soportes" es la bitácora técnica del admin global (fallos/mejoras del
//  propio sistema, ver migración 040_soportes.sql) — no es contenido
//  institucional ni algo que otros roles vean o creen, solo admin.
//  Mismo patrón que PendienteController.php (crear/completar/eliminar),
//  solo que acá no hay usuario_id: hay un único admin global viéndola.
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/');
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header('Location: ' . BASE_URL . '/?soporte=error');
    exit;
}

// ---- /soportes/crear ----
if ($uri === '/soportes/crear') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = ($_POST['tipo'] ?? '') === 'mejora' ? 'mejora' : 'fallo';

    if ($titulo === '') {
        header('Location: ' . BASE_URL . '/?soporte=error');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO soportes (titulo, descripcion, tipo) VALUES (:titulo, :descripcion, :tipo)');
    $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion !== '' ? $descripcion : null,
        ':tipo' => $tipo,
    ]);

    header('Location: ' . BASE_URL . '/?soporte=1');
    exit;
}

// ---- /soportes/completar ----
// Mismo criterio que /pendientes/completar: tilda/destilda sin borrar.
if ($uri === '/soportes/completar') {
    $id = (int) ($_POST['id'] ?? 0);
    $resuelto = ($_POST['resuelto'] ?? '0') === '1' ? 1 : 0;
    $resueltoEn = $resuelto ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare('UPDATE soportes SET resuelto = :resuelto, resuelto_en = :resuelto_en WHERE id = :id');
    $stmt->execute([':resuelto' => $resuelto, ':resuelto_en' => $resueltoEn, ':id' => $id]);

    header('Location: ' . BASE_URL . '/');
    exit;
}

// ---- /soportes/eliminar ----
if ($uri === '/soportes/eliminar') {
    $id = (int) ($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM soportes WHERE id = :id')->execute([':id' => $id]);

    header('Location: ' . BASE_URL . '/?soporte=eliminado');
    exit;
}

header('Location: ' . BASE_URL . '/');
