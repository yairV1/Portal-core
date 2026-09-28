<?php
$titulo = 'Vicerrectoría Académica';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Configuración del centro documental compartido (ver _shared/_modulo_documental.php).
// 'titulo'/'desc' solo llenan el vacío si Contenido Landing/BD no trae uno.
$MD = [
    'ruta'            => '/vicerrectoria-academica',
    'titulo'          => 'Vicerrectoría Académica',
    'desc'            => 'Programas académicos, reglamentos y documentos curriculares, organizados en un solo lugar.',
    'buscar'          => 'Buscar programas, reglamentos, currículos…',
    'vacioCategorias' => 'Crea la primera categoría (Programas académicos, Reglamentos, Currículos…) para empezar a organizar los documentos académicos.',
];
require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_documental.php';
require ROOT_PATH . '/app/Views/layouts/portal-footer.php';
