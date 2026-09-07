<?php $titulo = 'Mapa del portal'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/mapa-portal.css">
<h1 class="page-title">Arquitectura de información</h1>
      <p class="page-desc" style="max-width:700px">El Portal CORE replica la estructura organizacional de COREDUCACIÓN: cada Dirección es un módulo, cada área un espacio de trabajo con su documentación, indicadores y responsables. Máximo tres clics hasta cualquier contenido.</p>

      <div class="sitemap-grid" id="sitemapGrid">
        <?php if (!$sitemapModulos): ?>
          <p class="text-muted">Sin módulos registrados por ahora.</p>
        <?php else: foreach ($sitemapModulos as $m): ?>
          <div class="sitemap-card">
            <div class="sitemap-kicker">
              <span><i class="bi bi-<?= e($m['icono'] ?: 'app') ?>"></i></span>
              <span class="sitemap-nivel"><?= e($m['nivel']) ?></span>
            </div>
            <div class="sitemap-titulo"><?= e($m['label']) ?></div>
            <div class="sitemap-rule"></div>
            <?php foreach ($m['hijos'] as $h): ?>
              <div class="sitemap-hijo"><?= e($h) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="subsection">
        <h4 class="section-title">Contenido tipo de cada área</h4>
        <div class="chip-row" id="contenidoTipo"></div>
      </div>
<script src="<?= BASE_URL ?>/assets/portal/js/mapa-portal.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
