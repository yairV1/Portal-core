<?php $titulo = 'Normatividad'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/normatividad.css') ?>">
<?php ui_page_header(['title' => 'Normatividad', 'desc' => 'Acuerdos, resoluciones, reglamentos y normativa externa aplicable, con su estado de vigencia.']); ?>

<div class="alert" role="status">
  <i class="bi bi-info-circle-fill alert-icon" aria-hidden="true"></i>
  <div class="alert-content"><p class="alert-text">Listado de referencia: todavía no se administra desde el portal, así que puede no estar al día.</p></div>
</div>

<div class="chips norma-chips" id="normaChips" role="group" aria-label="Filtrar por tipo"></div>

<div class="table-wrap">
  <table class="table table--stack" aria-live="polite">
  <thead><tr><th>Código</th><th>Título</th><th>Tipo</th><th>Expedición</th><th>Estado</th></tr></thead>
  <tbody id="normasBody"></tbody>
</table>
  </div>

<script src="<?= v('/assets/portal/js/normatividad.js') ?>"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
