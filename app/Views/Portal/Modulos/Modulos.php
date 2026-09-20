<?php $titulo = 'Todos los módulos'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<h1 class="page-title">Todos los módulos</h1>
<p class="page-desc">Todo el Portal, agrupado en un solo lugar — cada tarjeta te lleva a su propia página.</p>

<?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
  <div class="section-head"><h4>Administración</h4></div>
  <div class="doc-categorias">
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/usuarios" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-people-fill"></i></span>
        <span class="nombre">Usuarios y roles</span>
      </a>
    </article>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/contenido-landing" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-window-stack"></i></span>
        <span class="nombre">Contenido landing</span>
      </a>
    </article>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/contrataciones" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-file-earmark-person"></i></span>
        <span class="nombre">Contrataciones</span>
      </a>
    </article>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/postulaciones" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-person-lines-fill"></i></span>
        <span class="nombre">Postulaciones</span>
      </a>
    </article>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/permisos-por-rol" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-shield-lock"></i></span>
        <span class="nombre">Permisos por rol</span>
      </a>
    </article>
  </div>
<?php endif; ?>

<?php
// Direcciones, Recursos y Analítica salen de config/modulos.php (la misma
// lista que usa el panel "Permisos por rol"): un módulo vetado para el rol de
// esta cuenta no se ofrece acá (usuario_puede_ver_modulo(), public/index.php).
// Además, si esta cuenta tiene un área de trabajo asignada (ver
// usuario_area_asignada() en public/index.php), tacha del listado las
// direcciones que no sean la suya — misma regla que ya bloquea la URL
// directa en PortalController.php, para que el menú nunca ofrezca un
// enlace que de todos modos va a dar 403. (En Direcciones la clave del
// módulo es el mismo slug de la tabla direcciones.)
$seccionesModulos = ['Direcciones' => [], 'Recursos' => [], 'Analítica' => []];
foreach (modulos_config() as $clave => $m) {
    if (isset($seccionesModulos[$m['seccion']]) && usuario_puede_ver_modulo($clave)) {
        $seccionesModulos[$m['seccion']][$clave] = $m;
    }
}
?>
<?php foreach ($seccionesModulos as $nombreSeccion => $modulosSeccion): ?>
  <?php
  if ($nombreSeccion === 'Direcciones' && $areaAsignada !== null) {
      $modulosSeccion = array_filter($modulosSeccion, fn ($clave) => ($direccionIdPorSlug[$clave] ?? null) === $areaAsignada, ARRAY_FILTER_USE_KEY);
  }
  if (!$modulosSeccion) continue;
  ?>
<div class="section-head"<?= $nombreSeccion === 'Direcciones' ? '' : ' style="margin-top:32px"' ?>><h4><?= e($nombreSeccion) ?></h4></div>
<div class="doc-categorias">
  <?php foreach ($modulosSeccion as $clave => $m): ?>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL . $m['rutas'][0] ?>" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-<?= e($m['icono']) ?>"></i></span>
        <span class="nombre"><?= e($m['label']) ?></span>
      </a>
    </article>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
