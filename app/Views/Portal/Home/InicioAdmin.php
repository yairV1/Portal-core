<?php
/** Variables que llegan ya resueltas desde HomeController.php (rama admin).
 * @var string $nombre
 * @var array  $soportes
 * @var array  $misPendientes
 * @var array  $widgetsOcultos claves ('soportes'/'pendientes'/'drive_personal')
 *             que este admin no debe ver — hoy siempre vacío, ver
 *             HomeController.php.
 * @var bool   $miDriveOauthConfigurado
 * @var bool   $miDriveConectado
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

  <?php if (!in_array('soportes', $widgetsOcultos, true)): ?>
  <div>
    <div class="section-head section-head--compact">
      <h4><i class="bi bi-life-preserver"></i> Soportes</h4>
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
        <p class="widget-empty"><i class="bi bi-life-preserver"></i> Sin soportes registrados por ahora.</p>
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
  <?php endif; ?>

  <?php if (!in_array('pendientes', $widgetsOcultos, true)): ?>
  <div>
    <div class="section-head section-head--compact">
      <h4><i class="bi bi-check2-square"></i> Mis pendientes</h4>
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
        <p class="widget-empty"><i class="bi bi-check2-circle"></i> Sin pendientes por ahora.</p>
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

    <?php // Va dentro de esta misma columna (el grid de arriba es fijo a 2:
          // Soportes / Mis pendientes) — si algún día se oculta 'pendientes'
          // por rol, esto se oculta con él; independizarlo necesitaría una
          // 3ra columna o reacomodar el grid, fuera de alcance por ahora. ?>
    <?php if (!in_array('drive_personal', $widgetsOcultos, true)): ?>
    <div class="section-head section-head--compact section-head--spaced-small"><h4><i class="bi bi-google"></i> Mi Google Drive</h4></div>
    <?php if (!$miDriveOauthConfigurado): ?>
      <p class="widget-empty"><i class="bi bi-google"></i> No configurado en este entorno.</p>
    <?php elseif ($miDriveConectado): ?>
      <p class="text-muted" style="margin:0 0 10px;font-size:12.5px"><i class="bi bi-check-circle-fill" style="color:var(--color-success)"></i> Tu cuenta está conectada.</p>
      <a href="<?= BASE_URL ?>/mi-drive" class="btn btn-link" style="text-decoration:none"><i class="bi bi-folder2"></i> Ver mis archivos</a>
    <?php else: ?>
      <p class="text-muted" style="margin:0 0 10px;font-size:12.5px">Trae tus propios archivos de Drive al portal.</p>
      <a href="<?= BASE_URL ?>/mi-drive" class="btn btn-primary" style="text-decoration:none"><i class="bi bi-google"></i> Conectar</a>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  <?php endif; ?>

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
