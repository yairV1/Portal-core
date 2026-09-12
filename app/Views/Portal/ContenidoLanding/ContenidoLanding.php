<?php $titulo = 'Contenido landing'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<h1 class="page-title">Contenido de la landing pública</h1>
<p class="page-desc">
  Esto edita lo que ve cualquier visitante en <a href="<?= BASE_URL ?>/" target="_blank" rel="noopener">la portada pública</a> (sin iniciar sesión) — cámbialo con calma, se actualiza al instante.
</p>

<?php
// Input de una sola línea — repetido para cada campo "titulo"/"etiqueta"/
// "valor"/"icono" de los formularios de abajo, para no repetir el mismo
// <input style="..."> veinte veces.
$campoTexto = function (string $name, string $valor, string $placeholder = '', string $ancho = '1 1 200px') {
    ?>
    <input type="text" name="<?= e($name) ?>" value="<?= e($valor) ?>" placeholder="<?= e($placeholder) ?>"
           style="flex:<?= e($ancho) ?>; padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider)">
    <?php
};

// Todas las claves de LANDING_SECCIONES_DEF que caen en una pestaña dada
// (ver la definición en el controlador — 'tab' agrupa por sección real de
// la portada), en el mismo orden en que están declaradas.
$seccionesPorTab = function (string $tab) {
    return array_filter(LANDING_SECCIONES_DEF, fn ($def) => $def['tab'] === $tab);
};

$TABS = [
    'hero'    => ['label' => 'Hero',          'icono' => 'bi-flag'],
    'porque'  => ['label' => 'Por qué + tarjetas', 'icono' => 'bi-patch-question'],
    'modulos' => ['label' => 'Módulos',       'icono' => 'bi-grid-3x3-gap'],
    'roles'   => ['label' => 'Para tu rol',   'icono' => 'bi-people'],
    'pasos'   => ['label' => 'Cómo empiezas', 'icono' => 'bi-signpost-split'],
];
?>

<div class="pill-tabs" id="landingTabs" style="margin-bottom:22px;">
  <?php foreach ($TABS as $tabId => $tabDef): ?>
    <button type="button" class="pill-tab" data-tab="<?= e($tabId) ?>"><i class="bi <?= e($tabDef['icono']) ?>"></i> <?= e($tabDef['label']) ?></button>
  <?php endforeach; ?>
</div>

<!-- ── Tab: Hero ── -->
<div class="landing-tab-panel" data-panel="hero">
  <?php foreach ($seccionesPorTab('hero') as $clave => $def):
      $actual = $landingSecciones[$clave] ?? ['etiqueta' => '', 'titulo' => '', 'descripcion' => ''];
  ?>
    <div class="box-card" style="margin-bottom:14px; padding:16px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/guardar-seccion" method="post" style="display:flex; flex-direction:column; gap:10px;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="clave" value="<?= e($clave) ?>">
        <strong><?= e($def['nombre']) ?></strong>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <?php if ($def['etiqueta']): ?>
            <?php $campoTexto('etiqueta', $actual['etiqueta'], 'Etiqueta pequeña (kicker)', '1 1 220px'); ?>
          <?php endif; ?>
          <?php $campoTexto('titulo', $actual['titulo'], 'Título', '2 1 320px'); ?>
        </div>
        <?php if ($def['descripcion']): ?>
          <textarea name="descripcion" rows="2" placeholder="Descripción"
                    style="padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider); font-family:inherit;"><?= e($actual['descripcion']) ?></textarea>
        <?php endif; ?>
        <div>
          <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;">
            <i class="bi bi-check-lg"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<!-- ── Tab: Por qué + tarjetas ── -->
