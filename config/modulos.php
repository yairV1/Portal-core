<?php
// ══════════════════════════════════════════════════════════
//  config/modulos.php — módulos del Portal que el admin global puede
//  vetarle a admin_direccion/usuario desde "Permisos por rol".
//
//  UNA sola lista, en código (no en nav_items: la migración 039 los borró y
//  no deben volver a depender de esa tabla). La usan:
//   - usuario_puede_ver_ruta()/usuario_puede_ver_modulo() en public/index.php
//   - PermisosController.php + Permisos/Permisos.php (el checklist)
//   - layouts/sidebar.php y Modulos/Modulos.php (qué se le muestra a cada rol)
//   - H4 (descargas/editor): 'slugs_direccion' dice a qué módulo pertenece un
//     archivo según direcciones.slug de la fila a la que cuelga.
//
//  Cada módulo:
//   'label'   → nombre en el panel y en "Todos los módulos"
//   'icono'   → Bootstrap Icons (sin el prefijo "bi-")
//   'seccion' → agrupación en el panel y en "Todos los módulos"
//   'rutas'   → prefijos de URL que abarca (la ruta exacta o cualquier
//               subruta: '/calendario' cubre '/calendario/crear-evento')
//   'slugs_direccion' (opcional) → direcciones.slug cuyos documentos/archivos
//               pertenecen a este módulo
//
//  NO están acá, a propósito, y por eso NO se pueden vetar: '/', '/perfil',
//  '/administracion', '/usuarios', '/permisos-por-rol', '/contenido-landing',
//  '/contrataciones', '/postulaciones', '/pendientes', '/soportes' (todas de
//  admin o propias de cada cuenta) ni '/documentos' y '/editor' (no son un
//  módulo: pertenecen al de la dirección del archivo, ver H4). El admin
//  global tampoco se veta nunca: usuario_puede_ver_ruta() lo deja pasar antes
//  de mirar esta lista.
//
//  Agregar un módulo nuevo = una entrada acá (y su ruta en routes/web.php).
// ══════════════════════════════════════════════════════════

return [
    // ── Direcciones ──
    'institucional'  => ['label' => 'Gestión Institucional',        'icono' => 'bank',         'seccion' => 'Direcciones', 'rutas' => ['/gestion-institucional'],     'slugs_direccion' => ['institucional']],
    'sgi'            => ['label' => 'Sistema de Gestión Integral',  'icono' => 'folder2-open', 'seccion' => 'Direcciones', 'rutas' => ['/sgi'],                       'slugs_direccion' => ['sgi']],
    'academica'      => ['label' => 'Vicerrectoría Académica',      'icono' => 'mortarboard',  'seccion' => 'Direcciones', 'rutas' => ['/vicerrectoria-academica'],   'slugs_direccion' => ['academica']],
    'financiera'     => ['label' => 'Administrativa y Financiera',  'icono' => 'cash-coin',    'seccion' => 'Direcciones', 'rutas' => ['/administrativa-financiera'], 'slugs_direccion' => ['financiera']],
    'talento-humano' => ['label' => 'Talento Humano',               'icono' => 'people',       'seccion' => 'Direcciones', 'rutas' => ['/talento-humano'],            'slugs_direccion' => ['talento-humano']],
    'investigacion'  => ['label' => 'Investigación e Innovación',   'icono' => 'stars',        'seccion' => 'Direcciones', 'rutas' => ['/investigacion-innovacion'],  'slugs_direccion' => ['investigacion']],

    // ── Recursos ──
    // Gestión Documental no tiene dirección propia (archivos_documentales no
    // trae direccion_id), así que no lleva 'slugs_direccion'.
    'gestion-documental' => ['label' => 'Gestión Documental', 'icono' => 'folder2',            'seccion' => 'Recursos', 'rutas' => ['/gestion-documental']],
    'normatividad'       => ['label' => 'Normatividad',       'icono' => 'file-earmark-text',  'seccion' => 'Recursos', 'rutas' => ['/normatividad']],
    'novedades'          => ['label' => 'Novedades',          'icono' => 'newspaper',          'seccion' => 'Recursos', 'rutas' => ['/novedades'],   'slugs_direccion' => ['novedades']],
    'aplicaciones'       => ['label' => 'Aplicaciones',       'icono' => 'grid',               'seccion' => 'Recursos', 'rutas' => ['/aplicaciones']],
    'directorio'         => ['label' => 'Directorio',         'icono' => 'person-badge',       'seccion' => 'Recursos', 'rutas' => ['/directorio']],
    'calendario'         => ['label' => 'Calendario',         'icono' => 'calendar3',          'seccion' => 'Recursos', 'rutas' => ['/calendario']],
    'trello'             => ['label' => 'Trello',             'icono' => 'kanban',             'seccion' => 'Recursos', 'rutas' => ['/trello']],

    // ── Analítica ──
    // Las 4 perspectivas del CMI (cmi-finanzas, cmi-planeacion, …) son un solo
    // módulo: '/cuadro-mando-integral' ya cubre sus subrutas.
    'cuadro-mando-integral' => ['label' => 'Cuadro de Mando Integral', 'icono' => 'bar-chart', 'seccion' => 'Analítica', 'rutas' => ['/cuadro-mando-integral'],
        'slugs_direccion' => ['cmi-finanzas', 'cmi-planeacion', 'cmi-vicerrectoria-academica', 'cmi-investigacion']],
    'mapa-portal'           => ['label' => 'Mapa del portal',          'icono' => 'map',       'seccion' => 'Analítica', 'rutas' => ['/mapa-portal']],

    // ── Navegación ──
    // Ya se podía vetar con el panel anterior (era el nav_item "Todos los
    // módulos"); se conserva para no quitar una opción existente.
    'modulos' => ['label' => 'Todos los módulos', 'icono' => 'grid-3x3-gap', 'seccion' => 'Navegación', 'rutas' => ['/modulos']],
];
