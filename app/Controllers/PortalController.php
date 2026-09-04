<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/PortalController.php
//  Un solo controlador para todos los módulos del Portal:
//  exige sesión y muestra la vista correspondiente a $uri.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$modulos = [
    '/tableros'                  => ['titulo' => 'Tableros Estratégicos',       'vista' => 'Tablero_Estratégicos/Tablero.php'],
    '/mapa-portal'                => ['titulo' => 'Mapa del portal',             'vista' => 'Mapa_Portal/Mapa.php'],
    '/gestion-institucional'      => ['titulo' => 'Gestión Institucional',       'vista' => 'Gestión_Institucional/Gestión_Ins.php'],
    '/sgi'                        => ['titulo' => 'Sistema de Gestión Integral', 'vista' => 'Sistema_Gestión_Integral/Sistema_Integral.php'],
    '/vicerrectoria-academica'    => ['titulo' => 'Vicerrectoría Académica',     'vista' => 'Vicerrectoría_Académica/Vicerrectoria.php'],
    '/administrativa-financiera'  => ['titulo' => 'Administrativa y Financiera', 'vista' => 'Administrativa_Financiera/Financiera.php'],
    '/talento-humano'             => ['titulo' => 'Talento Humano',              'vista' => 'Talento_Humano/Tal_Humano.php'],
    '/investigacion-innovacion'   => ['titulo' => 'Investigación e Innovación',  'vista' => 'Investigacón_Innovación/Investigacion.php'],
    '/gestion-documental'         => ['titulo' => 'Gestión Documental',          'vista' => 'Gestión_Documental/Documental.php'],
    '/normatividad'               => ['titulo' => 'Normatividad',                'vista' => 'Normatividad/Normatividad.php'],
    '/novedades'                  => ['titulo' => 'Novedades',                   'vista' => 'Novedades/Novedades.php'],
    '/aplicaciones'               => ['titulo' => 'Aplicaciones',                'vista' => 'Aplicaciones/Aplicaciones.php'],
];

$modulo = $modulos[$uri] ?? null;

if (!$modulo) {
    http_response_code(404);
    echo '<h1>404 — Página no encontrada</h1><p><a href="' . BASE_URL . '/">Volver al inicio</a></p>';
    exit;
}

$titulo = $modulo['titulo'];
require ROOT_PATH . '/app/Views/Portal/' . $modulo['vista'];
