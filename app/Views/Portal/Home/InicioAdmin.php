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

$TIPO_LABEL = ['fallo' => ['Fallo', 'badge-danger'], 'mejora' => ['Mejora', 'badge-info']];
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/inicio.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/admin-inicio.css') ?>">

<div class="hero">
  <div class="hero-main">
    <div class="kicker"><i class="bi bi-shield-check" aria-hidden="true"></i> Centro de administración</div>
    <h1><?= e($nombre) ?>, esto es lo tuyo.</h1>
    <p>Tu bitácora técnica y tus pendientes — el resto del portal lo administras desde <a href="<?= BASE_URL ?>/modulos">Todos los módulos</a>.</p>
  </div>
</div>

<?php
// Atajos a los paneles que solo el admin global gestiona (mismos destinos
// que la página /administracion y "Todos los módulos"). Navegación, no datos.
$ACCESOS_ADMIN = [
    ['/usuarios', 'people', 'Usuarios y roles', 'Cuentas, roles y áreas de trabajo'],
    ['/permisos-por-rol', 'shield-lock', 'Permisos por rol', 'Qué módulos ve cada rol y cargo'],
    ['/contenido-landing', 'window-stack', 'Contenido landing', 'Lo que ve la portada pública'],
    ['/contrataciones', 'file-earmark-person', 'Contrataciones', 'Enlaces y documentos de candidatos'],
    ['/postulaciones', 'person-lines-fill', 'Postulaciones', 'Hojas de vida recibidas'],
    ['/modulos', 'grid-3x3-gap', 'Todos los módulos', 'Direcciones, recursos y analítica'],
];
?>
<section class="admin-accesos">
  <div class="section-header"><h2 class="section-heading"><i class="bi bi-lightning-charge" aria-hidden="true"></i> Accesos de administración</h2></div>
  <div class="tile-grid">
    <?php foreach ($ACCESOS_ADMIN as [$ruta, $icono, $label, $meta]): ?>
      <a class="tile admin-acceso" href="<?= BASE_URL . $ruta ?>">
        <span class="admin-acceso-ic" aria-hidden="true"><i class="bi bi-<?= $icono ?>" aria-hidden="true"></i></span>
        <span class="admin-acceso-label"><?= e($label) ?></span>
        <span class="admin-acceso-meta"><?= e($meta) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<div class="admin-two-col">

  <?php if (!in_array('soportes', $widgetsOcultos, true)): ?>
  <div>
    <div class="section-header">
      <h2 class="section-heading"><i class="bi bi-life-preserver" aria-hidden="true"></i> Soportes</h2>
      <button type="button" class="btn btn-link" data-reveal="formNuevoSoporte" aria-expanded="false" aria-controls="formNuevoSoporte"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar</button>
    </div>

    <form action="<?= BASE_URL ?>/soportes/crear" method="post" class="quick-form" id="formNuevoSoporte" hidden>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <div class="segmented" role="radiogroup" aria-label="Tipo de soporte">
        <input type="radio" name="tipo" value="fallo" id="soporteFallo" checked><label for="soporteFallo"><i class="bi bi-bug" aria-hidden="true"></i> Fallo</label>
        <input type="radio" name="tipo" value="mejora" id="soporteMejora"><label for="soporteMejora"><i class="bi bi-lightbulb" aria-hidden="true"></i> Mejora</label>
      </div>
      <input class="input input-sm" type="text" name="titulo" placeholder="¿Qué fallo o mejora quieres anotar?" aria-label="Soporte" maxlength="200" required>
      <input class="input input-sm" type="text" name="descripcion" placeholder="Detalle (opcional)" aria-label="Detalle del soporte (opcional)" maxlength="500">
      <div class="quick-form-actions">
        <button type="button" class="btn btn-ghost btn-sm" data-reveal-cancel>Cancelar</button>
        <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
      </div>
    </form>

    <div id="soportes">
      <?php if (!$soportes): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-life-preserver" aria-hidden="true"></i></div><p>Sin soportes registrados por ahora.</p></div>
      <?php else: foreach ($soportes as $s): [$tipoLabel, $tipoClase] = $TIPO_LABEL[$s['tipo']]; ?>
        <div class="pendiente soporte<?= $s['resuelto'] ? ' pendiente-hecho' : '' ?>">
          <form action="<?= BASE_URL ?>/soportes/completar" method="post" class="pendiente-check-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <input type="hidden" name="resuelto" value="0">
            <input type="checkbox" name="resuelto" value="1" class="pendiente-check"
                   <?= $s['resuelto'] ? 'checked' : '' ?> onchange="this.form.submit()" aria-label="Marcar «<?= e($s['titulo']) ?>» como resuelto">
          </form>
          <span class="item-copy">
            <span class="t"><span class="badge <?= e($tipoClase) ?> soporte-tag"><?= e($tipoLabel) ?></span><?= e($s['titulo']) ?></span>
            <?php if ($s['descripcion']): ?><span class="m"><?= e($s['descripcion']) ?></span><?php endif; ?>
          </span>
          <form action="<?= BASE_URL ?>/soportes/eliminar" method="post" class="pendiente-eliminar-form" data-confirm="¿Eliminar este soporte?" data-confirm-ok="Eliminar">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button type="submit" class="btn btn-ghost btn-sm btn-icon pendiente-eliminar" aria-label="Eliminar «<?= e($s['titulo']) ?>»"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
          </form>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!in_array('pendientes', $widgetsOcultos, true)): ?>
  <div>
    <?php $listaPendientes = $misPendientes; require ROOT_PATH . '/app/Views/Portal/_shared/_widget_pendientes.php'; ?>

    <?php // Va dentro de esta misma columna (el grid de arriba es fijo a 2:
          // Soportes / Mis pendientes) — si algún día se oculta 'pendientes'
          // por rol, esto se oculta con él; independizarlo necesitaría una
          // 3ra columna o reacomodar el grid, fuera de alcance por ahora. ?>
    <?php if (!in_array('drive_personal', $widgetsOcultos, true)): ?>
    <div class="section-header section-header--spaced"><h2 class="section-heading"><i class="bi bi-google" aria-hidden="true"></i> Mi Google Drive</h2></div>
    <?php require ROOT_PATH . '/app/Views/Portal/_shared/_widget_drive.php'; ?>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
