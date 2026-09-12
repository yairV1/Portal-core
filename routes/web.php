<?php
// ══════════════════════════════════════════════════════════
//  routes/web.php — mapa de rutas
//  Cada ruta apunta a un archivo dentro de app/Controllers/
// ══════════════════════════════════════════════════════════

return [
    '/login'                => 'AuthController.php',
    '/logout'               => 'AuthController.php',
    '/auth/google'          => 'AuthController.php',
    '/auth/google/callback' => 'AuthController.php',
    '/'                     => 'HomeController.php',

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

    '/pendientes/crear'     => 'PendienteController.php',
    '/pendientes/completar' => 'PendienteController.php',
    '/pendientes/eliminar'  => 'PendienteController.php',

    // Módulos del Portal — todos los maneja PortalController.php
    '/cuadro-mando-integral'                        => 'PortalController.php',
    '/cuadro-mando-integral/finanzas'                => 'PortalController.php',
    '/cuadro-mando-integral/planeacion'              => 'PortalController.php',
    '/cuadro-mando-integral/vicerrectoria-academica' => 'PortalController.php',
    '/cuadro-mando-integral/investigacion'           => 'PortalController.php',
    '/mapa-portal'               => 'PortalController.php',
    '/gestion-institucional'     => 'PortalController.php',
    '/sgi'                       => 'PortalController.php',
    '/vicerrectoria-academica'   => 'PortalController.php',
    '/administrativa-financiera' => 'PortalController.php',
    '/administrativa-financiera/carpetas/crear'      => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/subir'      => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/importar-drive' => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/descargar'  => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/eliminar'   => 'CarpetaController.php',
    '/talento-humano'            => 'PortalController.php',
    '/talento-humano/carpetas/crear'         => 'CarpetaController.php',
    '/talento-humano/carpetas/subir'         => 'CarpetaController.php',
    '/talento-humano/carpetas/importar-drive'=> 'CarpetaController.php',
    '/talento-humano/carpetas/descargar'     => 'CarpetaController.php',
    '/talento-humano/carpetas/eliminar'      => 'CarpetaController.php',
    '/investigacion-innovacion'  => 'PortalController.php',
    '/gestion-documental'         => 'PortalController.php',
    '/documentos/crear'           => 'DocumentoController.php',
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

    // Contenido de la landing pública — solo admin (ver PortalController /
    // registrado en su propio controlador porque no es un módulo del
    // Portal como los demás, es el contenido institucional de "/").
    '/contenido-landing'                     => 'ContenidoLandingController.php',
    '/contenido-landing/guardar-seccion'     => 'ContenidoLandingController.php',
    '/contenido-landing/estadistica/guardar' => 'ContenidoLandingController.php',
    '/contenido-landing/estadistica/eliminar'=> 'ContenidoLandingController.php',
    '/contenido-landing/modulo/guardar'      => 'ContenidoLandingController.php',
    '/contenido-landing/modulo/eliminar'     => 'ContenidoLandingController.php',
    '/contenido-landing/rol/guardar'         => 'ContenidoLandingController.php',
    '/contenido-landing/rol/eliminar'        => 'ContenidoLandingController.php',
    '/contenido-landing/paso/guardar'        => 'ContenidoLandingController.php',
    '/contenido-landing/paso/eliminar'       => 'ContenidoLandingController.php',
];