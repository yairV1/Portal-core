<?php $titulo = 'Contenido landing'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/contenido-landing.css') ?>">
<?php
/** Variables desde ContenidoLandingController.php:
 * @var array $landingSecciones  clave => [etiqueta, titulo, descripcion]
 * @var array $landingEstadisticas, $landingModulos, $landingRoles, $landingPasos
 */

// Todas las claves de LANDING_SECCIONES_DEF que caen en una pestaña dada
// (ver el controlador — 'tab' agrupa por sección real de la portada).
$seccionesPorTab = fn (string $tab) => array_filter(LANDING_SECCIONES_DEF, fn ($def) => $def['tab'] === $tab);

$TABS = [
    'hero'    => ['label' => 'Hero',          'icono' => 'bi-flag'],
    'porque'  => ['label' => 'Por qué',       'icono' => 'bi-patch-question'],
    'modulos' => ['label' => 'Módulos',       'icono' => 'bi-grid-3x3-gap'],
    'roles'   => ['label' => 'Para tu rol',   'icono' => 'bi-people'],
    'pasos'   => ['label' => 'Cómo empiezas', 'icono' => 'bi-signpost-split'],
];

// Listas repetibles (mismas 4 que maneja $listasRepetibles en el
// controlador). 'campos' son los MISMOS name= que el controlador lee; 'req'
// refleja sus 'requeridos' (el servidor sigue validando igual).
$HINT_ICONO = 'Nombre de Bootstrap Icons, sin "bi-" (ej. shield-check).';
$LISTAS = [
    'estadistica' => [
        'titulo' => 'Estadísticas', 'singular' => 'estadística', 'items' => $landingEstadisticas,
        'desc' => 'Cifras de la franja "¿Por qué Portal CORE?".',
        'principal' => 'valor', 'secundario' => 'etiqueta', 'confirmar' => '¿Eliminar esta estadística?',
        'campos' => [
            ['name' => 'valor', 'label' => 'Cifra', 'req' => true, 'ph' => 'Ej. 98%'],
            ['name' => 'etiqueta', 'label' => 'Etiqueta', 'req' => true, 'ph' => 'Ej. Satisfacción'],
            ['name' => 'descripcion', 'label' => 'Descripción corta', 'req' => true, 'full' => true],
            ['name' => 'icono', 'label' => 'Ícono', 'ph' => 'shield-check', 'hint' => $HINT_ICONO],
            ['name' => 'orden', 'label' => 'Orden', 'type' => 'number'],
        ],
    ],
    'modulo' => [
        'titulo' => 'Módulos destacados', 'singular' => 'módulo', 'items' => $landingModulos,
        'desc' => 'Tarjetas de módulos que muestra la portada.',
        'principal' => 'titulo', 'secundario' => 'meta', 'confirmar' => '¿Eliminar este módulo de la portada?',
        'campos' => [
            ['name' => 'titulo', 'label' => 'Título', 'req' => true, 'full' => true],
            ['name' => 'meta', 'label' => 'Meta', 'req' => true, 'ph' => 'Ej. Módulo académico'],
            ['name' => 'estado', 'label' => 'Estado', 'req' => true, 'ph' => 'Ej. Disponible'],
            ['name' => 'ubicacion', 'label' => 'Ubicación', 'req' => true, 'ph' => 'Ej. Sidebar → Académico', 'full' => true],
            ['name' => 'icono', 'label' => 'Ícono', 'req' => true, 'ph' => 'mortarboard', 'hint' => $HINT_ICONO],
            ['name' => 'tema', 'label' => 'Tema', 'type' => 'select', 'opciones' => ['activo' => 'Activo (magenta)', 'desarrollo' => 'En desarrollo (naranja)', 'futuro' => 'Futuro (gris)'], 'def' => 'futuro'],
            ['name' => 'orden', 'label' => 'Orden', 'type' => 'number'],
        ],
    ],
    'rol' => [
        'titulo' => 'Tarjetas "Para tu rol"', 'singular' => 'tarjeta de rol', 'items' => $landingRoles,
        'desc' => 'Una tarjeta por tipo de usuario.',
        'principal' => 'titulo', 'secundario' => 'descripcion', 'confirmar' => '¿Eliminar esta tarjeta de rol?',
        'campos' => [
            ['name' => 'titulo', 'label' => 'Título', 'req' => true, 'ph' => 'Ej. Docentes'],
            ['name' => 'icono', 'label' => 'Ícono', 'req' => true, 'ph' => 'person-workspace', 'hint' => $HINT_ICONO],
            ['name' => 'descripcion', 'label' => 'Descripción', 'req' => true, 'type' => 'textarea', 'full' => true],
            ['name' => 'tema', 'label' => 'Tema', 'type' => 'select', 'opciones' => ['magenta' => 'Magenta', 'orange' => 'Naranja', 'dark' => 'Oscuro'], 'def' => 'magenta'],
            ['name' => 'orden', 'label' => 'Orden', 'type' => 'number'],
        ],
    ],
    'paso' => [
        'titulo' => 'Pasos', 'singular' => 'paso', 'items' => $landingPasos,
        'desc' => 'Los pasos de "Cómo empiezas", en orden.',
        'principal' => 'titulo', 'secundario' => 'descripcion', 'confirmar' => '¿Eliminar este paso?',
        'campos' => [
            ['name' => 'titulo', 'label' => 'Título del paso', 'req' => true, 'full' => true],
            ['name' => 'descripcion', 'label' => 'Descripción', 'req' => true, 'type' => 'textarea', 'full' => true],
            ['name' => 'icono', 'label' => 'Ícono', 'req' => true, 'ph' => 'box-arrow-in-right', 'hint' => $HINT_ICONO],
            ['name' => 'orden', 'label' => 'Orden', 'type' => 'number'],
        ],
    ],
];
$TAB_DE_LISTA = ['estadistica' => 'porque', 'modulo' => 'modulos', 'rol' => 'roles', 'paso' => 'pasos'];

