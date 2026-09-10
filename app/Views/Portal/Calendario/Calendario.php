<?php $titulo = 'Calendario'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/calendario.css">
<h1 class="page-title">Calendario</h1>
<p class="page-desc">Agenda institucional — mismos eventos que ya ves en Inicio, ahora en calendario completo.</p>

<?php
// Cualquier usuario logueado puede crear eventos propios (privados por
// defecto); solo un admin puede además marcar uno como público (visible
// para todo el portal) — ver EventoController.php, que vuelve a validar
// esto en servidor sin confiar en lo que llegue del formulario. Editar o
// eliminar un evento: mismo dueño, o admin (puede corregir cualquiera).
$esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin';
$puedeCrear = true;
$miId = (int) $_SESSION['usuario_id'];
$puedeEditarEvento = fn (array $ev): bool => $esAdmin || (int) $ev['usuario_id'] === $miId;

// Agenda del día — por defecto "hoy" si el mes que se ve es el actual;
// calendario.js la reemplaza al vuelo con la del día que se haga clic
// (los datos de todo el mes ya viajan una sola vez, ver EVENTOS_POR_DIA
// más abajo — mismo criterio que ORGANIGRAMA_DB en Talento Humano: esta
// vista recarga página completa por navegación, así que no hay AJAX).
$calAgendaEventos = $calHoy !== null ? ($eventosPorDia[$calHoy] ?? []) : [];
$calAgendaEtiqueta = $calHoy !== null ? ('Hoy · ' . $calHoy . ' de ' . $calTituloMes) : 'Selecciona un día';

// El enlace de exportar (.ics) nunca puede quedar anidado dentro del
// <button> de editar — <a> dentro de <button> es HTML inválido y algunos
// navegadores lo "arreglan" moviendo el <a> fuera, rompiendo el clic. Por
// eso van como hermanos dentro de un contenedor <div> (no clicable).
function cal_render_evento_item(array $ev, bool $puedeEditar, bool $conFecha = false): void {
    $claseExtra = $ev['visibilidad'] === 'privado' ? ' agenda-item-privado' : '';
    $infoTag = $puedeEditar ? 'button' : 'div';

    echo '<div class="agenda-item' . $claseExtra . '">';

    echo "<{$infoTag} class=\"agenda-item-info\"";
    if ($puedeEditar) {
        echo ' type="button" data-editar-evento'
            . ' data-id="' . (int) $ev['id'] . '"'
            . ' data-titulo="' . e($ev['titulo']) . '"'
            . ' data-fecha="' . e($ev['fecha']) . '"'
            . ' data-hora-lugar="' . e($ev['hora_lugar'] ?? '') . '"'
            . ' data-visibilidad="' . e($ev['visibilidad']) . '"';
    }
    echo '>';
    echo '<span class="agenda-item-text">';
    echo '<span class="agenda-item-t">';
    if ($ev['visibilidad'] === 'privado') echo '<i class="bi bi-lock-fill" title="Privado"></i> ';
    echo e($ev['titulo']);
    echo '</span>';
    $sub = [];
    if ($conFecha) $sub[] = (new DateTime($ev['fecha']))->format('d/m');
    if (!empty($ev['hora_lugar'])) $sub[] = $ev['hora_lugar'];
    if ($sub) echo '<span class="agenda-item-h">' . e(implode(' · ', $sub)) . '</span>';
    echo '</span>';
    if ($puedeEditar) echo '<i class="bi bi-pencil-fill agenda-item-editar" title="Editar"></i>';
    echo "</{$infoTag}>";

    echo '<a class="evento-ics" href="' . BASE_URL . '/calendario/exportar?id=' . (int) $ev['id'] . '" title="Agregar a mi calendario (.ics)"><i class="bi bi-calendar-plus"></i></a>';
    echo '</div>';
}
?>

