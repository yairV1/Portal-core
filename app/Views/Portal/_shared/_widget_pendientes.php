<?php
/**
 * Widget "Mis pendientes" — lo usan Home/Inicio.php y Home/InicioAdmin.php
 * (antes estaba copiado en las dos). Recibe $listaPendientes (filas con
 * id, titulo, meta, completado) y $csrf. Endpoints: PendienteController.php.
 */
?>
<div class="section-header">
  <h2 class="section-heading"><i class="bi bi-check2-square" aria-hidden="true"></i> Mis pendientes</h2>
  <button type="button" class="btn btn-link" data-reveal="formNuevoPendiente" aria-expanded="false" aria-controls="formNuevoPendiente"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar</button>
</div>

<form action="<?= BASE_URL ?>/pendientes/crear" method="post" class="quick-form" id="formNuevoPendiente" hidden>
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
  <input class="input input-sm" type="text" name="titulo" placeholder="¿Qué tienes pendiente?" aria-label="Pendiente" maxlength="200" required>
  <input class="input input-sm" type="text" name="meta" placeholder="Detalle (opcional, ej. vence hoy)" aria-label="Detalle del pendiente (opcional)" maxlength="150">
  <div class="quick-form-actions">
    <button type="button" class="btn btn-ghost btn-sm" data-reveal-cancel>Cancelar</button>
    <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
  </div>
</form>

<div id="pendientes">
  <?php if (!$listaPendientes): ?>
    <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-check2-circle" aria-hidden="true"></i></div><p>Sin pendientes por ahora.</p></div>
  <?php else: foreach ($listaPendientes as $p): ?>
    <div class="pendiente<?= $p['completado'] ? ' pendiente-hecho' : '' ?>">
      <form action="<?= BASE_URL ?>/pendientes/completar" method="post" class="pendiente-check-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
        <input type="hidden" name="completado" value="0">
        <input type="checkbox" name="completado" value="1" class="pendiente-check"
               <?= $p['completado'] ? 'checked' : '' ?> onchange="this.form.submit()" aria-label="Marcar «<?= e($p['titulo']) ?>» como hecho">
      </form>
      <span class="item-copy">
        <span class="t"><?= e($p['titulo']) ?></span>
        <?php if ($p['meta']): ?><span class="m"><?= e($p['meta']) ?></span><?php endif; ?>
      </span>
      <form action="<?= BASE_URL ?>/pendientes/eliminar" method="post" class="pendiente-eliminar-form" data-confirm="¿Eliminar este pendiente?" data-confirm-text="<?= e($p['titulo']) ?>" data-confirm-ok="Eliminar">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
        <button type="submit" class="btn btn-ghost btn-sm btn-icon pendiente-eliminar" aria-label="Eliminar «<?= e($p['titulo']) ?>»"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
      </form>
    </div>
  <?php endforeach; endif; ?>
</div>
