<?php $titulo = 'Aplicaciones'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/aplicaciones.css">
<h1 style="font-size:40px;margin:0 0 8px">Centro de aplicaciones</h1>
      <p style="opacity:.65;max-width:640px;margin:0 0 30px">Acceso único a todos los sistemas institucionales. Inicio de sesión federado.</p>

      <div class="apps-grid" id="appsGrid"></div>
<script src="<?= BASE_URL ?>/assets/portal/js/aplicaciones.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
