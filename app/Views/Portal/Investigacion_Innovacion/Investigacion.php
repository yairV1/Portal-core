<?php
$titulo = 'Investigación e Innovación';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Configuración del centro documental compartido (ver _shared/_modulo_documental.php).
// 'titulo'/'desc' solo llenan el vacío si Contenido Landing/BD no trae uno.
$MD = [
    'ruta'            => '/investigacion-innovacion',
    'titulo'          => 'Investigación e Innovación',
    'desc'            => 'Proyectos, publicaciones y convocatorias de investigación, organizados en un solo lugar.',
    'buscar'          => 'Buscar proyectos, publicaciones, convocatorias…',
    'vacioCategorias' => 'Crea la primera categoría (Proyectos de investigación, Publicaciones, Convocatorias…) para empezar a organizar los documentos.',
];
require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_documental.php';
require ROOT_PATH . '/app/Views/layouts/portal-footer.php';