// Tarjeta con los textos de una sección de la portada (un formulario cada una).
$tarjetaSeccion = function (string $clave, array $def) use ($landingSecciones, $csrf) {
    $actual = $landingSecciones[$clave] ?? ['etiqueta' => '', 'titulo' => '', 'descripcion' => ''];
    $id = 'sec-' . preg_replace('/[^a-z0-9]+/i', '-', $clave);
    ?>
    <form class="box-card landing-seccion" action="<?= BASE_URL ?>/contenido-landing/guardar-seccion" method="post">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="clave" value="<?= e($clave) ?>">
      <div class="box-card-header"><h3 class="box-card-title"><?= e($def['nombre']) ?></h3></div>
      <div class="form-grid">
        <?php if ($def['etiqueta']): ?>
          <div class="field">
            <label class="field-label" for="<?= $id ?>-et">Etiqueta pequeña <span class="opt">(kicker)</span></label>
            <input class="input" type="text" id="<?= $id ?>-et" name="etiqueta" value="<?= e($actual['etiqueta']) ?>">
          </div>
        <?php endif; ?>
        <div class="field<?= $def['etiqueta'] ? '' : ' field--full' ?>">
          <label class="field-label" for="<?= $id ?>-ti">Título <span class="req" aria-hidden="true">*</span></label>
          <input class="input" type="text" id="<?= $id ?>-ti" name="titulo" value="<?= e($actual['titulo']) ?>" required>
        </div>
        <?php if ($def['descripcion']): ?>
          <div class="field field--full">
            <label class="field-label" for="<?= $id ?>-de">Descripción</label>
            <textarea class="textarea" id="<?= $id ?>-de" name="descripcion" rows="3"><?= e($actual['descripcion']) ?></textarea>
          </div>
        <?php endif; ?>
      </div>
      <div class="box-card-footer"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar sección</button></div>
    </form>
    <?php
};

