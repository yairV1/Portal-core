<?php
// sidebar.php — incluido desde portal-header.php.

// Resalta como "activo" el ítem cuya ruta coincide con la página actual ($uri
// viene de public/index.php y llega hasta aquí sin cortes, vía require).
function sb_activo(string $ruta, string $actual): string {
    $actual = rtrim($actual, '/') ?: '/';
    $ruta   = rtrim($ruta, '/') ?: '/';
    return $ruta === $actual ? ' active' : '';
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
  (function () {
    try {
      if (localStorage.getItem('sidebarCollapsed') === '1' && window.innerWidth > 880) {
        document.getElementById('sidebar').classList.add('collapsed');
      }
    } catch (e) {}
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
    <div class="sidebar-section-head">
      <span class="sidebar-section-title">Direcciones</span>
    </div>
    <div class="sidebar-nav">
      <a href="<?= BASE_URL ?>/gestion-institucional" class="sidebar-item<?= sb_activo('/gestion-institucional', $rutaActual) ?>">
        <i class="fa-solid fa-building-columns"></i><span class="label">Gestión Institucional</span>
      </a>
      <a href="<?= BASE_URL ?>/sgi" class="sidebar-item<?= sb_activo('/sgi', $rutaActual) ?>">
        <i class="fa-solid fa-folder-tree"></i><span class="label">Sistema de Gestión Integral</span>
      </a>
      <a href="<?= BASE_URL ?>/vicerrectoria-academica" class="sidebar-item<?= sb_activo('/vicerrectoria-academica', $rutaActual) ?>">
        <i class="fa-solid fa-graduation-cap"></i><span class="label">Vicerrectoría Académica</span>
      </a>
      <a href="<?= BASE_URL ?>/administrativa-financiera" class="sidebar-item<?= sb_activo('/administrativa-financiera', $rutaActual) ?>">
        <i class="fa-solid fa-sack-dollar"></i><span class="label">Administrativa y Financiera</span>
      </a>
      <a href="<?= BASE_URL ?>/talento-humano" class="sidebar-item<?= sb_activo('/talento-humano', $rutaActual) ?>">
        <i class="fa-solid fa-users"></i><span class="label">Talento Humano</span>
      </a>
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
    <a href="<?= BASE_URL ?>/logout" class="sidebar-item">
      <i class="fa-solid fa-arrow-right-from-bracket"></i><span class="label">Cerrar sesión</span>
    </a>
  </div>

</aside>
