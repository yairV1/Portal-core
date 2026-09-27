<?php $titulo = 'Normatividad'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/normatividad.css">
<h1 class="page-title">Normatividad</h1>
<p class="page-desc">Acuerdos, resoluciones, reglamentos y normativa externa aplicable, con estado de vigencia y trazabilidad.</p>

<div class="chips norma-chips" id="normaChips" role="group" aria-label="Filtrar por tipo"></div>

<div class="table-wrap">
  <table class="table table--stack">
  <thead><tr><th>Código</th><th>Título</th><th>Tipo</th><th>Expedición</th><th>Estado</th></tr></thead>
  <tbody id="normasBody"></tbody>
</table>
  </div>

<script src="<?= BASE_URL ?>/assets/portal/js/normatividad.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
