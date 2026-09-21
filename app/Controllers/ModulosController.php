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
// Los 4 submódulos nuevos de Talento Humano (Hojas de vida/Contratos/
// Certificaciones laborales/Contrataciones, ver config/modulos.php,
// migraciones 048/049) rompen la regla "clave de módulo = slug de
// dirección" que Modulos.php asume para tachar del listado lo que no es
// de tu área — sin este mapeo quedaban ocultos para cualquier cuenta con
// área asignada a Talento Humano, aunque sí pudiera entrar a esas 4
// páginas (bug real encontrado 2026-09-21). Se mapean a mano al mismo id
// que 'talento-humano', sin tocar slugs_direccion (esa lista sí es
// sensible: la usan CarpetaController.php/DocumentoController.php para
// H4 y ahí SÍ debe haber una sola dirección dueña de cada módulo).
if (isset($direccionIdPorSlug['talento-humano'])) {
    foreach (['talento-humano-hojas-de-vida', 'talento-humano-contratos', 'talento-humano-certificaciones-laborales', 'contrataciones'] as $claveHija) {
        $direccionIdPorSlug[$claveHija] = $direccionIdPorSlug['talento-humano'];
    }
}
$areaAsignada = usuario_area_asignada();

require ROOT_PATH . '/app/Views/Portal/Modulos/Modulos.php';
