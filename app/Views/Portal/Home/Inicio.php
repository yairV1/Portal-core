<?php $titulo = 'Inicio'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/inicio.css">

<?php $COLOR_ESTADO = ['success' => 'var(--color-success)', 'warning' => 'var(--color-warning)', 'accent' => 'var(--color-accent)']; ?>

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
      Jeiver, tienes <?= count($pendientes) ?> pendiente<?= count($pendientes) === 1 ? '' : 's' ?>
      <?php if ($docsEnRevision > 0): ?>
        y <?= $docsEnRevision ?> documento<?= $docsEnRevision === 1 ? '' : 's' ?> en revisión.
      <?php else: ?>
        y ningún documento en revisión.
      <?php endif; ?>
      El semestre 2026-II sigue su curso.
    </p>
    <div class="hero-status">
      <span class="hero-pill"><span class="dot"></span>Operación normal</span>
      <span class="hero-pill">2026 · Semestre II</span>
    </div>
  </div>

  <?php if ($pdiValor !== ''): ?>
  <div class="hero-pdi">
    <div class="hero-pdi-label">Avance PDI 2026</div>
    <div class="hero-pdi-valor"><?= e($pdiValor) ?><span><?= e($pdiSufijo) ?></span></div>
    <?php if ($pdiDesc): ?><div class="hero-pdi-desc"><?= e($pdiDesc) ?></div><?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div class="kpis" id="kpis">
  <?php if (!$kpis): ?>
    <p class="text-muted">Sin indicadores por ahora.</p>
  <?php else: foreach ($kpis as $i => $k): ?>
    <div class="kpi">
      <div class="kpi-head">
        <?php if ($k['icono']): ?><i class="bi bi-<?= e($k['icono']) ?> kpi-ic"></i><?php endif; ?>
        <div class="kpi-label"><?= e($k['label']) ?></div>
      </div>
      <div class="kpi-value"><?= e($k['valor']) ?></div>
      <div class="kpi-foot">
        <span class="tag tag-<?= e($k['estado']) ?>"><?= e($k['delta']) ?></span>
        <?php if ($k['tendencia']): ?>
          <span class="kpi-spark" data-tendencia="<?= e(implode(',', $k['tendencia'])) ?>" data-color="<?= e($COLOR_ESTADO[$k['estado']] ?? $COLOR_ESTADO['accent']) ?>"></span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class="grid-main">
  <div>
    <div class="section-head">
      <h4>Accesos rápidos</h4>
      <span class="link">Centro de aplicaciones</span>
    </div>
    <div class="accesos" id="accesos">
      <?php if (!$accesos): ?>
        <p class="text-muted">Sin accesos configurados por ahora.</p>
      <?php else: foreach ($accesos as $i => $a): ?>
        <a class="acceso<?= $a['sugerido'] ? ' acceso-sugerido' : '' ?>"
           <?= $a['href'] !== '' ? 'href="' . e($a['href']) . '"' : 'aria-disabled="true"' ?>
           <?= $a['externo'] ? 'target="_blank" rel="noopener"' : '' ?>>
          <?php if ($a['sugerido']): ?><span class="acceso-badge">Core sugiere</span><?php endif; ?>
          <span class="ic"><i class="bi bi-<?= e($a['icono']) ?>"></i></span>
          <span class="label"><?= e($a['label']) ?></span>
          <span class="meta"><?= e($a['meta']) ?></span>
        </a>
      <?php endforeach; endif; ?>
    </div>

    <div class="section-head" style="margin-top:44px">
      <h4>Documentos recientes</h4>
      <span class="link">Repositorio</span>
    </div>
    <table class="table">
      <thead><tr><th>Documento</th><th>Área</th><th>Ver.</th><th>Actualizado</th></tr></thead>
      <tbody id="docsRecientes">
        <?php if (!$docsRecientes): ?>
          <tr><td colspan="4" class="text-muted">No hay documentos recientes.</td></tr>
        <?php else: foreach ($docsRecientes as $d): ?>
          <tr>
            <td><strong><?= e($d['nombre']) ?></strong></td>
            <td style="opacity:.7"><?= e($d['area']) ?></td>
            <td>
              <span style="display:inline-flex;align-items:center;gap:8px">
                <span class="tag-stamp"><?= e($d['version']) ?></span>
                <span class="tag tag-<?= e($d['estadoTag']) ?>"><?= e($d['estado']) ?></span>
              </span>
            </td>
            <td style="opacity:.7"><?= e($d['fecha']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>

    <div class="section-head" style="margin-top:44px">
      <h4>Novedades institucionales</h4>
    </div>
    <div class="noticias" id="noticias">
      <?php if (!$noticias): ?>
        <p class="text-muted">Sin novedades por ahora.</p>
      <?php else: foreach ($noticias as $n): ?>
        <div class="card">
          <div class="noticia-foto">Fotografía institucional</div>
          <div class="noticia-body">
            <div class="noticia-cat"><?= e($n['categoria']) ?></div>
            <div class="noticia-titulo"><?= e($n['titulo']) ?></div>
            <div class="noticia-fecha"><?= e($n['fecha']) ?></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div class="side-col">
    <div class="section-head" style="margin-bottom:6px"><h4>Mis pendientes</h4></div>
    <div id="pendientes">
      <?php if (!$pendientes): ?>
        <p class="text-muted">No hay pendientes por ahora.</p>
      <?php else: foreach ($pendientes as $p): ?>
        <div class="pendiente">
          <span class="dotp" style="background:<?= e($p['color']) ?>"></span>
          <span style="flex:1">
            <span class="t" style="display:block"><?= e($p['titulo']) ?></span>
            <span class="m" style="display:block"><?= e($p['meta']) ?></span>
          </span>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="section-head" style="margin-bottom:6px; margin-top:34px"><h4>Agenda de la semana</h4></div>
    <div id="eventos">
      <?php if (!$eventos): ?>
        <p class="text-muted">No hay eventos programados.</p>
      <?php else: foreach ($eventos as $ev): ?>
        <div class="evento">
          <span class="fecha">
            <span class="dia" style="display:block"><?= e($ev['dia']) ?></span>
            <span class="mes" style="display:block"><?= e($ev['mes']) ?></span>
          </span>
          <span style="flex:1">
            <span class="t" style="display:block"><?= e($ev['titulo']) ?></span>
            <span class="h" style="display:block"><?= e($ev['hora']) ?></span>
          </span>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="section-head" style="margin-bottom:6px; margin-top:34px"><h4>Cumpleaños</h4></div>
    <div id="cumpleanos">
      <?php if (!$cumpleanos): ?>
        <p class="text-muted">Sin cumpleaños esta semana.</p>
      <?php else: foreach ($cumpleanos as $c): ?>
        <div class="cumple">
          <span class="ini"><?= e($c['ini']) ?></span>
          <span class="n"><?= e($c['nombre']) ?></span>
          <span class="f"><?= e($c['fecha']) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

</div><!-- /.inicio-page -->

<script src="<?= BASE_URL ?>/assets/portal/js/inicio.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
