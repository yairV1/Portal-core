<?php
$titulo = 'Gestión Institucional';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Configuración del centro documental compartido (ver _shared/_modulo_documental.php).
// 'titulo'/'desc' solo llenan el vacío si Contenido Landing/BD no trae uno.
$MD = [
    'ruta'            => '/gestion-institucional',
    'titulo'          => 'Gestión Institucional',
    'desc'            => 'Actas, resoluciones y documentos de gobierno institucional, organizados en un solo lugar.',
    'buscar'          => 'Buscar actas, resoluciones, manuales…',
    'vacioCategorias' => 'Crea la primera categoría (Actas de Consejo, Resoluciones, Manuales…) para empezar a organizar los documentos institucionales.',
];
require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_documental.php';
require ROOT_PATH . '/app/Views/layouts/portal-footer.php';
