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

    // Crear eventos ya no es solo de admin — cualquier usuario logueado
    // puede crear los suyos (por defecto privados, solo él los ve). Marcar
    // uno como público (visible para todo el portal) sigue siendo admin-
    // only: se decide acá, en servidor, ignorando lo que venga del POST si
    // el usuario no es admin — no basta con ocultar la opción en el form.
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=error');
        exit;
    }

    $esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin';
    $titulo = trim($_POST['titulo'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $horaLugar = trim($_POST['hora_lugar'] ?? '');
    $visibilidad = ($esAdmin && ($_POST['visibilidad'] ?? '') === 'publico') ? 'publico' : 'privado';

    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
    $fechaOk = $fechaValida && $fechaValida->format('Y-m-d') === $fecha;

    if ($titulo === '' || !$fechaOk) {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=error');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO eventos (usuario_id, titulo, fecha, hora_lugar, visibilidad) VALUES (:usuario_id, :titulo, :fecha, :hora_lugar, :visibilidad)');
    $stmt->execute([
        ':usuario_id'  => $_SESSION['usuario_id'],
        ':titulo'      => $titulo,
        ':fecha'       => $fecha,
        ':hora_lugar'  => $horaLugar !== '' ? $horaLugar : null,
        ':visibilidad' => $visibilidad,
    ]);

    // Vuelve al mes del evento recién creado, no al que se estaba viendo,
    // para que se vea de una vez sin tener que navegar hasta ahí.
    header('Location: ' . BASE_URL . '/calendario?mes=' . $fechaValida->format('Y-m') . '&evento=1');
    exit;
}

// ---- /calendario/editar-evento ----
// Corrige un evento ya creado (título/fecha/hora/lugar) — mismo permiso
// que verlo en la agenda de detalle: dueño del evento, o admin (que
// además puede cambiarle la visibilidad, igual que al crear).
if ($uri === '/calendario/editar-evento') {
    $mesVolver = preg_match('/^\d{4}-\d{2}$/', $_POST['mes'] ?? '') ? $_POST['mes'] : date('Y-m');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver);
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=error');
        exit;
    }

    $eventoId = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, usuario_id, visibilidad FROM eventos WHERE id = :id');
    $stmt->execute([':id' => $eventoId]);
    $evento = $stmt->fetch();

    $esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin';
    $puedeEditar = $evento && ($esAdmin || (int) $evento['usuario_id'] === (int) $_SESSION['usuario_id']);
    if (!$puedeEditar) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $horaLugar = trim($_POST['hora_lugar'] ?? '');
    // Un usuario normal no puede subir su propio evento a "publico" al
    // editarlo (misma regla que al crear) — si no es admin, se queda con
    // la visibilidad que ya tenía.
    $visibilidad = $esAdmin
        ? (($_POST['visibilidad'] ?? '') === 'publico' ? 'publico' : 'privado')
        : $evento['visibilidad'];

    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
    $fechaOk = $fechaValida && $fechaValida->format('Y-m-d') === $fecha;

    if ($titulo === '' || !$fechaOk) {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=error');
        exit;
    }

    $stmt = $pdo->prepare('UPDATE eventos SET titulo = :titulo, fecha = :fecha, hora_lugar = :hora_lugar, visibilidad = :visibilidad WHERE id = :id');
    $stmt->execute([
        ':titulo'      => $titulo,
        ':fecha'       => $fecha,
        ':hora_lugar'  => $horaLugar !== '' ? $horaLugar : null,
        ':visibilidad' => $visibilidad,
        ':id'          => $eventoId,
    ]);

    header('Location: ' . BASE_URL . '/calendario?mes=' . $fechaValida->format('Y-m') . '&evento=editado');
    exit;
}

// ---- /calendario/eliminar-evento ----
if ($uri === '/calendario/eliminar-evento') {
    $mesVolver = preg_match('/^\d{4}-\d{2}$/', $_POST['mes'] ?? '') ? $_POST['mes'] : date('Y-m');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver);
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=error');
        exit;
    }

    $eventoId = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, usuario_id FROM eventos WHERE id = :id');
    $stmt->execute([':id' => $eventoId]);
    $evento = $stmt->fetch();

    $esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin';
    $puedeEliminar = $evento && ($esAdmin || (int) $evento['usuario_id'] === (int) $_SESSION['usuario_id']);
    if (!$puedeEliminar) {
        http_response_code(403);
        mostrar_error(403);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM eventos WHERE id = :id');
    $stmt->execute([':id' => $eventoId]);

    header('Location: ' . BASE_URL . '/calendario?mes=' . $mesVolver . '&evento=eliminado');
    exit;
}

// ---- /calendario/exportar ----
// Descarga un .ics de un solo evento — "Agregar a mi calendario" para
// Google/Apple/Outlook, que lo importan de forma nativa sin necesitar
// login ni permisos de API contra esos servicios. Mismo control de acceso
// que ver el evento en la grilla: público, o privado y dueño del evento.
if ($uri === '/calendario/exportar') {
    $eventoId = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT id, usuario_id, titulo, fecha, hora_lugar, visibilidad FROM eventos WHERE id = :id');
    $stmt->execute([':id' => $eventoId]);
    $evento = $stmt->fetch();

    $puedeVerlo = $evento && ($evento['visibilidad'] === 'publico' || (int) $evento['usuario_id'] === (int) $_SESSION['usuario_id']);
    if (!$puedeVerlo) {
        http_response_code(404);
        mostrar_error(404);
        exit;
    }

    // Escapa según RFC 5545 (\, ; , y salto de línea) — el título u hora
    // pueden traer comas o punto y coma escritos por el usuario.
    $icsEscapar = fn (string $t): string => addcslashes($t, "\\;,\n");

    $fecha = (new DateTime($evento['fecha']))->format('Ymd');
    $uid = 'evento-' . $evento['id'] . '@portal-core';
    $resumen = $icsEscapar($evento['titulo']);
    $descripcion = $evento['hora_lugar'] !== null ? $icsEscapar($evento['hora_lugar']) : '';

    $lineas = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Portal CORE//Calendario//ES',
        'CALSCALE:GREGORIAN',
        'BEGIN:VEVENT',
        'UID:' . $uid,
        'DTSTAMP:' . gmdate('Ymd\THis\Z'),
        'DTSTART;VALUE=DATE:' . $fecha,
        'SUMMARY:' . $resumen,
    ];
    if ($descripcion !== '') {
        $lineas[] = 'DESCRIPTION:' . $descripcion;
    }
    $lineas[] = 'END:VEVENT';
    $lineas[] = 'END:VCALENDAR';

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="evento-' . $evento['id'] . '.ics"');
    echo implode("\r\n", $lineas) . "\r\n";
    exit;
}
