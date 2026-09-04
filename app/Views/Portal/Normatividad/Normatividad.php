<?php $titulo = 'Normatividad'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/normatividad.css">
<h1 style="font-size:40px;margin:0 0 8px">Normatividad</h1>
<p style="opacity:.65;max-width:640px;margin:0 0 26px">Acuerdos, resoluciones, reglamentos y normativa externa aplicable, con estado de vigencia y trazabilidad.</p>

<div class="norma-chips" id="normaChips"></div>

<table class="table">
  <thead><tr><th>Código</th><th>Título</th><th>Tipo</th><th>Expedición</th><th>Estado</th></tr></thead>
  <tbody id="normasBody"></tbody>
</table>

<script src="<?= BASE_URL ?>/assets/portal/js/normatividad.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
