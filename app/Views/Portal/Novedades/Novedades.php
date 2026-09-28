<?php
$titulo = 'Novedades';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Página resumen compartida (ver _shared/_modulo_resumen.php).
$MR = ['volver' => '/novedades', 'areasTitulo' => 'Áreas del módulo'];
require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_resumen.php';
require ROOT_PATH . '/app/Views/layouts/portal-footer.php';
