<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/HomeController.php
// ══════════════════════════════════════════════════════════

// Exige sesión activa — si no hay usuario logueado, manda al login
if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$titulo = 'Inicio';

// Datos reales del dashboard (ver database/migrations/002_kpis_e_iconos.sql).
// inicio.js espera cada arreglo con la misma forma que antes tenía
// hardcodeada — acá solo se arma esa misma forma desde $pdo.

$MESES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

// Avance PDI: fila protagonista del hero, aparte de la franja de KPIs.
$stmt = $pdo->prepare("SELECT valor, delta FROM kpis WHERE label = 'Avance PDI' LIMIT 1");
$stmt->execute();
$pdiFila = $stmt->fetch();
$pdiValor = '';
$pdiSufijo = '';
if ($pdiFila) {
    preg_match('/^([\d.,]+)(.*)$/', trim($pdiFila['valor']), $m);
    $pdiValor  = $m[1] ?? $pdiFila['valor'];
    $pdiSufijo = trim($m[2] ?? '');
}
$pdiDesc = $pdiFila['delta'] ?? '';

// Franja de KPIs secundaria (todo menos Avance PDI, que ya se usó arriba).
$kpis = [];
$stmt = $pdo->prepare("SELECT label, valor, delta, estado, tendencia, icono FROM kpis WHERE label <> 'Avance PDI' ORDER BY orden");
$stmt->execute();
foreach ($stmt->fetchAll() as $r) {
    $kpis[] = [
        'label' => $r['label'],
        'valor' => $r['valor'],
        'delta' => $r['delta'],
        'estado' => $r['estado'] ?: 'accent',
        'tendencia' => $r['tendencia'] ? array_map('floatval', explode(',', $r['tendencia'])) : [],
        'icono' => $r['icono'] ?: '',
    ];
}

// Accesos rápidos — el de menor "orden" se marca como sugerido (mismo
// criterio que antes tenía el JS a mano, sin necesitar columna nueva).
$accesos = [];
$stmt = $pdo->query('SELECT label, meta, icono, enlace FROM accesos_rapidos ORDER BY orden');
foreach ($stmt->fetchAll() as $i => $r) {
    $enlace = trim((string)$r['enlace']);
    $externo = (bool)preg_match('#^https?://#', $enlace);
    $accesos[] = [
        'label' => $r['label'],
        'meta' => $r['meta'],
        'icono' => $r['icono'] ?: 'app',
        'href' => $enlace === '' ? '' : ($externo ? $enlace : BASE_URL . $enlace),
        'externo' => $externo,
        'sugerido' => $i === 0,
    ];
}

// Documentos recientes — se aprovecha "estado" (Vigente/En revisión/
// Obsoleto), que ya existía en la tabla pero no se mostraba en ningún lado.
$ESTADO_TAG = ['Vigente' => 'success', 'En revisión' => 'warning', 'Obsoleto' => 'danger'];
$docsRecientes = [];
$docsEnRevision = 0;
$stmt = $pdo->query('SELECT nombre, area, version, fecha, estado FROM documentos ORDER BY fecha DESC');
foreach ($stmt->fetchAll() as $r) {
    $fecha = new DateTime($r['fecha']);
    $docsRecientes[] = [
        'nombre' => $r['nombre'],
        'area' => $r['area'],
        'version' => $r['version'],
        'fecha' => $fecha->format('d') . ' ' . $MESES[(int)$fecha->format('n') - 1],
        'estado' => $r['estado'],
        'estadoTag' => $ESTADO_TAG[$r['estado']] ?? 'info',
    ];
    if ($r['estado'] === 'En revisión') $docsEnRevision++;
}

// Novedades institucionales
$noticias = [];
$stmt = $pdo->query('SELECT categoria, titulo, fecha FROM noticias ORDER BY fecha DESC');
foreach ($stmt->fetchAll() as $r) {
    $fecha = new DateTime($r['fecha']);
    $noticias[] = [
        'categoria' => $r['categoria'],
        'titulo' => $r['titulo'],
        'fecha' => $fecha->format('d') . ' ' . $MESES[(int)$fecha->format('n') - 1] . ' ' . $fecha->format('Y'),
    ];
}

// Mis pendientes
$pendientes = [];
$stmt = $pdo->query('SELECT titulo, meta, color FROM pendientes ORDER BY orden');
foreach ($stmt->fetchAll() as $r) {
    $pendientes[] = ['titulo' => $r['titulo'], 'meta' => $r['meta'], 'color' => $r['color'] ?: '#9e1f63'];
}

// Agenda de la semana
$eventos = [];
$stmt = $pdo->query('SELECT titulo, fecha, hora_lugar FROM eventos ORDER BY fecha');
foreach ($stmt->fetchAll() as $r) {
    $fecha = new DateTime($r['fecha']);
    $eventos[] = [
        'dia' => $fecha->format('d'),
        'mes' => $MESES[(int)$fecha->format('n') - 1],
        'titulo' => $r['titulo'],
        'hora' => $r['hora_lugar'],
    ];
}

// Cumpleaños — "Hoy"/"Mañana" se calculan contra la fecha real, no se
// dejan escritos a mano (si no, quedarían mal en cuanto pase el día).
$hoy = new DateTime('today');
$manana = (new DateTime('today'))->modify('+1 day');
$cumpleanos = [];
$stmt = $pdo->query('SELECT nombre, fecha FROM cumpleanos ORDER BY fecha');
foreach ($stmt->fetchAll() as $r) {
    $fecha = new DateTime($r['fecha']);
    $fecha->setDate((int)$hoy->format('Y'), (int)$fecha->format('n'), (int)$fecha->format('j'));
    if ($fecha == $hoy) {
        $cuando = 'Hoy';
    } elseif ($fecha == $manana) {
        $cuando = 'Mañana';
    } else {
        $cuando = $fecha->format('d') . ' ' . $MESES[(int)$fecha->format('n') - 1];
    }
    $partes = preg_split('/\s+/', trim($r['nombre']));
    $iniciales = strtoupper(mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1));
    $cumpleanos[] = ['ini' => $iniciales, 'nombre' => $r['nombre'], 'fecha' => $cuando];
}

require ROOT_PATH . '/app/Views/Portal/Home/Inicio.php';
