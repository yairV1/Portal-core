<?php $titulo = 'Calendario'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/calendario.css">
<h1 class="page-title">Calendario</h1>
<p class="page-desc">Agenda institucional — mismos eventos que ya ves en Inicio, ahora en calendario completo.</p>

<div class="cal-toolbar">
  <a class="cal-nav" href="<?= BASE_URL ?>/calendario?mes=<?= e($calMesAnterior) ?>"><i class="bi bi-chevron-left"></i></a>
  <h4 class="cal-titulo"><?= e(ucfirst($calTituloMes)) ?></h4>
  <a class="cal-nav" href="<?= BASE_URL ?>/calendario?mes=<?= e($calMesSiguiente) ?>"><i class="bi bi-chevron-right"></i></a>
  <a class="cal-hoy" href="<?= BASE_URL ?>/calendario">Hoy</a>
</div>

<?php $esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin'; ?>

<?php if ($esAdmin): ?>
<div class="cal-popover" id="calPopover" hidden>
  <div class="cal-pop-head">
    <strong>Nuevo evento</strong>
    <button type="button" class="cal-pop-close" id="calPopClose" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
  </div>
  <form action="<?= BASE_URL ?>/calendario/crear-evento" method="post" class="cal-pop-form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="mes" value="<?= e($calMesActual) ?>">
    <input type="text" name="titulo" placeholder="Título del evento" maxlength="200" required>
    <input type="date" name="fecha" id="calPopFecha" required>
    <input type="text" name="hora_lugar" placeholder="Hora y lugar (ej. 10:00 · Sala de juntas)" maxlength="100">
    <button type="submit"><i class="bi bi-check-lg"></i> Crear</button>
  </form>
</div>
<?php endif; ?>

<div class="cal-grid">
  <?php foreach ($DIAS_SEMANA as $d): ?>
    <div class="cal-encabezado"><?= e($d) ?></div>
  <?php endforeach; ?>

  <?php foreach ($calSemanas as $semana): foreach ($semana as $dia): ?>
    <?php
      $celdaClase = 'cal-celda' . ($dia === null ? ' cal-vacia' : '') . (($dia !== null && $dia === $calHoy) ? ' cal-hoy' : '');
      $celdaClicable = $esAdmin && $dia !== null;
      $tag = $celdaClicable ? 'button' : 'div';
    ?>
    <<?= $tag ?> class="<?= $celdaClase ?>"<?php if ($celdaClicable): ?> type="button" data-dia="<?= $dia ?>" title="Agregar evento el <?= $dia ?>"<?php endif; ?>>
      <?php if ($dia !== null): ?>
        <span class="cal-num"><?= $dia ?></span>
        <?php foreach ($eventosPorDia[$dia] ?? [] as $ev): ?>
          <span class="cal-evento" title="<?= e($ev['titulo'] . ' · ' . $ev['hora_lugar']) ?>"><?= e($ev['titulo']) ?></span>
        <?php endforeach; ?>
      <?php endif; ?>
    </<?= $tag ?>>
  <?php endforeach; endforeach; ?>
</div>

<div class="section-head" style="margin-top:32px"><h4>Eventos de <?= e($calTituloMes) ?></h4></div>
<?php
$todosLosEventosDelMes = array_merge(...array_values($eventosPorDia ?: [[]]));
usort($todosLosEventosDelMes, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));
?>
<?php if (!$todosLosEventosDelMes): ?>
  <p class="text-muted">No hay eventos programados para este mes.</p>
<?php else: foreach ($todosLosEventosDelMes as $ev): ?>
  <div class="evento">
    <span class="fecha">
      <span class="dia" style="display:block"><?= e((new DateTime($ev['fecha']))->format('d')) ?></span>
    </span>
    <span style="flex:1">
      <span class="t" style="display:block"><?= e($ev['titulo']) ?></span>
      <span class="h" style="display:block"><?= e($ev['hora_lugar']) ?></span>
    </span>
  </div>
<?php endforeach; endif; ?>
<?php if ($esAdmin): ?><script src="<?= BASE_URL ?>/assets/portal/js/calendario.js"></script><?php endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
