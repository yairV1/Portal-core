<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/QuienesSomosController.php
//  Landing pública institucional — sin sesión.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

// Módulos del portal: la tabla direcciones real (ya existe desde las
// migraciones 002/003), nada quemado.
$direccionesInfo = $pdo->query('SELECT id, slug, kicker, titulo, descripcion FROM direcciones ORDER BY id')->fetchAll();

// Directivos: mismos cargos reales que ya usa Talento Humano/Directorio,
// filtrados a nivel Directivo. "foto" queda NULL donde todavía no se ha
// subido una real (ver migración 015) — la vista muestra un placeholder.
$directivos = $pdo->query("SELECT nombre, cargo, direccion, foto FROM cargos WHERE nivel = 'Directivo' ORDER BY orden")->fetchAll();

// Encargados por área, agrupados por dirección (ver migración 015 — datos
// reales que antes estaban quemados en los 6 *.js de módulo genérico).
$responsablesPorDireccion = [];
foreach ($pdo->query('
    SELECT r.direccion_id, r.nombre, r.cargo, r.foto, d.titulo AS direccion_titulo
    FROM direccion_responsables r
    JOIN direcciones d ON d.id = r.direccion_id
    ORDER BY d.id, r.orden
')->fetchAll() as $r) {
    $responsablesPorDireccion[$r['direccion_titulo']][] = $r;
}

require ROOT_PATH . '/app/Views/Landing/QuienesSomos.php';
