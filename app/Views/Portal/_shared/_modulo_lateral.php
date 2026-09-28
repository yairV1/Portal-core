<?php
/**
 * Columna lateral de un módulo de dirección: Responsables y Software
 * relacionado. Recibe $moduloResponsables y $moduloSoftware (PortalController).
 */
?>
<section class="box-card box-card--flat box-card--compact">
  <h2 class="side-box-title">Responsables</h2>
  <?php if (!$moduloResponsables): ?>
    <div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-people" aria-hidden="true"></i></div><p>Sin responsables por ahora.</p></div>
  <?php else: foreach ($moduloResponsables as $r):
      $partesNombre = preg_split('/\s+/', trim($r['nombre']));
      $iniciales = mb_strtoupper(mb_substr($partesNombre[0], 0, 1) . mb_substr(end($partesNombre), 0, 1));
  ?>
    <div class="responsable">
      <span class="ini" aria-hidden="true"><?= e($iniciales) ?></span>
      <span class="doc-flex-grow">
        <span class="nombre"><?= e($r['nombre']) ?></span>
        <span class="cargo"><?= e($r['cargo']) ?></span>
      </span>
    </div>
  <?php endforeach; endif; ?>
</section>
<section class="box-card box-card--flat box-card--compact">
  <h2 class="side-box-title">Software relacionado</h2>
  <?php if (!$moduloSoftware): ?>
    <div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-link-45deg" aria-hidden="true"></i></div><p>Sin software relacionado.</p></div>
  <?php else: foreach ($moduloSoftware as $s): ?>
    <div class="software-item"><span class="doc-soft-icon" aria-hidden="true"><i class="bi bi-app-indicator" aria-hidden="true"></i></span><?= e($s['nombre']) ?></div>
  <?php endforeach; endif; ?>
</section>
