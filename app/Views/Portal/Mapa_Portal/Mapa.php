<?php $titulo = 'Mapa del portal'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/mapa-portal.css">
<h1 style="font-size:40px;margin:0 0 8px">Arquitectura de información</h1>
      <p style="opacity:.65;max-width:700px;margin:0 0 34px">El Portal CORE replica la estructura organizacional de COREDUCACIÓN: cada Dirección es un módulo, cada área un espacio de trabajo con su documentación, indicadores y responsables. Máximo tres clics hasta cualquier contenido.</p>

      <div class="sitemap-grid" id="sitemapGrid"></div>

      <div style="margin-top:48px;border-top:2px solid var(--color-divider);padding-top:26px">
        <h4 style="margin:0 0 16px">Contenido tipo de cada área</h4>
        <div style="display:flex;flex-wrap:wrap;gap:8px" id="contenidoTipo"></div>
      </div>
<script src="<?= BASE_URL ?>/assets/portal/js/mapa-portal.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
