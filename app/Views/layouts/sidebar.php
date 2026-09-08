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
$navSecciones = [];
$navItemsPorSeccion = [];
$navHijosPorPadre = [];
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

    // Menú del sidebar: nav_secciones agrupa, nav_items son los ítems (con
    // parent_id para anidar sub-ítems propios del menú — hoy ninguno lo usa,
    // pero la estructura ya lo soporta). Aparte de eso, cada ítem que
    // coincide con el slug de una dirección real suma como submenú sus
    // áreas ($areasPorSlug, ver arriba) — mismo mecanismo de siempre, nada
    // nuevo inventado.
    $navSecciones = $pdo->query('SELECT id, label, mostrar_titulo FROM nav_secciones ORDER BY orden')->fetchAll();
    $navItems = $pdo->query('SELECT id, seccion_id, parent_id, slug, ruta, label, icono FROM nav_items ORDER BY orden')->fetchAll();
    foreach ($navItems as $it) {
        if ($it['parent_id'] === null) {
            $navItemsPorSeccion[$it['seccion_id']][] = $it;
        } else {
            $navHijosPorPadre[$it['parent_id']][] = $it;
        }
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

    <?php foreach ($navSecciones as $si => $seccion): ?>
      <?php if ($si > 0): ?><div class="sidebar-divider"></div><?php endif; ?>
      <?php if ($seccion['mostrar_titulo']): ?>
      <div class="sidebar-section-head">
        <span class="sidebar-section-title"><?= e($seccion['label']) ?></span>
      </div>
      <?php endif; ?>
      <div class="sidebar-nav">
        <?php foreach ($navItemsPorSeccion[$seccion['id']] ?? [] as $item): ?>
          <?php
          // Sub-ítems de este ítem: primero los propios del menú (nav_items
          // con parent_id = este ítem — hoy ninguno los usa, pero ya queda
          // servido si se agrega uno), y si el ítem es una dirección real,
          // además sus áreas reales como anclas dentro de la misma página
          // (misma fuente que "Áreas del módulo" — $areasPorSlug, ver
          // arriba). Talento Humano no tiene fila en direccion_areas (su
          // contenido vive en pestañas, no en áreas) — queda como enlace
          // simple, sin submenú de mentira.
          $hijos = $navHijosPorPadre[$item['id']] ?? [];
          $areas = $areasPorSlug[$item['slug']] ?? [];
          $subItems = [];
          $subrutas = [];
          foreach ($hijos as $h) {
            $subItems[] = ['label' => $h['label'], 'href' => BASE_URL . ($h['ruta'] ?: '#')];
            if ($h['ruta']) $subrutas[] = $h['ruta'];
          }
          foreach ($areas as $label) {
            $subItems[] = ['label' => $label, 'href' => BASE_URL . $item['ruta'] . '#' . sb_slug($label)];
            $subrutas[] = $item['ruta'] . '#' . sb_slug($label);
          }
          $ruta = $item['ruta'] ?: '#';
          $activo = $subItems ? sb_grupo_activo($ruta, $subrutas, $rutaActual) : (bool) sb_activo($ruta, $rutaActual);
          $idSub = 'submenu' . $item['id'];
          ?>
          <?php if ($subItems): ?>
          <div class="sidebar-group">
            <a href="#<?= $idSub ?>" class="sidebar-item<?= $activo ? ' active' : '' ?>"
              data-bs-toggle="collapse" role="button"
              aria-expanded="<?= $activo ? 'true' : 'false' ?>" aria-controls="<?= $idSub ?>">
              <i class="bi bi-<?= e($item['icono']) ?>"></i><span class="label"><?= e($item['label']) ?></span>
              <i class="fa-solid fa-chevron-down chevron"></i>
            </a>
            <div class="collapse<?= $activo ? ' show' : '' ?>" id="<?= $idSub ?>">
              <div class="collapse-inner">
                <?php foreach ($subItems as $s): ?>
                  <a href="<?= e($s['href']) ?>" class="sidebar-subitem"><?= e($s['label']) ?></a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php else: ?>
          <a href="<?= BASE_URL . e($ruta) ?>" class="sidebar-item<?= sb_activo($ruta, $rutaActual) ?>">
            <i class="bi bi-<?= e($item['icono']) ?>"></i><span class="label"><?= e($item['label']) ?></span>
          </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

  </div>

  <div class="sidebar-divider"></div>

  <!-- ── Cerrar sesión ── -->
  <div class="sidebar-nav" style="padding: 8px 12px 12px">
    <a href="<?= BASE_URL ?>/logout" class="sidebar-item" id="btnCerrarSesion">
      <i class="fa-solid fa-arrow-right-from-bracket"></i><span class="label">Cerrar sesión</span>
    </a>
  </div>

</aside>