<?php
/** Variables que llegan ya resueltas desde HomeController.php (vía
 * require, mismo scope) — el análisis estático no rastrea esa ruta
 * dinámica, así que se declaran acá solo para que no marque falso
 * positivo de "undefined variable"; no cambia nada en runtime.
 * @var string $nombre
 * @var array  $pendientes
 * @var int    $docsEnRevision
 * @var string $pdiValor
 * @var string $pdiSufijo
 * @var string $pdiDesc
 * @var array  $kpis
 * @var array  $accesos
 * @var array  $docsRecientes
 * @var array  $noticias
 * @var array  $eventos
 * @var array  $cumpleanos
 * @var bool   $miDriveOauthConfigurado
 * @var bool   $miDriveConectado
 * @var array  $widgetsOcultos claves de widgets que este rol no debe ver
 *             (hoy siempre vacío — ver HomeController.php); cada bloque de
 *             abajo ya chequea contra esta lista para poder ocultarse por
 *             rol más adelante sin tocar esta vista.
 */
$titulo = 'Inicio'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/inicio.css') ?>">

<?php $COLOR_ESTADO = ['success' => 'var(--success)', 'warning' => 'var(--warning)', 'danger' => 'var(--danger)', 'info' => 'var(--info)', 'accent' => 'var(--primary)']; ?>

<div class="inicio-page">

<div class="hero">
  <svg class="hero-rio" width="100%" height="100%" viewBox="0 0 900 420" preserveAspectRatio="none">
    <path d="M -20 340 C 140 300, 220 380, 360 320 S 560 250, 700 300 S 880 260, 940 300" fill="none" stroke="#ffffff" stroke-width="2.5"/>
    <path d="M -20 380 C 160 350, 260 410, 400 360 S 600 300, 760 340 S 900 310, 950 340" fill="none" stroke="#ffffff" stroke-width="1.5" opacity=".6"/>
  </svg>

  <div class="hero-main">
    <div class="kicker" id="saludoKicker">Buenos días</div>
    <h1>La institución está en marcha.</h1>
    <p>
      <?= e($nombre) ?>, tienes <?= count($pendientes) ?> pendiente<?= count($pendientes) === 1 ? '' : 's' ?>
      <?php if ($docsEnRevision > 0): ?>
        y <?= $docsEnRevision ?> documento<?= $docsEnRevision === 1 ? '' : 's' ?> en revisión.
      <?php else: ?>
        y ningún documento en revisión.
      <?php endif; ?>
    </p>
  </div>

  <?php if ($pdiValor !== ''): ?>
  <div class="hero-pdi">
    <div class="hero-pdi-label">Avance PDI 2026</div>
    <div class="hero-pdi-valor"><?= e($pdiValor) ?><span><?= e($pdiSufijo) ?></span></div>
    <?php if ($pdiDesc): ?><div class="hero-pdi-desc"><?= e($pdiDesc) ?></div><?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php if (!in_array('kpis', $widgetsOcultos, true)): ?>
<div class="kpis" id="kpis">
  <?php if (!$kpis): ?>
    <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-bar-chart" aria-hidden="true"></i></div><p>Sin indicadores por ahora.</p></div>
  <?php else: foreach ($kpis as $i => $k): ?>
    <div class="kpi">
      <div class="kpi-head">
        <?php if ($k['icono']): ?><i class="bi bi-<?= e($k['icono']) ?> kpi-ic" aria-hidden="true"></i><?php endif; ?>
        <div class="kpi-label"><?= e($k['label']) ?></div>
      </div>
      <div class="kpi-value"><?= e($k['valor']) ?></div>
      <div class="kpi-foot">
        <span class="badge badge-<?= e($k['estado']) ?>"><?= e($k['delta']) ?></span>
        <?php if ($k['tendencia']): ?>
          <span class="kpi-spark" data-tendencia="<?= e(implode(',', $k['tendencia'])) ?>" data-color="<?= e($COLOR_ESTADO[$k['estado']] ?? $COLOR_ESTADO['accent']) ?>"></span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php endif; ?>

