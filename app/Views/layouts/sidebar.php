<?php
// sidebar.php — incluido desde portal-header.php.

// Resalta como "activo" el ítem cuya ruta coincide con la página actual ($uri
// viene de public/index.php y llega hasta aquí sin cortes, vía require).
function sb_activo(string $ruta, string $actual): string
{
  $actual = rtrim($actual, '/') ?: '/';
  $ruta   = rtrim($ruta, '/') ?: '/';
  return $ruta === $actual ? ' active' : '';
}

// Marca el padre de un grupo como activo si la ruta actual es el padre
// o alguna de sus subrutas (ej. "/gestion-institucional/mejoras").
function sb_grupo_activo(string $rutaPadre, array $subrutas, string $rutaActual): bool
{
  return $rutaActual === $rutaPadre || in_array($rutaActual, $subrutas, true);
}

$rutaActual = $uri ?? '';

// Áreas reales por dirección (mismas que ya se muestran en "Áreas del
// módulo" de cada página — ver PortalController.php/direccion_areas),
// para armar los submenús del sidebar sin inventar contenido nuevo. Un
// slug helper simple para el ancla (#area-slug) — sin librería, es solo
// para bajar a esa área dentro de la misma página.
function sb_slug(string $texto): string
{
    $texto = strtolower($texto);
    $texto = strtr($texto, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $texto), '-');
}

