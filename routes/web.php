<?php
// ══════════════════════════════════════════════════════════
//  routes/web.php — mapa de rutas
//  Cada ruta apunta a un archivo dentro de app/Controllers/
// ══════════════════════════════════════════════════════════

return [
    '/login'  => 'AuthController.php',
    '/logout' => 'AuthController.php',
    '/'       => 'HomeController.php',

    // Trabaja con nosotros — landing pública (sin sesión) + panel admin
    '/trabaja-con-nosotros'           => 'TrabajoController.php',
    '/trabaja-con-nosotros/postular'  => 'TrabajoController.php',
    '/postulaciones'                  => 'TrabajoController.php',
    '/postulaciones/descargar'        => 'TrabajoController.php',

    // Quiénes somos — landing pública institucional (sin sesión)
    '/quienes-somos' => 'QuienesSomosController.php',

    // Contratación — acceso solo por enlace con token (sin sesión) + panel admin
    '/contratacion'            => 'ContratacionController.php',
    '/contratacion/enviar'     => 'ContratacionController.php',
    '/contrataciones'          => 'ContratacionController.php',
    '/contrataciones/generar'  => 'ContratacionController.php',
    '/contrataciones/eliminar'=> 'ContratacionController.php',
    '/contrataciones/descargar'=> 'ContratacionController.php',

    '/perfil' => 'PerfilController.php',

    // Módulos del Portal — todos los maneja PortalController.php
    '/tableros'                 => 'PortalController.php',
    '/mapa-portal'               => 'PortalController.php',
    '/gestion-institucional'     => 'PortalController.php',
    '/sgi'                       => 'PortalController.php',
    '/vicerrectoria-academica'   => 'PortalController.php',
    '/administrativa-financiera' => 'PortalController.php',
    '/talento-humano'            => 'PortalController.php',
    '/investigacion-innovacion'  => 'PortalController.php',
    '/gestion-documental'         => 'PortalController.php',
    '/documentos/subir'           => 'DocumentoController.php',
    '/documentos/descargar'       => 'DocumentoController.php',
    '/normatividad'              => 'PortalController.php',
    '/novedades'                 => 'PortalController.php',
    '/aplicaciones'              => 'PortalController.php',
    '/directorio'                => 'PortalController.php',
    '/calendario'                => 'PortalController.php',
    '/calendario/crear-evento'   => 'EventoController.php',
    '/calendario/editar-evento'  => 'EventoController.php',
    '/calendario/eliminar-evento'=> 'EventoController.php',
    '/calendario/exportar'       => 'EventoController.php',
];