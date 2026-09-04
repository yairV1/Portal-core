<?php
// ══════════════════════════════════════════════════════════
//  routes/web.php — mapa de rutas
//  Cada ruta apunta a un archivo dentro de app/Controllers/
// ══════════════════════════════════════════════════════════

return [
    '/login'  => 'AuthController.php',
    '/logout' => 'AuthController.php',
    '/'       => 'HomeController.php',

    // Módulos del Portal — todos los maneja PortalController.php
    '/tableros'                 => 'PortalController.php',
    '/mapa-portal'               => 'PortalController.php',
    '/gestion-institucional'     => 'PortalController.php',
    '/sgi'                       => 'PortalController.php',
    '/vicerrectoria-academica'   => 'PortalController.php',
    '/administrativa-financiera' => 'PortalController.php',
    '/talento-humano'            => 'PortalController.php',
    '/investigacion-innovacion'  => 'PortalController.php',
    '/gestion-documental'        => 'PortalController.php',
    '/normatividad'              => 'PortalController.php',
    '/novedades'                 => 'PortalController.php',
    '/aplicaciones'              => 'PortalController.php',
];