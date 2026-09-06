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
      $subGestion = ['/gestion-institucional/mejoras'];
      $activoGestion = sb_grupo_activo('/gestion-institucional', $subGestion, $rutaActual);
      ?>
      <div class="sidebar-group">
        <a href="#submenuGestion" class="sidebar-item<?= $activoGestion ? ' active' : '' ?>"
          data-bs-toggle="collapse" role="button"
          aria-expanded="<?= $activoGestion ? 'true' : 'false' ?>" aria-controls="submenuGestion">
          <i class="fa-solid fa-building-columns"></i><span class="label">Gestión Institucional</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </a>
        <div class="collapse<?= $activoGestion ? ' show' : '' ?>" id="submenuGestion">
          <a href="<?= BASE_URL ?>/gestion-institucional/mejoras"
            class="sidebar-subitem<?= sb_activo('/gestion-institucional/mejoras', $rutaActual) ?>">Mejoras</a>
        </div>
      </div>

      <?php
      $subSGI = ['/sgi/mejoras'];
      $activoSGI = sb_grupo_activo('/sgi', $subSGI, $rutaActual);
      ?>
      <div class="sidebar-group">
        <a href="#submenuSGI" class="sidebar-item<?= $activoSGI ? ' active' : '' ?>"
          data-bs-toggle="collapse" role="button"
          aria-expanded="<?= $activoSGI ? 'true' : 'false' ?>" aria-controls="submenuSGI">
          <i class="fa-solid fa-folder-tree"></i><span class="label">Sistema de Gestión Integral</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </a>
        <div class="collapse<?= $activoSGI ? ' show' : '' ?>" id="submenuSGI">
          <a href="<?= BASE_URL ?>/sgi/mejoras"
            class="sidebar-subitem<?= sb_activo('/sgi/mejoras', $rutaActual) ?>">Mejoras</a>
        </div>
      </div>

      <?php
      $subVicerrectoria = ['/vicerrectoria-academica/mejoras'];
      $activoVicerrectoria = sb_grupo_activo('/vicerrectoria-academica', $subVicerrectoria, $rutaActual);
      ?>
      <div class="sidebar-group">
        <a href="#submenuVicerrectoria" class="sidebar-item<?= $activoVicerrectoria ? ' active' : '' ?>"
          data-bs-toggle="collapse" role="button"
          aria-expanded="<?= $activoVicerrectoria ? 'true' : 'false' ?>" aria-controls="submenuVicerrectoria">
          <i class="fa-solid fa-graduation-cap"></i><span class="label">Vicerrectoría Académica</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </a>
        <div class="collapse<?= $activoVicerrectoria ? ' show' : '' ?>" id="submenuVicerrectoria">
          <a href="<?= BASE_URL ?>/vicerrectoria-academica/mejoras"
            class="sidebar-subitem<?= sb_activo('/vicerrectoria-academica/mejoras', $rutaActual) ?>">Mejoras</a>
        </div>
      </div>

      <?php
      $subAdmin = ['/administrativa-financiera/mejoras'];
      $activoAdmin = sb_grupo_activo('/administrativa-financiera', $subAdmin, $rutaActual);
      ?>
      <div class="sidebar-group">
        <a href="#submenuAdmin" class="sidebar-item<?= $activoAdmin ? ' active' : '' ?>"
          data-bs-toggle="collapse" role="button"
          aria-expanded="<?= $activoAdmin ? 'true' : 'false' ?>" aria-controls="submenuAdmin">
          <i class="fa-solid fa-sack-dollar"></i><span class="label">Administrativa y Financiera</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </a>
        <div class="collapse<?= $activoAdmin ? ' show' : '' ?>" id="submenuAdmin">
          <a href="<?= BASE_URL ?>/administrativa-financiera/mejoras"
            class="sidebar-subitem<?= sb_activo('/administrativa-financiera/mejoras', $rutaActual) ?>">Mejoras</a>
        </div>
      </div>

      <?php
      $subTalento = ['/talento-humano/mejoras'];
      $activoTalento = sb_grupo_activo('/talento-humano', $subTalento, $rutaActual);
      ?>
      <div class="sidebar-group">
        <a href="#submenuTalento" class="sidebar-item<?= $activoTalento ? ' active' : '' ?>"
          data-bs-toggle="collapse" role="button"
          aria-expanded="<?= $activoTalento ? 'true' : 'false' ?>" aria-controls="submenuTalento">
          <i class="fa-solid fa-users"></i><span class="label">Talento Humano</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </a>
        <div class="collapse<?= $activoTalento ? ' show' : '' ?>" id="submenuTalento">
          <a href="<?= BASE_URL ?>/talento-humano/mejoras"
            class="sidebar-subitem<?= sb_activo('/talento-humano/mejoras', $rutaActual) ?>">Mejoras</a>
        </div>
      </div>

      <?php
      $subInvestigacion = ['/investigacion-innovacion/mejoras'];
      $activoInvestigacion = sb_grupo_activo('/investigacion-innovacion', $subInvestigacion, $rutaActual);
      ?>

      <a href="<?= BASE_URL ?>/investigacion-innovacion" class="sidebar-item<?= sb_activo('/investigacion-innovacion', $rutaActual) ?>">
        <i class="fa-solid fa-lightbulb"></i><span class="label">Investigación e Innovación</span> 
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