// Tabla + botón "Agregar" de una lista repetible; agregar y editar usan el
// mismo modal (#modal-<tipo>, más abajo).
$tablaLista = function (string $tipo, array $L) use ($csrf) {
    $conTema = in_array('tema', array_column($L['campos'], 'name'), true);
    $clavesItem = array_merge(['id'], array_column($L['campos'], 'name'));
    ?>
    <div class="section-header section-header--spaced">
      <div>
        <h2 class="section-heading"><?= e($L['titulo']) ?></h2>
        <p class="section-desc"><?= e($L['desc']) ?></p>
      </div>
      <button type="button" class="btn btn-sm btn-primary" data-item-nuevo="modal-<?= $tipo ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar <?= e($L['singular']) ?></button>
    </div>
    <?php if (!$L['items']): ?>
      <?php ui_empty_state(['icon' => 'inbox', 'title' => 'Sin elementos todavía', 'text' => 'Agrega el primero con el botón de arriba.', 'compact' => true]); ?>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table--stack">
          <thead><tr><th>Elemento</th><th>Detalle</th><?php if ($conTema): ?><th>Tema</th><?php endif; ?><th class="col-num">Orden</th><th class="col-actions"><span class="sr-only">Acciones</span></th></tr></thead>
          <tbody>
            <?php foreach ($L['items'] as $item): ?>
              <tr>
                <td>
                  <span class="landing-item">
                    <span class="landing-item-ic" aria-hidden="true"><i class="bi bi-<?= e($item['icono'] ?: 'dot') ?>"></i></span>
                    <span class="cell-strong"><?= e($item[$L['principal']]) ?></span>
                  </span>
                </td>
                <td class="cell-muted landing-item-desc"><?= e($item[$L['secundario']]) ?></td>
                <?php if ($conTema): ?><td><span class="badge badge-neutral"><?= e($item['tema']) ?></span></td><?php endif; ?>
                <td class="col-num"><?= (int) $item['orden'] ?></td>
                <td class="col-actions">
                  <div class="table-actions">
                    <button type="button" class="btn btn-sm" data-item-editar="modal-<?= $tipo ?>"
                      data-item="<?= e(json_encode(array_intersect_key($item, array_flip($clavesItem)), JSON_UNESCAPED_UNICODE)) ?>">
                      <i class="bi bi-pencil" aria-hidden="true"></i> Editar
                    </button>
                    <form action="<?= BASE_URL ?>/contenido-landing/<?= $tipo ?>/eliminar" method="post" data-confirm="<?= e($L['confirmar']) ?>" data-confirm-text="<?= e($item[$L['principal']]) ?>" data-confirm-ok="Eliminar">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-icon btn-danger-soft" aria-label="Eliminar <?= e($item[$L['principal']]) ?>"><i class="bi bi-trash" aria-hidden="true"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <?php
};
?>

<?php ui_page_header([
    'title'   => 'Contenido de la landing',
    'desc'    => 'Edita lo que ve cualquier visitante en la portada pública, sin iniciar sesión. Los cambios se publican al guardar.',
    'eyebrow' => 'Administración',
    'actions' => function () { ?>
        <a class="btn" href="<?= BASE_URL ?>/" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Ver la portada</a>
    <?php },
]); ?>

<div class="segmented landing-tabs" id="landingTabs" role="tablist" aria-label="Secciones de la portada">
  <?php foreach ($TABS as $tabId => $tabDef): ?>
    <button type="button" class="segmented-item" role="tab" data-tab="<?= e($tabId) ?>" aria-controls="panel-<?= e($tabId) ?>"><i class="bi <?= e($tabDef['icono']) ?>" aria-hidden="true"></i> <?= e($tabDef['label']) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($TABS as $tabId => $tabDef): ?>
  <section class="landing-tab-panel" id="panel-<?= e($tabId) ?>" data-panel="<?= e($tabId) ?>" role="tabpanel" aria-label="<?= e($tabDef['label']) ?>">
    <div class="landing-secciones">
      <?php foreach ($seccionesPorTab($tabId) as $clave => $def) { $tarjetaSeccion($clave, $def); } ?>
    </div>
    <?php foreach ($TAB_DE_LISTA as $tipo => $tabLista) { if ($tabLista === $tabId) $tablaLista($tipo, $LISTAS[$tipo]); } ?>
  </section>
<?php endforeach; ?>

