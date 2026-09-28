<?php
// Una perspectiva del Cuadro de Mando Integral ($titulo lo define
// PortalController.php según la ruta). Página resumen compartida (ver
// _shared/_modulo_resumen.php).
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
$MR = [
    'volver'      => $uri,
    'areasTitulo' => 'Áreas de esta perspectiva',
    'back'        => ['href' => BASE_URL . '/cuadro-mando-integral', 'label' => 'Volver al Cuadro de Mando'],
];
require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_resumen.php';
require ROOT_PATH . '/app/Views/layouts/portal-footer.php';
