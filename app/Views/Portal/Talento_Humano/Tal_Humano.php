<?php $titulo = 'Talento Humano'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/talento-humano.css">
<h1 class="page-title">Talento Humano</h1>
      <p class="page-desc">Gestión Humana y Desarrollo Organizacional. Estructura, funciones, desempeño y bienestar en un solo espacio.</p>

      <div id="thTabs"></div>
      <div id="thContenido"></div>
<script>
  // Organigrama/cargos/competencias reales (ver PortalController.php y
  // database/migrations/006_talento_humano.sql) — se resuelven acá porque
  // esta vista cambia de pestaña sin recargar la página.
  const ORGANIGRAMA_DB  = <?= json_encode($thOrganigrama, JSON_UNESCAPED_UNICODE) ?>;
  const CARGOS_DB        = <?= json_encode($thCargos, JSON_UNESCAPED_UNICODE) ?>;
  const COMPETENCIAS_DB = <?= json_encode($thCompetencias, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= BASE_URL ?>/assets/portal/js/talento-humano.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
