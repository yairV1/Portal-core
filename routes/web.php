<?php
// ══════════════════════════════════════════════════════════
//  routes/web.php — mapa de rutas
//  Cada ruta apunta a un archivo dentro de app/Controllers/
// ══════════════════════════════════════════════════════════

return [
    '/error/403'            => 'ErrorController.php',
    '/error/500'            => 'ErrorController.php',
    '/login'                => 'AuthController.php',
    '/logout'               => 'AuthController.php',
    // Botón "Continuar con Google" — flujo por redirección: /auth/google
    // arma el consentimiento de Google y /auth/google/callback intercambia
    // el code por la sesión (ver AuthController.php).
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

    // Panel central del admin global — reúne Usuarios/Contenido landing/
    // Contrataciones/Postulaciones en un solo ítem de sidebar (ver
    // migración 038_nav_administracion.sql)
    '/administracion' => 'AdminController.php',

    // "Todos los módulos" — reúne Direcciones/Recursos/Analítica en un
    // solo ítem de sidebar (ver migración 039_nav_todos_los_modulos.sql)
    '/modulos' => 'ModulosController.php',

    // Permisos por rol — solo admin global (ver migración
    // 044_permisos_rol_nav_item.sql)
    '/permisos-por-rol'          => 'PermisosController.php',
    '/permisos-por-rol/guardar'  => 'PermisosController.php',

    // Panel de usuarios y roles — solo admin global (ver migración
    // 036_roles_por_direccion.sql)
    '/usuarios'         => 'UsuariosController.php',
    '/usuarios/crear'   => 'UsuariosController.php',
    '/usuarios/editar'  => 'UsuariosController.php',
    '/usuarios/eliminar'=> 'UsuariosController.php',

    '/pendientes/crear'     => 'PendienteController.php',
    '/pendientes/completar' => 'PendienteController.php',
    '/pendientes/eliminar'  => 'PendienteController.php',

    // Bitácora de fallos/mejoras del admin global (ver migración
    // 040_soportes.sql) — vive en el propio Inicio, sin nav_item aparte.
    '/soportes/crear'     => 'SoportesController.php',
    '/soportes/completar' => 'SoportesController.php',
    '/soportes/eliminar'  => 'SoportesController.php',

    // Módulos del Portal — todos los maneja PortalController.php
    '/cuadro-mando-integral'                        => 'PortalController.php',
    '/cuadro-mando-integral/finanzas'                => 'PortalController.php',
    '/cuadro-mando-integral/planeacion'              => 'PortalController.php',
    '/cuadro-mando-integral/vicerrectoria-academica' => 'PortalController.php',
    '/cuadro-mando-integral/investigacion'           => 'PortalController.php',
    '/mapa-portal'               => 'PortalController.php',
    '/gestion-institucional'     => 'PortalController.php',
    '/gestion-institucional/carpetas/crear'         => 'CarpetaController.php',
    '/gestion-institucional/carpetas/crear-documento'=> 'CarpetaController.php',
    '/gestion-institucional/carpetas/subir'         => 'CarpetaController.php',
    '/gestion-institucional/carpetas/importar-drive'=> 'CarpetaController.php',
    '/gestion-institucional/carpetas/descargar'     => 'CarpetaController.php',
    '/gestion-institucional/carpetas/eliminar'      => 'CarpetaController.php',
    '/sgi'                       => 'PortalController.php',
    '/sgi/carpetas/crear'          => 'CarpetaController.php',
    '/sgi/carpetas/crear-documento'=> 'CarpetaController.php',
    '/sgi/carpetas/subir'          => 'CarpetaController.php',
    '/sgi/carpetas/importar-drive' => 'CarpetaController.php',
    '/sgi/carpetas/descargar'      => 'CarpetaController.php',
    '/sgi/carpetas/eliminar'       => 'CarpetaController.php',
    '/vicerrectoria-academica'   => 'PortalController.php',
    '/vicerrectoria-academica/carpetas/crear'         => 'CarpetaController.php',
    '/vicerrectoria-academica/carpetas/crear-documento' => 'CarpetaController.php',
    '/vicerrectoria-academica/carpetas/subir'         => 'CarpetaController.php',
    '/vicerrectoria-academica/carpetas/importar-drive'=> 'CarpetaController.php',
    '/vicerrectoria-academica/carpetas/descargar'     => 'CarpetaController.php',
    '/vicerrectoria-academica/carpetas/eliminar'      => 'CarpetaController.php',
    '/administrativa-financiera' => 'PortalController.php',
    '/administrativa-financiera/carpetas/crear'      => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/crear-documento' => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/subir'      => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/importar-drive' => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/descargar'  => 'CarpetaController.php',
    '/administrativa-financiera/carpetas/eliminar'   => 'CarpetaController.php',
    '/talento-humano'            => 'PortalController.php',
    '/talento-humano/carpetas/crear'         => 'CarpetaController.php',
    '/talento-humano/carpetas/crear-documento' => 'CarpetaController.php',
    '/talento-humano/carpetas/subir'         => 'CarpetaController.php',
    '/talento-humano/carpetas/importar-drive'=> 'CarpetaController.php',
    '/talento-humano/carpetas/descargar'     => 'CarpetaController.php',
    '/talento-humano/carpetas/eliminar'      => 'CarpetaController.php',
    '/investigacion-innovacion'  => 'PortalController.php',
    '/investigacion-innovacion/carpetas/crear'         => 'CarpetaController.php',
    '/investigacion-innovacion/carpetas/crear-documento' => 'CarpetaController.php',
    '/investigacion-innovacion/carpetas/subir'         => 'CarpetaController.php',
    '/investigacion-innovacion/carpetas/importar-drive'=> 'CarpetaController.php',
    '/investigacion-innovacion/carpetas/descargar'     => 'CarpetaController.php',
    '/investigacion-innovacion/carpetas/eliminar'      => 'CarpetaController.php',
    '/gestion-documental'         => 'PortalController.php',

    // Drive personal de cada usuario en Gestión Documental (distinto del
    // "Traer de Drive" por link de arriba, que usa una cuenta de servicio
    // compartida) — ver DriveUsuarioController.php.
    '/gestion-documental/drive/conectar'    => 'DriveUsuarioController.php',
    '/gestion-documental/drive/callback'    => 'DriveUsuarioController.php',
    '/gestion-documental/drive/desconectar' => 'DriveUsuarioController.php',
    '/gestion-documental/drive/importar'    => 'DriveUsuarioController.php',
    '/gestion-documental/drive/importar-todo/avanzar' => 'DriveUsuarioController.php',
    '/gestion-documental/drive/mi-drive/descargar'    => 'DriveUsuarioController.php',

    '/documentos/crear'           => 'DocumentoController.php',
    '/documentos/subir'           => 'DocumentoController.php',
    '/documentos/descargar'       => 'DocumentoController.php',

    // Editor de Word/Excel/PowerPoint dentro del portal (ver
    // docker-compose.yml, servicio "onlyoffice", y .env.example)
    '/editor'          => 'EditorController.php',
    '/editor/archivo'  => 'EditorController.php',
    '/editor/callback' => 'EditorController.php',
    '/normatividad'              => 'PortalController.php',
    '/novedades'                 => 'PortalController.php',
    '/aplicaciones'              => 'PortalController.php',
    '/directorio'                => 'PortalController.php',
    '/calendario'                => 'PortalController.php',
    '/calendario/crear-evento'   => 'EventoController.php',
    '/calendario/editar-evento'  => 'EventoController.php',
    '/calendario/eliminar-evento'=> 'EventoController.php',
    '/calendario/exportar'       => 'EventoController.php',
    '/trello'                    => 'PortalController.php',

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