<?php $titulo = 'Tableros Estratégicos'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/tableros.css">
<h1 class="page-title">Tableros Estratégicos</h1>
<p class="page-desc">Indicadores institucionales en tiempo real. Vista Rectoría, con descenso a dirección y área.</p>

<div class="pill-tabs" style="margin-bottom:28px" id="tableroTabs"></div>

<div class="kpis" id="kpisTablero" style="margin-bottom:36px">
  <?php if (!$tableroKpis): ?>
    <p class="text-muted">Sin indicadores por ahora.</p>
  <?php else: foreach ($tableroKpis as $k): ?>
    <div class="kpi">
      <div class="kpi-label"><?= e($k['label']) ?></div>
      <div class="kpi-value"><?= e($k['valor']) ?></div>
      <div class="kpi-meta">Meta <?= e($k['meta']) ?></div>
      <div class="progress-track"><div class="progress-fill" style="width:<?= (int)$k['pct'] ?>%"></div></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class="dashboard-grid">
  <div class="box-card">
    <div class="chart-card-head">
      <h4>Matrícula por facultad · 2026-II</h4>
      <span class="badge-live">Power BI · en vivo</span>
    </div>
    <div class="bar-chart" id="barras">
      <?php if (!$tableroMatricula): ?>
        <p class="text-muted">Sin datos de matrícula por ahora.</p>
      <?php else: foreach ($tableroMatricula as $b): ?>
        <div class="bar-col">
          <span class="bar-value"><?= number_format((int)$b['estudiantes'], 0, ',', '.') ?></span>
          <span class="bar-fill" style="height:<?= (int)$b['h'] ?>%"></span>
          <span class="bar-label"><?= e($b['facultad']) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div class="dashboard-col">
    <div class="box-card box-card--flat">
      <h4 class="section-title">Ejecución presupuestal</h4>
      <div id="ejecucion">
        <?php if (!$tableroEjecucion): ?>
          <p class="text-muted">Sin datos de ejecución por ahora.</p>
        <?php else: foreach ($tableroEjecucion as $e): ?>
          <div class="exec-row">
            <div class="exec-head"><span><?= e($e['label']) ?></span><strong><?= (int)$e['pct'] ?>%</strong></div>
            <div class="progress-track"><div class="progress-fill progress-fill-alt" style="width:<?= (int)$e['pct'] ?>%"></div></div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <div class="box-card box-card--flat">
      <h4 class="section-title">Alertas de indicador</h4>
      <div id="alertasKpi">
        <?php if (!$tableroAlertas): ?>
          <p class="text-muted">Sin alertas por ahora.</p>
        <?php else: foreach ($tableroAlertas as $a): ?>
          <div class="alert-row">
            <span class="ic"><i class="bi bi-exclamation-triangle"></i></span>
            <span class="texto"><?= e($a['texto']) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/portal/js/tableros.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