<div class="grid-main">
  <div>
    <?php if (!in_array('accesos', $widgetsOcultos, true)): ?>
    <div class="section-header">
      <h2 class="section-heading"><i class="bi bi-grid" aria-hidden="true"></i> Accesos rápidos</h2>
      <?php if (usuario_puede_ver_ruta('/aplicaciones')): ?><a class="btn btn-link btn-sm" href="<?= BASE_URL ?>/aplicaciones">Centro de aplicaciones <i class="bi bi-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
    </div>
    <div class="accesos" id="accesos">
      <?php if (!$accesos): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-grid" aria-hidden="true"></i></div><p>Sin accesos configurados por ahora.</p></div>
      <?php else: foreach ($accesos as $i => $a): ?>
        <a class="acceso<?= $a['sugerido'] ? ' acceso-sugerido' : '' ?>"
           <?= $a['href'] !== '' ? 'href="' . e($a['href']) . '"' : 'aria-disabled="true"' ?>
           <?= $a['externo'] ? 'target="_blank" rel="noopener"' : '' ?>>
          <?php if ($a['sugerido']): ?><span class="acceso-badge">Core sugiere</span><?php endif; ?>
          <span class="ic"><i class="bi bi-<?= e($a['icono']) ?>" aria-hidden="true"></i></span>
          <span class="label"><?= e($a['label']) ?></span>
          <span class="meta"><?= e($a['meta']) ?></span>
        </a>
      <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!in_array('documentos', $widgetsOcultos, true)): ?>
    <div class="section-header section-header--spaced">
      <h2 class="section-heading"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Documentos recientes</h2>
      <span class="section-desc">Repositorio institucional</span>
    </div>
    <div class="table-wrap">
  <table class="table table--stack">
      <thead><tr><th>Documento</th><th>Área</th><th>Ver.</th><th>Actualizado</th></tr></thead>
      <tbody id="docsRecientes">
        <?php if (!$docsRecientes): ?>
          <tr><td colspan="4"><div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></div><p>No hay documentos recientes.</p></div></td></tr>
        <?php else: foreach ($docsRecientes as $d): ?>
          <tr>
            <td><strong><?= e($d['nombre']) ?></strong></td>
            <td class="cell-muted"><?= e($d['area']) ?></td>
            <td>
              <span class="document-status">
                <span class="tag-stamp"><?= e($d['version']) ?></span>
                <span class="badge badge-<?= e($d['estadoTag']) ?>"><?= e($d['estado']) ?></span>
              </span>
            </td>
            <td class="cell-muted"><?= e($d['fecha']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
    <?php endif; ?>

    <?php if (!in_array('novedades', $widgetsOcultos, true)): ?>
    <div class="section-header section-header--spaced">
      <h2 class="section-heading"><i class="bi bi-newspaper" aria-hidden="true"></i> Novedades institucionales</h2>
    </div>
    <div class="noticias" id="noticias">
      <?php if (!$noticias): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-newspaper" aria-hidden="true"></i></div><p>Sin novedades por ahora.</p></div>
      <?php else: foreach ($noticias as $n): ?>
        <div class="card">
          <div class="noticia-foto" aria-hidden="true"><i class="bi bi-newspaper" aria-hidden="true"></i></div>
          <div class="noticia-body">
            <div class="noticia-cat"><?= e($n['categoria']) ?></div>
            <div class="noticia-titulo"><?= e($n['titulo']) ?></div>
            <div class="noticia-fecha"><?= e($n['fecha']) ?></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="side-col">
    <?php if (!in_array('pendientes', $widgetsOcultos, true)): ?>
    <?php $listaPendientes = $pendientes; require ROOT_PATH . '/app/Views/Portal/_shared/_widget_pendientes.php'; ?>
    <?php endif; ?>

    <?php if (!in_array('agenda', $widgetsOcultos, true)): ?>
    <div class="section-header section-header--spaced"><h2 class="section-heading"><i class="bi bi-calendar-week" aria-hidden="true"></i> Agenda de la semana</h2></div>
    <div id="eventos">
      <?php if (!$eventos): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-calendar-week" aria-hidden="true"></i></div><p>Sin eventos esta semana.</p></div>
      <?php else: foreach ($eventos as $ev): ?>
        <div class="evento">
          <span class="fecha">
            <span class="dia"><?= e($ev['dia']) ?></span>
            <span class="mes"><?= e($ev['mes']) ?></span>
          </span>
          <span class="item-copy">
            <span class="t"><?= e($ev['titulo']) ?></span>
            <span class="h"><?= e($ev['hora']) ?></span>
          </span>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!in_array('cumpleanos', $widgetsOcultos, true)): ?>
    <div class="section-header section-header--spaced"><h2 class="section-heading"><i class="bi bi-gift" aria-hidden="true"></i> Cumpleaños</h2></div>
    <div id="cumpleanos">
      <?php if (!$cumpleanos): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-gift" aria-hidden="true"></i></div><p>Sin cumpleaños esta semana.</p></div>
      <?php else: foreach ($cumpleanos as $c): ?>
        <div class="cumple">
          <span class="ini"><?= e($c['ini']) ?></span>
          <span class="n"><?= e($c['nombre']) ?></span>
          <span class="f"><?= e($c['fecha']) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!in_array('drive_personal', $widgetsOcultos, true)): ?>
    <div class="section-header section-header--spaced"><h2 class="section-heading"><i class="bi bi-google" aria-hidden="true"></i> Mi Google Drive</h2></div>
    <?php require ROOT_PATH . '/app/Views/Portal/_shared/_widget_drive.php'; ?>
    <?php endif; ?>
  </div>
</div>

</div><!-- /.inicio-page -->

<script src="<?= v('/assets/portal/js/inicio.js') ?>"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
