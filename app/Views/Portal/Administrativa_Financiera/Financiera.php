<?php
$titulo = 'Administrativa y Financiera';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Configuración del centro documental compartido (ver _shared/_modulo_documental.php).
// 'titulo'/'desc' solo llenan el vacío si Contenido Landing/BD no trae uno.
$MD = [
    'ruta'            => '/administrativa-financiera',
    'titulo'          => 'Administración y Finanzas',
    'desc'            => 'Gestiona y organiza de forma centralizada la documentación administrativa y financiera de tu institución.',
    'buscar'          => 'Buscar documentos…',
    'vacioCategorias' => 'Crea la primera categoría de ' . ($areaActiva === 'finanzas' ? 'Finanzas' : 'Administración') . ' para empezar a organizar tus documentos.',
    'areas'           => ['administracion' => ['Administración', 'building'], 'finanzas' => ['Finanzas', 'cash-coin']],
];
require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_documental.php';
require ROOT_PATH . '/app/Views/layouts/portal-footer.php';