<div class="cal-layout">
  <div class="cal-main box-card">
    <div class="cal-toolbar">
      <a class="cal-nav" href="<?= BASE_URL ?>/calendario?mes=<?= e($calMesAnterior) ?>"><i class="bi bi-chevron-left"></i></a>
      <h4 class="cal-titulo"><?= e(ucfirst($calTituloMes)) ?></h4>
      <a class="cal-nav" href="<?= BASE_URL ?>/calendario?mes=<?= e($calMesSiguiente) ?>"><i class="bi bi-chevron-right"></i></a>
      <a class="cal-hoy" href="<?= BASE_URL ?>/calendario">Hoy</a>
    </div>

    <div class="cal-grid">
      <?php foreach ($DIAS_SEMANA as $d): ?>
        <div class="cal-encabezado"><?= e($d) ?></div>
      <?php endforeach; ?>

      <?php foreach ($calSemanas as $semana): foreach ($semana as $dia): ?>
        <?php
          $celdaClase = 'cal-celda' . ($dia === null ? ' cal-vacia' : '') . (($dia !== null && $dia === $calHoy) ? ' cal-hoy' : '');
          $celdaClicable = $puedeCrear && $dia !== null;
          $tag = $celdaClicable ? 'button' : 'div';
        ?>
        <<?= $tag ?> class="<?= $celdaClase ?>"<?php if ($celdaClicable): ?> type="button" data-dia="<?= $dia ?>" title="Ver o agregar evento el <?= $dia ?>"<?php endif; ?>>
          <?php if ($dia !== null): ?>
            <span class="cal-num"><?= $dia ?></span>
            <?php foreach ($eventosPorDia[$dia] ?? [] as $ev): ?>
              <span class="cal-evento<?= $ev['visibilidad'] === 'privado' ? ' cal-evento-privado' : '' ?>" title="<?= e($ev['titulo'] . ' · ' . $ev['hora_lugar']) ?>"><?php if ($ev['visibilidad'] === 'privado'): ?><i class="bi bi-lock-fill"></i> <?php endif; ?><?= e($ev['titulo']) ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
        </<?= $tag ?>>
      <?php endforeach; endforeach; ?>
    </div>
  </div>

  <aside class="cal-agenda box-card box-card--flat">
    <div class="pill-tabs cal-agenda-tabs" id="calAgendaTabs">
      <button type="button" class="pill-tab active" data-tab="dia">Hoy</button>
      <button type="button" class="pill-tab" data-tab="mes">Este mes</button>
    </div>

    <div class="cal-agenda-panel" id="calPanelDia">
      <span class="cal-agenda-fecha" id="calAgendaFecha"><?= e($calAgendaEtiqueta) ?></span>
      <div class="cal-agenda-lista" id="calAgendaLista">
        <?php if (!$calAgendaEventos): ?>
          <p class="text-muted cal-agenda-vacia">Sin eventos para este día.</p>
        <?php else: foreach ($calAgendaEventos as $ev): ?>
          <?php cal_render_evento_item($ev, $puedeEditarEvento($ev)); ?>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <?php
      $todosLosEventosDelMes = array_merge(...array_values($eventosPorDia ?: [[]]));
      usort($todosLosEventosDelMes, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));
    ?>
    <div class="cal-agenda-panel" id="calPanelMes" hidden>
      <span class="cal-agenda-fecha"><?= e(ucfirst($calTituloMes)) ?></span>
      <div class="cal-agenda-lista cal-agenda-lista-scroll">
        <?php if (!$todosLosEventosDelMes): ?>
          <p class="text-muted cal-agenda-vacia">No hay eventos programados para este mes.</p>
        <?php else: foreach ($todosLosEventosDelMes as $ev): ?>
          <?php cal_render_evento_item($ev, $puedeEditarEvento($ev), conFecha: true); ?>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <?php if ($puedeCrear): ?>
    <button type="button" class="btn btn-primary cal-agenda-nuevo" id="calNuevoBtn">
      <i class="bi bi-plus-lg"></i> Nuevo evento
    </button>
    <?php endif; ?>
  </aside>
</div>

<?php if ($puedeCrear): ?>
<div class="cal-popover" id="calPopover" hidden>
  <div class="cal-pop-head">
    <strong id="calPopTituloModo"><i class="bi bi-calendar-event"></i> Nuevo evento</strong>
    <button type="button" class="cal-pop-close" id="calPopClose" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
  </div>
  <form action="<?= BASE_URL ?>/calendario/crear-evento" method="post" class="cal-pop-form" id="calPopForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="mes" value="<?= e($calMesActual) ?>">
    <input type="hidden" name="id" id="calPopId" value="">

    <label class="cal-pop-label" for="calPopTitulo">Título</label>
    <input type="text" name="titulo" id="calPopTitulo" placeholder="Ej. Día de descanso institucional" maxlength="200" required>

    <label class="cal-pop-label" for="calPopFecha">Fecha</label>
    <input type="date" name="fecha" id="calPopFecha" required>

    <label class="cal-pop-label" for="calPopHoraLugar">Hora y lugar</label>
    <input type="text" name="hora_lugar" id="calPopHoraLugar" placeholder="Ej. 10:00 · Sala de juntas" maxlength="100">

    <?php if ($esAdmin): ?>
      <span class="cal-pop-label">Visibilidad</span>
      <div class="pill-tabs cal-pop-visibilidad" id="calPopVisibilidad">
        <button type="button" class="pill-tab active" data-valor="publico"><i class="bi bi-globe2"></i> Público</button>
        <button type="button" class="pill-tab" data-valor="privado"><i class="bi bi-lock-fill"></i> Privado</button>
      </div>
      <input type="hidden" name="visibilidad" id="calPopVisibilidadInput" value="publico">
    <?php else: ?>
      <p class="cal-pop-nota"><i class="bi bi-lock-fill"></i> Se crea como recordatorio privado, solo visible para ti.</p>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary" id="calPopSubmitBtn"><i class="bi bi-check-lg"></i> Crear evento</button>
  </form>
  <form action="<?= BASE_URL ?>/calendario/eliminar-evento" method="post" class="cal-pop-delete" id="calPopDeleteForm" hidden onsubmit="return confirm('¿Eliminar este evento? Esta acción no se puede deshacer.')">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="mes" value="<?= e($calMesActual) ?>">
    <input type="hidden" name="id" id="calPopDeleteId" value="">
    <button type="submit" class="btn btn-danger"><i class="bi bi-trash3"></i> Eliminar evento</button>
  </form>
</div>
<?php endif; ?>

<?php if ($puedeCrear): ?>
<script>
  // Todo el mes visible ya viaja una sola vez (mismo criterio que
  // ORGANIGRAMA_DB en Talento Humano) — calendario.js arma la agenda de
  // cada día sin volver a pedirle nada al servidor.
  const EVENTOS_POR_DIA = <?= json_encode($eventosPorDia, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const CAL_MES_TITULO = <?= json_encode($calTituloMes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const CAL_BASE_URL = <?= json_encode(BASE_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const CAL_MI_ID = <?= json_encode($miId) ?>;
  const CAL_ES_ADMIN = <?= json_encode($esAdmin) ?>;
</script>
<script src="<?= BASE_URL ?>/assets/portal/js/calendario.js"></script>
<?php endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