<div class="landing-tab-panel" data-panel="porque">
  <?php foreach ($seccionesPorTab('porque') as $clave => $def):
      $actual = $landingSecciones[$clave] ?? ['etiqueta' => '', 'titulo' => '', 'descripcion' => ''];
  ?>
    <div class="box-card" style="margin-bottom:14px; padding:16px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/guardar-seccion" method="post" style="display:flex; flex-direction:column; gap:10px;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="clave" value="<?= e($clave) ?>">
        <strong><?= e($def['nombre']) ?></strong>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <?php if ($def['etiqueta']): ?>
            <?php $campoTexto('etiqueta', $actual['etiqueta'], 'Etiqueta pequeña (kicker)', '1 1 220px'); ?>
          <?php endif; ?>
          <?php $campoTexto('titulo', $actual['titulo'], 'Título', '2 1 320px'); ?>
        </div>
        <?php if ($def['descripcion']): ?>
          <textarea name="descripcion" rows="2" placeholder="Descripción"
                    style="padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider); font-family:inherit;"><?= e($actual['descripcion']) ?></textarea>
        <?php endif; ?>
        <div>
          <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;">
            <i class="bi bi-check-lg"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>

  <div class="section-head" style="margin-top:24px;"><h4>Estadísticas (franja "¿Por qué Portal CORE?")</h4></div>
  <?php foreach ($landingEstadisticas as $item): ?>
    <div class="box-card" style="margin-bottom:10px; padding:14px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/estadistica/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <?php $campoTexto('valor', $item['valor'], 'Cifra (ej: 98%)', '0 0 130px'); ?>
        <?php $campoTexto('descripcion', $item['descripcion'], 'Descripción corta', '1 1 220px'); ?>
        <?php $campoTexto('icono', $item['icono'], 'Ícono Bootstrap (ej: shield-check)', '0 0 170px'); ?>
        <?php $campoTexto('orden', (string) $item['orden'], 'Orden', '0 0 70px'); ?>
        <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;"><i class="bi bi-check-lg"></i></button>
      </form>
      <form action="<?= BASE_URL ?>/contenido-landing/estadistica/eliminar" method="post" onsubmit="return confirm('¿Eliminar esta estadística?')" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <button type="submit" class="tag tag-danger" style="border:none; cursor:pointer; margin-top:8px;"><i class="bi bi-trash"></i> Eliminar</button>
      </form>
    </div>
  <?php endforeach; ?>
  <div class="box-card" style="padding:14px 18px; background:var(--color-bg-subtle, #faf9fb);">
    <form action="<?= BASE_URL ?>/contenido-landing/estadistica/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <?php $campoTexto('valor', '', 'Cifra (ej: 98%)', '0 0 130px'); ?>
      <?php $campoTexto('descripcion', '', 'Descripción corta', '1 1 220px'); ?>
      <?php $campoTexto('icono', '', 'Ícono Bootstrap (ej: shield-check)', '0 0 170px'); ?>
      <?php $campoTexto('orden', '0', 'Orden', '0 0 70px'); ?>
      <button type="submit" class="tag tag-accent" style="border:none; cursor:pointer;"><i class="bi bi-plus-lg"></i> Agregar</button>
    </form>
  </div>
</div>