$areasPorSlug = [];
if (isset($pdo)) {
    $filas = $pdo->query('
        SELECT d.slug, a.label
        FROM direccion_areas a
        JOIN direcciones d ON d.id = a.direccion_id
        ORDER BY d.id, a.orden
    ')->fetchAll();
    foreach ($filas as $f) {
        $areasPorSlug[$f['slug']][] = $f['label'];
    }
}
?>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<aside class="sidebar" id="sidebar">

  <script>
    // Aplica el estado contraído/expandido ANTES de pintar la página, para que
    // no se vea un parpadeo (expandido un instante y luego contraído).
    // La preferencia "contraído a íconos" es solo de escritorio: por debajo de
    // 880px el sidebar ya es un panel deslizante (ver paneles.css/paneles.js),
    // así que ahí no se aplica aunque esté guardada.
    (function() {
      var sidebar = document.getElementById('sidebar');
      if (!sidebar) return;
      try {
        var pref = localStorage.getItem('sidebarCollapsed');
        var ancho = window.innerWidth;
        // Sin preferencia guardada todavía (primera visita) y en rango
        // tablet (881-1279px): arranca contraído por defecto, como mejor
        // uso del espacio — nunca pisa una preferencia explícita del
        // usuario ("0" se respeta aunque esté en ese rango).
        var esTabletSinPreferencia = pref === null && ancho > 880 && ancho <= 1279;
        if (ancho > 880 && (pref === '1' || esTabletSinPreferencia)) {
          sidebar.classList.add('collapsed');
        }
      } catch (e) {}

      // Cada clic en el menú recarga la página completa (esto no es una SPA),
      // así que este mismo bloque corre en CADA navegación. Sin esto, la
      // transición de ancho/etiquetas del panel (pensada solo para cuando el
      // usuario lo colapsa/expande a mano) también se dispara acá, y se ve
      // como un parpadeo entre expandido y contraído al cambiar de página.
      // Se apaga con "no-anim" y se reactiva recién después del primer pintado.
      sidebar.classList.add('no-anim');
      requestAnimationFrame(function() {
        requestAnimationFrame(function() {
          sidebar.classList.remove('no-anim');
        });
      });
    })();
  </script>

  <div class="sidebar-scroll">

    <!-- ── Principal ── -->
    <div class="sidebar-nav">
      <a href="<?= BASE_URL ?>/" class="sidebar-item<?= sb_activo('/', $rutaActual) ?>">
        <i class="fa-solid fa-house"></i><span class="label">Inicio</span>
      </a>
      <a href="<?= BASE_URL ?>/tableros" class="sidebar-item<?= sb_activo('/tableros', $rutaActual) ?>">
        <i class="fa-solid fa-chart-line"></i><span class="label">Tableros Estratégicos</span>
      </a>
      <a href="<?= BASE_URL ?>/mapa-portal" class="sidebar-item<?= sb_activo('/mapa-portal', $rutaActual) ?>">
        <i class="fa-solid fa-map"></i><span class="label">Mapa del portal</span>
      </a>
    </div>

    <div class="sidebar-divider"></div>

    <!-- ── Direcciones (equivalente a "Shared" del mockup) ── -->
    <div class="sidebar-nav">

      <?php
      // Un grupo por dirección, con sus áreas reales como submenú (misma
      // fuente que "Áreas del módulo" de cada página — $areasPorSlug, ver
      // arriba). Cada sub-ítem baja hasta esa área en la propia página
      // (#ancla), nada de rutas nuevas inventadas. Talento Humano no tiene
      // fila en direccion_areas (su contenido vive en pestañas, no en
      // áreas) — se deja como enlace simple, sin submenú de mentira.
      $direcciones = [
        ['ruta' => '/gestion-institucional',     'icono' => 'fa-building-columns', 'label' => 'Gestión Institucional',       'slug' => 'institucional'],
        ['ruta' => '/sgi',                       'icono' => 'fa-folder-tree',      'label' => 'Sistema de Gestión Integral', 'slug' => 'sgi'],
        ['ruta' => '/vicerrectoria-academica',   'icono' => 'fa-graduation-cap',   'label' => 'Vicerrectoría Académica',     'slug' => 'academica'],
        ['ruta' => '/administrativa-financiera', 'icono' => 'fa-sack-dollar',      'label' => 'Administrativa y Financiera', 'slug' => 'financiera'],
        ['ruta' => '/investigacion-innovacion',  'icono' => 'fa-lightbulb',        'label' => 'Investigación e Innovación',  'slug' => 'investigacion'],
      ];
      foreach ($direcciones as $i => $d):
        $items = $areasPorSlug[$d['slug']] ?? [];
        $subrutas = array_map(fn($label) => $d['ruta'] . '#' . sb_slug($label), $items);
        $activo = sb_grupo_activo($d['ruta'], $subrutas, $rutaActual);
        $idSub = 'submenu' . $i;
      ?>
      <div class="sidebar-group">
        <a href="#<?= $idSub ?>" class="sidebar-item<?= $activo ? ' active' : '' ?>"
          data-bs-toggle="collapse" role="button"
          aria-expanded="<?= $activo ? 'true' : 'false' ?>" aria-controls="<?= $idSub ?>">
          <i class="fa-solid <?= e($d['icono']) ?>"></i><span class="label"><?= e($d['label']) ?></span>
          <?php if ($items): ?><i class="fa-solid fa-chevron-down chevron"></i><?php endif; ?>
        </a>
        <?php if ($items): ?>
        <div class="collapse<?= $activo ? ' show' : '' ?>" id="<?= $idSub ?>">
          <div class="collapse-inner">
            <?php foreach ($items as $label): ?>
              <a href="<?= BASE_URL . e($d['ruta']) ?>#<?= e(sb_slug($label)) ?>" class="sidebar-subitem"><?= e($label) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>

      <a href="<?= BASE_URL ?>/talento-humano" class="sidebar-item<?= sb_activo('/talento-humano', $rutaActual) ?>">
        <i class="fa-solid fa-users"></i><span class="label">Talento Humano</span>
      </a>

    </div>

    <div class="sidebar-divider"></div>

    <!-- ── Recursos ── -->
    <div class="sidebar-section-head">
      <span class="sidebar-section-title">Recursos</span>
    </div>
    <div class="sidebar-nav">
      <a href="<?= BASE_URL ?>/gestion-documental" class="sidebar-item<?= sb_activo('/gestion-documental', $rutaActual) ?>">
        <i class="fa-solid fa-folder-open"></i><span class="label">Gestión Documental</span>
      </a>
      <a href="<?= BASE_URL ?>/normatividad" class="sidebar-item<?= sb_activo('/normatividad', $rutaActual) ?>">
        <i class="fa-solid fa-scale-balanced"></i><span class="label">Normatividad</span>
      </a>
      <a href="<?= BASE_URL ?>/novedades" class="sidebar-item<?= sb_activo('/novedades', $rutaActual) ?>">
        <i class="fa-solid fa-newspaper"></i><span class="label">Novedades</span>
      </a>
      <a href="<?= BASE_URL ?>/aplicaciones" class="sidebar-item<?= sb_activo('/aplicaciones', $rutaActual) ?>">
        <i class="fa-solid fa-grip"></i><span class="label">Aplicaciones</span>
      </a>
    </div>

  </div>

  <div class="sidebar-divider"></div>

  <!-- ── Cerrar sesión ── -->
  <div class="sidebar-nav" style="padding: 8px 12px 12px">
    <a href="<?= BASE_URL ?>/logout" class="sidebar-item" id="btnCerrarSesion">
      <i class="fa-solid fa-arrow-right-from-bracket"></i><span class="label">Cerrar sesión</span>
    </a>
  </div>

</aside>