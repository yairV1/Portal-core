<?php $titulo = 'Aplicaciones'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/aplicaciones.css') ?>">
<?php ui_page_header(['title' => 'Centro de aplicaciones', 'desc' => 'Los sistemas institucionales en un solo lugar. Marca con la estrella los que más uses.']); ?>

<div class="alert" role="status">
  <i class="bi bi-info-circle-fill alert-icon" aria-hidden="true"></i>
  <div class="alert-content">
    <p class="alert-title">Accesos en preparación</p>
    <p class="alert-text">Todavía no se abren desde el portal: por ahora es el catálogo de sistemas que se van a conectar con inicio de sesión único.</p>
  </div>
</div>

<div class="apps-grid" id="appsGrid"></div>
<script src="<?= v('/assets/portal/js/aplicaciones.js') ?>"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
