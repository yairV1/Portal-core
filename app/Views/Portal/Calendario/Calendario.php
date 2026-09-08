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

<?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
<details class="cal-nuevo">
  <summary><i class="bi bi-plus-lg"></i> Nuevo evento</summary>
  <form action="<?= BASE_URL ?>/calendario/crear-evento" method="post" class="cal-form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="mes" value="<?= e($calMesActual) ?>">
    <input type="text" name="titulo" placeholder="Título del evento" maxlength="200" required>
    <input type="date" name="fecha" required>
    <input type="text" name="hora_lugar" placeholder="Hora y lugar (ej. 10:00 · Sala de juntas)" maxlength="100">
    <button type="submit"><i class="bi bi-check-lg"></i> Crear</button>
  </form>
</details>
<?php endif; ?>

<div class="cal-grid">
  <?php foreach ($DIAS_SEMANA as $d): ?>
    <div class="cal-encabezado"><?= e($d) ?></div>
  <?php endforeach; ?>

  <?php foreach ($calSemanas as $semana): foreach ($semana as $dia): ?>
    <div class="cal-celda<?= $dia === null ? ' cal-vacia' : '' ?><?= ($dia !== null && $dia === $calHoy) ? ' cal-hoy' : '' ?>">
      <?php if ($dia !== null): ?>
        <span class="cal-num"><?= $dia ?></span>
        <?php foreach ($eventosPorDia[$dia] ?? [] as $ev): ?>
          <span class="cal-evento" title="<?= e($ev['titulo'] . ' · ' . $ev['hora_lugar']) ?>"><?= e($ev['titulo']) ?></span>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
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
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
