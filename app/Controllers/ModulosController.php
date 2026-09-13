<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/ModulosController.php
//  "Todos los módulos" — página que reúne, agrupados, los enlaces que
//  antes estaban sueltos en el sidebar (Direcciones, Recursos, Cuadro de
//  Mando Integral, Mapa del portal). Ninguna ruta ni permiso cambia: cada
//  tarjeta lleva a la misma página de siempre, esto solo junta el menú.
//  Sin gate de admin — es pura navegación, cualquier usuario logueado ya
//  podía entrar a cada una de estas páginas por su cuenta.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

require ROOT_PATH . '/app/Views/Portal/Modulos/Modulos.php';
