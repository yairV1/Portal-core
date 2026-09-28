<?php
$titulo = 'Sistema de Gestión Integral';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Configuración del centro documental compartido (ver _shared/_modulo_documental.php).
// 'titulo'/'desc' solo llenan el vacío si Contenido Landing/BD no trae uno.
$MD = [
    'ruta'            => '/sgi',
    'titulo'          => 'Sistema de Gestión Integral',
    'desc'            => 'Procedimientos, manuales de calidad y auditorías, organizados en un solo lugar.',
    'buscar'          => 'Buscar procedimientos, manuales, auditorías…',
    'vacioCategorias' => 'Crea la primera categoría (Procedimientos, Manuales de calidad, Auditorías…) para empezar a organizar los documentos del sistema.',
];
require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_documental.php';
require ROOT_PATH . '/app/Views/layouts/portal-footer.php';