<!-- ── Modales agregar/editar (uno por lista) ── -->
<?php foreach ($LISTAS as $tipo => $L): ?>
<dialog class="modal" id="modal-<?= $tipo ?>" aria-labelledby="modal-<?= $tipo ?>-t" data-singular="<?= e($L['singular']) ?>">
  <form action="<?= BASE_URL ?>/contenido-landing/<?= $tipo ?>/guardar" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <!-- Sin id = crear; con id = actualizar (ver $listasRepetibles en el
         controlador). Se deshabilita al agregar para que no se envíe. -->
    <input type="hidden" name="id" value="" disabled>
    <header class="modal-header">
      <div class="modal-heading"><h2 class="modal-title" id="modal-<?= $tipo ?>-t">Agregar <?= e($L['singular']) ?></h2></div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body">
      <div class="form-grid">
        <?php foreach ($L['campos'] as $c): $cid = $tipo . '-' . $c['name']; $tipoCampo = $c['type'] ?? 'text'; ?>
          <div class="field<?= !empty($c['full']) ? ' field--full' : '' ?>">
            <label class="field-label" for="<?= $cid ?>"><?= e($c['label']) ?><?php if (!empty($c['req'])): ?> <span class="req" aria-hidden="true">*</span><?php endif; ?></label>
            <?php if ($tipoCampo === 'select'): ?>
              <select class="select" id="<?= $cid ?>" name="<?= e($c['name']) ?>" data-default="<?= e($c['def'] ?? '') ?>">
                <?php foreach ($c['opciones'] as $val => $lab): ?>
                  <option value="<?= e($val) ?>" <?= ($c['def'] ?? '') === $val ? 'selected' : '' ?>><?= e($lab) ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($tipoCampo === 'textarea'): ?>
              <textarea class="textarea" id="<?= $cid ?>" name="<?= e($c['name']) ?>" rows="3"<?= !empty($c['req']) ? ' required' : '' ?>></textarea>
            <?php else: ?>
              <input class="input" type="<?= $tipoCampo === 'number' ? 'number' : 'text' ?>" id="<?= $cid ?>" name="<?= e($c['name']) ?>"<?= $tipoCampo === 'number' ? ' value="0" inputmode="numeric"' : '' ?><?= !empty($c['ph']) ? ' placeholder="' . e($c['ph']) . '"' : '' ?><?= !empty($c['req']) ? ' required' : '' ?>>
            <?php endif; ?>
            <?php if (!empty($c['hint'])): ?><p class="field-hint"><?= e($c['hint']) ?></p><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar</button>
    </footer>
  </form>
</dialog>
<?php endforeach; ?>

<script>
  (function () {
    // Pestañas: muestra un panel a la vez y recuerda la última (tras
    // guardar, la recarga vuelve a la misma pestaña, no siempre a "Hero").
    var STORAGE_KEY = 'landingTabActiva';
    var botones = document.querySelectorAll('#landingTabs .segmented-item');
    var paneles = document.querySelectorAll('.landing-tab-panel');
    function activar(tabId) {
      botones.forEach(function (b) {
        var activo = b.dataset.tab === tabId;
        b.classList.toggle('active', activo);
        b.setAttribute('aria-selected', activo ? 'true' : 'false');
      });
      paneles.forEach(function (p) { p.hidden = p.dataset.panel !== tabId; });
      try { localStorage.setItem(STORAGE_KEY, tabId); } catch (e) {}
    }
    botones.forEach(function (b) { b.addEventListener('click', function () { activar(b.dataset.tab); }); });
    var guardada = null;
    try { guardada = localStorage.getItem(STORAGE_KEY); } catch (e) {}
    var existe = guardada && document.querySelector('.landing-tab-panel[data-panel="' + guardada + '"]');
    activar(existe ? guardada : (botones[0] ? botones[0].dataset.tab : null));

    // Agregar/editar con el mismo modal: "Agregar" lo deja vacío (id
    // deshabilitado → el controlador crea); "Editar" lo llena con la fila.
    function prepararModal(dialog, item) {
      var form = dialog.querySelector('form');
      form.reset();
      form.querySelectorAll('select[data-default]').forEach(function (s) { if (s.dataset.default) s.value = s.dataset.default; });
      var idInput = form.querySelector('input[name="id"]');
      idInput.disabled = !item;
      idInput.value = item ? item.id : '';
      if (item) {
        Object.keys(item).forEach(function (k) {
          var el = form.elements[k];
          if (el && k !== 'id' && k !== 'csrf_token') el.value = item[k] === null ? '' : item[k];
        });
      }
      dialog.querySelector('.modal-title').textContent = (item ? 'Editar ' : 'Agregar ') + dialog.dataset.singular;
    }
    document.querySelectorAll('[data-item-nuevo]').forEach(function (b) {
      b.addEventListener('click', function () {
        var d = document.getElementById(b.dataset.itemNuevo);
        prepararModal(d, null);
        UI.open(d, b);
      });
    });
    document.querySelectorAll('[data-item-editar]').forEach(function (b) {
      b.addEventListener('click', function () {
        var d = document.getElementById(b.dataset.itemEditar);
        prepararModal(d, JSON.parse(b.dataset.item));
        UI.open(d, b);
      });
    });
  })();
</script>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
