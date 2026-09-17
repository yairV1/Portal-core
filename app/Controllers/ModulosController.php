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

// Direcciones reales por slug (ver PortalController.php/usuario_area_asignada()
// en public/index.php) — para tachar del listado las que no correspondan a
// quien tiene un área de trabajo asignada. Sin fila todavía (institucional/
// sgi/academica/investigacion) queda sin id y la tarjeta se sigue mostrando
// a todos, porque nadie puede estar asignado a una dirección que no existe.
$direccionIdPorSlug = [];
foreach ($pdo->query("SELECT id, slug FROM direcciones")->fetchAll() as $d) {
    $direccionIdPorSlug[$d['slug']] = (int) $d['id'];
}
$areaAsignada = usuario_area_asignada();

require ROOT_PATH . '/app/Views/Portal/Modulos/Modulos.php';
