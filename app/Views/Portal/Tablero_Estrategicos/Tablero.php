<?php $titulo = 'Cuadro de Mando Integral'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/tableros.css') ?>">
<?php ui_page_header([
    'title' => 'Cuadro de Mando Integral',
    'desc'  => 'Indicadores institucionales, matrícula y ejecución presupuestal, con acceso a cada perspectiva.',
]); ?>

<div class="kpis tablero-kpis">
  <?php if (!$tableroKpis): ?>
    <div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></div><p>Sin indicadores por ahora.</p></div>
  <?php else: foreach ($tableroKpis as $k): ?>
    <div class="kpi">
      <div class="kpi-head"><div class="kpi-label"><?= e($k['label']) ?></div></div>
      <div class="kpi-value"><?= e($k['valor']) ?></div>
      <div class="kpi-meta">Meta <?= e($k['meta']) ?></div>
      <div class="progress-track" role="progressbar" aria-valuenow="<?= (int)$k['pct'] ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Avance de <?= e($k['label']) ?>"><div class="progress-fill" style="width:<?= (int)$k['pct'] ?>%"></div></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class="dashboard-grid">
  <div class="box-card">
    <div class="box-card-header">
      <h2 class="box-card-title">Matrícula por facultad · 2026-II</h2>
    </div>
    <div class="bar-chart" id="barras">
      <?php if (!$tableroMatricula): ?>
<div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-bar-chart" aria-hidden="true"></i></div><p>Sin datos de matrícula por ahora.</p></div>
      <?php else: foreach ($tableroMatricula as $b): ?>
        <div class="bar-col">
          <span class="bar-value"><?= number_format((int)$b['estudiantes'], 0, ',', '.') ?></span>
          <span class="bar-fill" style="--pct:<?= (int)$b['h'] ?>%"></span>
          <span class="bar-label"><?= e($b['facultad']) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div class="dashboard-col">
    <div class="box-card box-card--flat">
      <h2 class="box-card-title tablero-titulo">Ejecución presupuestal</h2>
      <div id="ejecucion">
        <?php if (!$tableroEjecucion): ?>
<div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-cash-coin" aria-hidden="true"></i></div><p>Sin datos de ejecución por ahora.</p></div>
        <?php else: foreach ($tableroEjecucion as $e): ?>
          <div class="exec-row">
            <div class="exec-head"><span><?= e($e['label']) ?></span><strong><?= (int)$e['pct'] ?>%</strong></div>
            <div class="progress-track" role="progressbar" aria-valuenow="<?= (int)$e['pct'] ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e($e['label']) ?>"><div class="progress-fill progress-fill-alt" style="width:<?= (int)$e['pct'] ?>%"></div></div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <div class="box-card box-card--flat">
      <h2 class="box-card-title tablero-titulo">Alertas de indicador</h2>
      <div id="alertasKpi">
        <?php if (!$tableroAlertas): ?>
<div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-check2-circle" aria-hidden="true"></i></div><p>Sin alertas por ahora.</p></div>
        <?php else: foreach ($tableroAlertas as $a): ?>
          <div class="alert-row">
            <span class="ic" aria-hidden="true"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
            <span class="texto"><?= e($a['texto']) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="section-header section-header--spaced"><h2 class="section-heading">Perspectivas</h2></div>
<div>
  <div class="tile-grid">
    <?php if (!$tableroSubmodulos): ?>
      <div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-grid" aria-hidden="true"></i></div><p>Sin perspectivas registradas por ahora.</p></div>
    <?php else: foreach ($tableroSubmodulos as $s): ?>
      <a class="tile tile-submodulo" href="<?= BASE_URL . e($s['ruta']) ?>">
        <i class="bi bi-<?= e($s['icono'] ?: 'app') ?>" aria-hidden="true"></i>
        <span class="label"><?= e($s['label']) ?></span>
      </a>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
