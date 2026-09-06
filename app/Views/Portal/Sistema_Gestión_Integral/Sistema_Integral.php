<?php $titulo = 'Sistema de Gestión Integral'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/sgi.css">
<div class="modulo-head">
        <div class="modulo-kicker"><?= e($moduloKicker) ?></div>
        <h1 class="modulo-titulo"><?= e($moduloTitulo) ?></h1>
        <p class="modulo-desc"><?= e($moduloDesc) ?></p>
      </div>

      <div class="modulo-kpis" id="moduloKpis">
        <?php if (!$moduloKpis): ?>
          <p class="text-muted">Sin indicadores por ahora.</p>
        <?php else: foreach ($moduloKpis as $k): ?>
          <div class="modulo-kpi">
            <div class="label"><?= e($k['label']) ?></div>
            <div class="valor"><?= e($k['valor']) ?></div>
            <span class="spark" data-valor="<?= e($k['valor']) ?>"></span>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="modulo-grid">
        <div>
          <div class="section-head"><h4>Áreas del módulo</h4></div>
          <div id="moduloAreas">
            <?php if (!$moduloAreas): ?>
              <p class="text-muted">Sin áreas registradas por ahora.</p>
            <?php else: foreach ($moduloAreas as $a): ?>
              <div class="area-item">
                <span class="ic"><i class="bi bi-folder2"></i></span>
                <span style="flex:1">
                  <span class="label"><?= e($a['label']) ?></span>
                  <span class="meta"><?= e($a['meta']) ?></span>
                </span>
                <span class="arrow">→</span>
              </div>
            <?php endforeach; endif; ?>
          </div>

          <div class="section-head" style="margin-top:40px"><h4>Documentación destacada</h4></div>
          <table class="table">
            <thead><tr><th>Documento</th><th>Tipo</th><th>Ver.</th><th>Actualizado</th></tr></thead>
            <tbody id="moduloDocs"></tbody>
          </table>
        </div>

        <div style="display:flex;flex-direction:column;gap:28px">
          <div class="side-box">
            <div class="side-box-title">Responsables</div>
            <div id="moduloResponsables"></div>
          </div>
          <div class="side-box">
            <div class="side-box-title">Software relacionado</div>
            <div id="moduloSoftware"></div>
          </div>
        </div>
      </div>
<script src="<?= BASE_URL ?>/assets/portal/js/sgi.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