<!-- ── Tab: Módulos ── -->
<div class="landing-tab-panel" data-panel="modulos">
  <?php foreach ($seccionesPorTab('modulos') as $clave => $def):
      $actual = $landingSecciones[$clave] ?? ['etiqueta' => '', 'titulo' => '', 'descripcion' => ''];
  ?>
    <div class="box-card" style="margin-bottom:14px; padding:16px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/guardar-seccion" method="post" style="display:flex; flex-direction:column; gap:10px;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="clave" value="<?= e($clave) ?>">
        <strong><?= e($def['nombre']) ?></strong>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <?php $campoTexto('titulo', $actual['titulo'], 'Título', '2 1 320px'); ?>
        </div>
        <textarea name="descripcion" rows="2" placeholder="Descripción"
                  style="padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider); font-family:inherit;"><?= e($actual['descripcion']) ?></textarea>
        <div>
          <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;"><i class="bi bi-check-lg"></i> Guardar</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>

  <div class="section-head" style="margin-top:24px;"><h4>Módulos destacados</h4></div>
  <?php foreach ($landingModulos as $item): ?>
    <div class="box-card" style="margin-bottom:10px; padding:14px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/modulo/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <?php $campoTexto('titulo', $item['titulo'], 'Título del módulo', '1 1 180px'); ?>
        <?php $campoTexto('meta', $item['meta'], 'Meta (ej: Módulo académico)', '1 1 180px'); ?>
        <?php $campoTexto('ubicacion', $item['ubicacion'], 'Ubicación (ej: Sidebar → Académico)', '1 1 200px'); ?>
        <?php $campoTexto('estado', $item['estado'], 'Estado (ej: Disponible)', '0 0 140px'); ?>
        <?php $campoTexto('icono', $item['icono'], 'Ícono Bootstrap', '0 0 140px'); ?>
        <select name="tema" style="padding:9px 10px; border-radius:8px; border:1px solid var(--color-divider);">
          <?php foreach (['activo' => 'Activo (magenta)', 'desarrollo' => 'En desarrollo (naranja)', 'futuro' => 'Futuro (gris)'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= $item['tema'] === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
        <?php $campoTexto('orden', (string) $item['orden'], 'Orden', '0 0 70px'); ?>
        <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;"><i class="bi bi-check-lg"></i></button>
      </form>
      <form action="<?= BASE_URL ?>/contenido-landing/modulo/eliminar" method="post" onsubmit="return confirm('¿Eliminar este módulo de la portada?')" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <button type="submit" class="tag tag-danger" style="border:none; cursor:pointer; margin-top:8px;"><i class="bi bi-trash"></i> Eliminar</button>
      </form>
    </div>
  <?php endforeach; ?>
  <div class="box-card" style="padding:14px 18px; background:var(--color-bg-subtle, #faf9fb);">
    <form action="<?= BASE_URL ?>/contenido-landing/modulo/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <?php $campoTexto('titulo', '', 'Título del módulo', '1 1 180px'); ?>
      <?php $campoTexto('meta', '', 'Meta (ej: Módulo académico)', '1 1 180px'); ?>
      <?php $campoTexto('ubicacion', '', 'Ubicación (ej: Sidebar → Académico)', '1 1 200px'); ?>
      <?php $campoTexto('estado', '', 'Estado (ej: Disponible)', '0 0 140px'); ?>
      <?php $campoTexto('icono', '', 'Ícono Bootstrap', '0 0 140px'); ?>
      <select name="tema" style="padding:9px 10px; border-radius:8px; border:1px solid var(--color-divider);">
        <option value="activo">Activo (magenta)</option>
        <option value="desarrollo">En desarrollo (naranja)</option>
        <option value="futuro" selected>Futuro (gris)</option>
      </select>
      <?php $campoTexto('orden', '0', 'Orden', '0 0 70px'); ?>
      <button type="submit" class="tag tag-accent" style="border:none; cursor:pointer;"><i class="bi bi-plus-lg"></i> Agregar</button>
    </form>
  </div>
</div>

<!-- ── Tab: Para tu rol ── -->
<div class="landing-tab-panel" data-panel="roles">
  <?php foreach ($seccionesPorTab('roles') as $clave => $def):
      $actual = $landingSecciones[$clave] ?? ['etiqueta' => '', 'titulo' => '', 'descripcion' => ''];
  ?>
    <div class="box-card" style="margin-bottom:14px; padding:16px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/guardar-seccion" method="post" style="display:flex; flex-direction:column; gap:10px;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="clave" value="<?= e($clave) ?>">
        <strong><?= e($def['nombre']) ?></strong>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <?php $campoTexto('titulo', $actual['titulo'], 'Título', '2 1 320px'); ?>
        </div>
        <textarea name="descripcion" rows="2" placeholder="Descripción"
                  style="padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider); font-family:inherit;"><?= e($actual['descripcion']) ?></textarea>
        <div>
          <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;"><i class="bi bi-check-lg"></i> Guardar</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>

  <div class="section-head" style="margin-top:24px;"><h4>Tarjetas "Para tu rol"</h4></div>
  <?php foreach ($landingRoles as $item): ?>
    <div class="box-card" style="margin-bottom:10px; padding:14px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/rol/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <?php $campoTexto('titulo', $item['titulo'], 'Título (ej: Docentes)', '1 1 180px'); ?>
        <?php $campoTexto('descripcion', $item['descripcion'], 'Descripción', '2 1 260px'); ?>
        <?php $campoTexto('icono', $item['icono'], 'Ícono Bootstrap', '0 0 140px'); ?>
        <select name="tema" style="padding:9px 10px; border-radius:8px; border:1px solid var(--color-divider);">
          <?php foreach (['magenta' => 'Magenta', 'orange' => 'Naranja', 'dark' => 'Oscuro'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= $item['tema'] === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
        <?php $campoTexto('orden', (string) $item['orden'], 'Orden', '0 0 70px'); ?>
        <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;"><i class="bi bi-check-lg"></i></button>
      </form>
      <form action="<?= BASE_URL ?>/contenido-landing/rol/eliminar" method="post" onsubmit="return confirm('¿Eliminar esta tarjeta de rol?')" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <button type="submit" class="tag tag-danger" style="border:none; cursor:pointer; margin-top:8px;"><i class="bi bi-trash"></i> Eliminar</button>
      </form>
    </div>
  <?php endforeach; ?>
  <div class="box-card" style="padding:14px 18px; background:var(--color-bg-subtle, #faf9fb);">
    <form action="<?= BASE_URL ?>/contenido-landing/rol/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <?php $campoTexto('titulo', '', 'Título (ej: Docentes)', '1 1 180px'); ?>
      <?php $campoTexto('descripcion', '', 'Descripción', '2 1 260px'); ?>
      <?php $campoTexto('icono', '', 'Ícono Bootstrap', '0 0 140px'); ?>
      <select name="tema" style="padding:9px 10px; border-radius:8px; border:1px solid var(--color-divider);">
        <option value="magenta" selected>Magenta</option>
        <option value="orange">Naranja</option>
        <option value="dark">Oscuro</option>
      </select>
      <?php $campoTexto('orden', '0', 'Orden', '0 0 70px'); ?>
      <button type="submit" class="tag tag-accent" style="border:none; cursor:pointer;"><i class="bi bi-plus-lg"></i> Agregar</button>
    </form>
  </div>
</div>

<!-- ── Tab: Cómo empiezas ── -->
<div class="landing-tab-panel" data-panel="pasos">
  <?php foreach ($seccionesPorTab('pasos') as $clave => $def):
      $actual = $landingSecciones[$clave] ?? ['etiqueta' => '', 'titulo' => '', 'descripcion' => ''];
  ?>
    <div class="box-card" style="margin-bottom:14px; padding:16px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/guardar-seccion" method="post" style="display:flex; flex-direction:column; gap:10px;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="clave" value="<?= e($clave) ?>">
        <strong><?= e($def['nombre']) ?></strong>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <?php $campoTexto('titulo', $actual['titulo'], 'Título', '2 1 320px'); ?>
        </div>
        <div>
          <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;"><i class="bi bi-check-lg"></i> Guardar</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>

  <div class="section-head" style="margin-top:24px;"><h4>Pasos ("Cómo empiezas")</h4></div>
  <?php foreach ($landingPasos as $item): ?>
    <div class="box-card" style="margin-bottom:10px; padding:14px 18px;">
      <form action="<?= BASE_URL ?>/contenido-landing/paso/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <?php $campoTexto('titulo', $item['titulo'], 'Título del paso', '1 1 180px'); ?>
        <?php $campoTexto('descripcion', $item['descripcion'], 'Descripción', '2 1 260px'); ?>
        <?php $campoTexto('icono', $item['icono'], 'Ícono Bootstrap', '0 0 140px'); ?>
        <?php $campoTexto('orden', (string) $item['orden'], 'Orden', '0 0 70px'); ?>
        <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer;"><i class="bi bi-check-lg"></i></button>
      </form>
      <form action="<?= BASE_URL ?>/contenido-landing/paso/eliminar" method="post" onsubmit="return confirm('¿Eliminar este paso?')" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <button type="submit" class="tag tag-danger" style="border:none; cursor:pointer; margin-top:8px;"><i class="bi bi-trash"></i> Eliminar</button>
      </form>
    </div>
  <?php endforeach; ?>
  <div class="box-card" style="padding:14px 18px; background:var(--color-bg-subtle, #faf9fb); margin-bottom:32px;">
    <form action="<?= BASE_URL ?>/contenido-landing/paso/guardar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <?php $campoTexto('titulo', '', 'Título del paso', '1 1 180px'); ?>
      <?php $campoTexto('descripcion', '', 'Descripción', '2 1 260px'); ?>
      <?php $campoTexto('icono', '', 'Ícono Bootstrap', '0 0 140px'); ?>
      <?php $campoTexto('orden', '0', 'Orden', '0 0 70px'); ?>
      <button type="submit" class="tag tag-accent" style="border:none; cursor:pointer;"><i class="bi bi-plus-lg"></i> Agregar</button>
    </form>
  </div>
</div>

<p class="text-muted" style="font-size:12px;">
  Los nombres de ícono son de <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a> — escribe solo el nombre (ej. <code>shield-check</code>), sin "bi-" ni comillas.
</p>

<script>
  // Pestañas simples: muestra un panel a la vez, sin recargar. Recuerda la
  // última pestaña abierta en localStorage (mismo patrón que el colapso del
  // sidebar) para que, tras guardar un cambio (recarga completa por el
  // POST normal), la página vuelva a abrir en la misma pestaña donde
  // estabas — no siempre en "Hero".
  (function () {
    var STORAGE_KEY = 'landingTabActiva';
    var botones = document.querySelectorAll('#landingTabs .pill-tab');
    var paneles = document.querySelectorAll('.landing-tab-panel');

    function activar(tabId) {
      botones.forEach(function (b) { b.classList.toggle('active', b.dataset.tab === tabId); });
      paneles.forEach(function (p) { p.hidden = p.dataset.panel !== tabId; });
      try { localStorage.setItem(STORAGE_KEY, tabId); } catch (e) {}
    }

    botones.forEach(function (b) {
      b.addEventListener('click', function () { activar(b.dataset.tab); });
    });

    var guardada = null;
    try { guardada = localStorage.getItem(STORAGE_KEY); } catch (e) {}
    var existe = guardada && document.querySelector('.landing-tab-panel[data-panel="' + guardada + '"]');
    activar(existe ? guardada : (botones[0] ? botones[0].dataset.tab : null));
  })();
</script>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
