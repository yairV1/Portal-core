<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/EventoController.php
//  Crea eventos reales en la tabla "eventos" (ver Calendario.php) —
//  misma tabla que ya usa Inicio, así que un evento nuevo aparece en
//  ambos lados sin nada extra.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// ---- /calendario/crear-evento ----
if ($uri === '/calendario/crear-evento') {
    // Mes al que volver tras crear (o tras un error) — el mes que se
    // estaba viendo cuando se abrió el formulario, no necesariamente el
    // mes del evento nuevo.
    $mesVolver = preg_match('/^\d{4}-\d{2}$/', $_POST['mes'] ?? '') ? $_POST['mes'] : date('Y-m');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver);
        exit;
    }

    // Crear eventos es una acción administrativa (mismo criterio que subir
    // documentos — ver DocumentoController.php: rol admin/usuario, sin
    // niveles de acceso todavía, eso vive en el módulo de áreas y permisos
    // pendiente).
    if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=error');
        exit;
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $horaLugar = trim($_POST['hora_lugar'] ?? '');

    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
    $fechaOk = $fechaValida && $fechaValida->format('Y-m-d') === $fecha;

    if ($titulo === '' || !$fechaOk) {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=error');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO eventos (titulo, fecha, hora_lugar) VALUES (:titulo, :fecha, :hora_lugar)');
    $stmt->execute([
        ':titulo'     => $titulo,
        ':fecha'      => $fecha,
        ':hora_lugar' => $horaLugar !== '' ? $horaLugar : null,
    ]);

    // Vuelve al mes del evento recién creado, no al que se estaba viendo,
    // para que se vea de una vez sin tener que navegar hasta ahí.
    header('Location: ' . BASE_URL . '/calendario?mes=' . $fechaValida->format('Y-m') . '&evento=1');
    exit;
}
