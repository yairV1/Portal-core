<?php $titulo = 'Mapa del portal'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/mapa-portal.css') ?>">
<?php ui_page_header([
    'title' => 'Mapa del portal',
    'desc'  => 'El Portal CORE replica la estructura organizacional de COREDUCACIÓN: cada Dirección es un módulo y cada área un espacio de trabajo con su documentación, indicadores y responsables.',
]); ?>

      <div class="sitemap-grid" id="sitemapGrid">
        <?php if (!$sitemapModulos): ?>
<div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-diagram-3" aria-hidden="true"></i></div><p>Sin módulos registrados por ahora.</p></div>
        <?php else: foreach ($sitemapModulos as $m): ?>
          <div class="sitemap-card">
            <div class="sitemap-kicker">
              <span aria-hidden="true"><i class="bi bi-<?= e($m['icono'] ?: 'app') ?>" aria-hidden="true"></i></span>
              <span class="sitemap-nivel"><?= e($m['nivel']) ?></span>
            </div>
            <?php if ($m['ruta']): ?>
              <a class="sitemap-titulo" href="<?= BASE_URL . e($m['ruta']) ?>"><?= e($m['label']) ?></a>
            <?php else: ?>
              <div class="sitemap-titulo"><?= e($m['label']) ?></div>
            <?php endif; ?>
            <?php if ($m['descripcion']): ?>
              <p class="sitemap-desc"><?= e($m['descripcion']) ?></p>
            <?php endif; ?>
            <div class="sitemap-rule"></div>
            <?php foreach ($m['hijos'] as $h): ?>
              <div class="sitemap-hijo"><?= e($h) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="section-header section-header--spaced"><h2 class="section-heading">Contenido tipo de cada área</h2></div>
      <div class="chip-row" id="contenidoTipo"></div>
<script src="<?= v('/assets/portal/js/mapa-portal.js') ?>"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
