<?php $titulo = 'Directorio'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/directorio.css') ?>">
<?php ui_page_header(['title' => 'Directorio', 'desc' => 'Cargos institucionales y quién los ocupa hoy. Abre cada uno para ver más.']); ?>

<?php if (!$directorioPorNivel): ?>
  <?php ui_empty_state(['icon' => 'person-badge', 'title' => 'Aún no hay cargos registrados', 'text' => 'Cuando se registren los cargos institucionales aparecerán acá.']); ?>
<?php else: foreach ($directorioPorNivel as $nivel => $cargos): ?>
  <div class="section-header section-header--spaced">
    <h2 class="section-heading"><?= e($nivel) ?></h2>
  </div>
  <div class="dir-lista">
    <?php foreach ($cargos as $c): ?>
      <details class="dir-card">
        <summary>
          <span class="dir-ini" aria-hidden="true"><?= e($c['ini']) ?></span>
          <span class="dir-texto">
            <span class="dir-nombre"><?= $c['nombre'] ? e($c['nombre']) : 'Vacante / sin asignar' ?></span>
            <span class="dir-cargo"><?= e($c['cargo']) ?></span>
          </span>
          <i class="bi bi-chevron-down dir-chevron" aria-hidden="true"></i>
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
