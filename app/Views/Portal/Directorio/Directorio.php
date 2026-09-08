<?php $titulo = 'Directorio'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/directorio.css">
<h1 class="page-title">Directorio</h1>
<p class="page-desc">Cargos institucionales y quién los ocupa hoy — clic en cada uno para ver más.</p>

<?php if (!$directorioPorNivel): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-cone-striped"></i></div>
    <h4>Módulo en construcción</h4>
    <p>Este módulo todavía no tiene contenido cargado. Vuelve pronto.</p>
  </div>
<?php else: foreach ($directorioPorNivel as $nivel => $cargos): ?>
  <div class="section-head" style="margin-top:28px">
    <h4><?= e($nivel) ?></h4>
  </div>
  <div class="dir-lista">
    <?php foreach ($cargos as $c): ?>
      <details class="dir-card">
        <summary>
          <span class="dir-ini"><?= e($c['ini']) ?></span>
          <span style="flex:1">
            <span class="dir-nombre"><?= $c['nombre'] ? e($c['nombre']) : 'Vacante / sin asignar' ?></span>
            <span class="dir-cargo"><?= e($c['cargo']) ?></span>
          </span>
          <i class="bi bi-chevron-down dir-chevron"></i>
        </summary>
        <div class="dir-detalle">
          <div><span class="k">Dirección</span><span class="v"><?= e($c['direccion']) ?></span></div>
          <div><span class="k">Nivel</span><span class="v"><?= e($c['nivel']) ?></span></div>
          <div><span class="k">Código</span><span class="v text-accent">MF-<?= e($c['codigo']) ?></span></div>
        </div>
      </details>
    <?php endforeach; ?>
  </div>
<?php endforeach; endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
