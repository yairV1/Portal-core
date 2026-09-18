<?php
/** Variables que llegan ya resueltas desde HomeController.php (rama admin).
 * @var string $nombre
 * @var array  $soportes
 * @var array  $misPendientes
 */
$titulo = 'Centro de administración';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';

$TIPO_LABEL = ['fallo' => ['Fallo', 'tag-danger'], 'mejora' => ['Mejora', 'tag-info']];
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/inicio.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/admin-inicio.css') ?>">

<div class="hero">
  <div class="hero-main">
    <div class="kicker"><i class="bi bi-shield-check"></i> Centro de administración</div>
    <h1><?= e($nombre) ?>, esto es lo tuyo.</h1>
    <p>Tu bitácora técnica y tus pendientes — el resto del portal lo administras desde <a href="<?= BASE_URL ?>/modulos">Todos los módulos</a>.</p>
  </div>
</div>

<div class="admin-two-col">

  <div>
    <div class="section-head section-head--compact">
      <h4>Soportes</h4>
      <button type="button" class="btn btn-link" id="btnNuevoSoporte"><i class="bi bi-plus-lg"></i> Agregar</button>
    </div>

    <form action="<?= BASE_URL ?>/soportes/crear" method="post" class="pendiente-form" id="formNuevoSoporte" hidden>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="text" name="titulo" placeholder="¿Qué fallo o mejora quieres anotar?" maxlength="200" required>
      <input type="text" name="descripcion" placeholder="Detalle (opcional)" maxlength="500">
      <div class="soporte-tipo-radios">
        <label><input type="radio" name="tipo" value="fallo" checked> Fallo</label>
        <label><input type="radio" name="tipo" value="mejora"> Mejora</label>
      </div>
      <button type="submit" class="btn btn-primary">Agregar</button>
    </form>

    <div id="soportes">
      <?php if (!$soportes): ?>
        <p class="text-muted">No hay soportes registrados por ahora.</p>
      <?php else: foreach ($soportes as $s): [$tipoLabel, $tipoClase] = $TIPO_LABEL[$s['tipo']]; ?>
        <div class="pendiente soporte<?= $s['resuelto'] ? ' pendiente-hecho' : '' ?>">
          <form action="<?= BASE_URL ?>/soportes/completar" method="post" class="pendiente-check-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <input type="hidden" name="resuelto" value="0">
            <input type="checkbox" name="resuelto" value="1" class="pendiente-check"
                   <?= $s['resuelto'] ? 'checked' : '' ?> onchange="this.form.submit()" title="Marcar como resuelto">
          </form>
          <span class="item-copy">
            <span class="t"><span class="tag <?= e($tipoClase) ?> soporte-tag"><?= e($tipoLabel) ?></span><?= e($s['titulo']) ?></span>
            <?php if ($s['descripcion']): ?><span class="m"><?= e($s['descripcion']) ?></span><?php endif; ?>
          </span>
          <form action="<?= BASE_URL ?>/soportes/eliminar" method="post" class="pendiente-eliminar-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button type="submit" class="pendiente-eliminar" title="Eliminar" onclick="return confirm('¿Eliminar este soporte?')"><i class="fa-solid fa-xmark"></i></button>
          </form>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div>
    <div class="section-head section-head--compact">
      <h4>Mis pendientes</h4>
      <button type="button" class="btn btn-link" id="btnNuevoPendiente"><i class="bi bi-plus-lg"></i> Agregar</button>
    </div>

    <form action="<?= BASE_URL ?>/pendientes/crear" method="post" class="pendiente-form" id="formNuevoPendiente" hidden>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="text" name="titulo" placeholder="¿Qué tienes pendiente?" maxlength="200" required>
      <input type="text" name="meta" placeholder="Detalle (opcional, ej. vence hoy)" maxlength="150">
      <button type="submit" class="btn btn-primary">Agregar</button>
    </form>

    <div id="pendientes">
      <?php if (!$misPendientes): ?>
        <p class="text-muted">No hay pendientes por ahora.</p>
      <?php else: foreach ($misPendientes as $p): ?>
        <div class="pendiente<?= $p['completado'] ? ' pendiente-hecho' : '' ?>">
          <form action="<?= BASE_URL ?>/pendientes/completar" method="post" class="pendiente-check-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <input type="hidden" name="completado" value="0">
            <input type="checkbox" name="completado" value="1" class="pendiente-check"
                   <?= $p['completado'] ? 'checked' : '' ?> onchange="this.form.submit()" title="Marcar como hecho">
          </form>
          <span class="item-copy">
            <span class="t"><?= e($p['titulo']) ?></span>
            <?php if ($p['meta']): ?><span class="m"><?= e($p['meta']) ?></span><?php endif; ?>
          </span>
          <form action="<?= BASE_URL ?>/pendientes/eliminar" method="post" class="pendiente-eliminar-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button type="submit" class="pendiente-eliminar" title="Eliminar" onclick="return confirm('¿Eliminar este pendiente?')"><i class="fa-solid fa-xmark"></i></button>
          </form>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

</div>

<script>
  // Mismo criterio que btnNuevoPendiente de inicio.js, pero ese archivo
  // solo conecta un botón/form con id fijo — acá hay dos pares (Soportes y
  // Mis pendientes), así que se resuelve aparte en vez de generalizar
  // inicio.js para un caso que solo usa esta página.
  document.addEventListener('DOMContentLoaded', function () {
    [['btnNuevoSoporte', 'formNuevoSoporte'], ['btnNuevoPendiente', 'formNuevoPendiente']].forEach(function (par) {
      var btn = document.getElementById(par[0]);
      var form = document.getElementById(par[1]);
      if (!btn || !form) return;
      btn.addEventListener('click', function () {
        form.hidden = !form.hidden;
        if (!form.hidden) form.querySelector('input[name="titulo"]').focus();
      });
    });
  });
</script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
