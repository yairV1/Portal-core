<?php
// ══════════════════════════════════════════════════════════
//  app/Views/layouts/portal-header.php
//  Shell del portal logueado: barra superior + panel lateral.
//  Requiere $titulo (opcional) y que $_SESSION['usuario_*']
//  ya exista (viene de auth.php / AuthController). Se cierra
//  con portal-footer.php.
// ══════════════════════════════════════════════════════════

$nombre  = $_SESSION['usuario_nombre'] ?? 'Invitado';
$correo  = $_SESSION['usuario_correo'] ?? '';
$cargo   = $_SESSION['usuario_cargo'] ?? '';
$partes  = explode(' ', trim($nombre));
$inicial = strtoupper(substr($partes[0] ?? 'U', 0, 1) . substr(end($partes) ?: '', 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="theme-color" content="#9E1F63">
<script>
  // Si el navegador restaura esta página desde su caché de "atrás/adelante"
  // (bfcache) — por ejemplo, al volver con el botón "atrás" justo después
  // de cerrar sesión — la vuelve a pedir al servidor en vez de mostrarla
  // tal cual quedó pintada. El Cache-Control: no-store de public/index.php
  // ya evita que la guarde en la mayoría de los casos, pero esto cubre el
  // resto: sin esto, alcanza a verse un instante como si la sesión
  // siguiera activa antes de que la redirección real se complete.
  window.addEventListener('pageshow', function (evento) {
    if (evento.persisted) window.location.reload();
  });
</script>
<title><?= isset($titulo) ? e($titulo) . ' - ' : '' ?>Portal CORE</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/logo/logo-core.jpg">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/layouts/css/paneles.css">
</head>
<body>
<script>
  // Aplica el tema ANTES de pintar la página, para no parpadear (mismo
  // criterio que el estado contraído del sidebar).
  // Si el usuario ya eligió manualmente (botón de luna/sol), se respeta esa
  // elección guardada. Si no, se decide solo por la hora: 06:00–18:00 claro,
  // el resto oscuro (misma regla que paneles.js aplica en vivo).
  (function () {
    try {
      var elegido = localStorage.getItem('tema');
      var tema = (elegido === 'claro' || elegido === 'oscuro')
        ? elegido
        : (new Date().getHours() >= 6 && new Date().getHours() < 18 ? 'claro' : 'oscuro');
      if (tema === 'oscuro') {
        document.body.dataset.tema = 'oscuro';
      }
    } catch (e) {}
  })();
</script>

<header class="topbar">
  <button class="btn btn-icon" id="btnToggleNav" title="Menú">
    <i class="fa-solid fa-bars"></i>
  </button>

  <div class="brand" onclick="location.reload()">
    <div class="brand-logo"><img src="<?= BASE_URL ?>/uploads/logo/logo-core.jpg" alt="Portal CORE"></div>
    <span>
      <span class="brand-name">PORTAL CORE</span>
      <span class="brand-sub">Coreducación</span>
    </span>
  </div>

  <div class="topbar-clock"><i class="fa-regular fa-clock"></i><span id="topbarClock"></span></div>

  <div class="right-actions">
    <button class="btn btn-icon" id="btnTheme" title="Modo claro / oscuro">
      <i class="fa-regular fa-moon icon-claro"></i>
      <i class="fa-regular fa-sun icon-oscuro"></i>
    </button>
    <button class="btn btn-icon" id="btnBell" title="Notificaciones" style="position:relative">
      <i class="fa-regular fa-bell"></i><span class="bell-dot"></span>
    </button>
    <div class="profile" id="btnProfile">
      <span class="avatar"><?= e($inicial) ?></span>
      <span>
        <span class="profile-name"><?= e($nombre) ?></span>
        <span class="profile-role"><?= e($cargo) ?></span>
      </span>
      <i class="fa-solid fa-chevron-down profile-chevron"></i>
    </div>
  </div>
</header>

<div class="profile-drawer-backdrop" id="profileDrawerBackdrop"></div>
<aside class="profile-drawer" id="profileDrawer">
  <button class="profile-drawer-close" id="profileDrawerClose" title="Cerrar">
    <i class="fa-solid fa-xmark"></i>
  </button>

  <div class="profile-drawer-avatar"><?= e($inicial) ?></div>
  <h3 class="profile-drawer-name"><?= e($nombre) ?></h3>
  <p class="profile-drawer-desc">
    <?= e($cargo ?: 'Usuario') ?> · COREDUCACIÓN. Acceso a los tableros de Rectoría, aprobación documental y firma de actos administrativos.
  </p>

  <div class="profile-drawer-actions">
    <button class="btn btn-primary"><i class="fa-solid fa-download"></i> Descargar</button>
    <button class="btn"><i class="fa-solid fa-share-nodes"></i> Compartir</button>
  </div>

  <!-- Datos de ejemplo (Dependencia, Extensión, Perfil de acceso, Sede) —
       todavía no existen esos campos en la base de datos; Cargo y Correo
       ya son reales, vienen de la sesión. -->
  <div class="profile-drawer-fields">
    <div class="profile-drawer-field">
      <span class="label">Cargo</span>
      <span class="value"><?= e($cargo ?: '—') ?></span>
    </div>
    <div class="profile-drawer-field">
      <span class="label">Dependencia</span>
      <span class="value">Rectoría</span>
    </div>
    <div class="profile-drawer-field">
      <span class="label">Correo</span>
      <span class="value"><?= e($correo ?: '—') ?></span>
    </div>
    <div class="profile-drawer-field">
      <span class="label">Extensión</span>
      <span class="value">101</span>
    </div>
    <div class="profile-drawer-field">
      <span class="label">Perfil de acceso</span>
      <span class="value">Directivo · total</span>
    </div>
    <div class="profile-drawer-field">
      <span class="label">Sede</span>
      <span class="value">Honda, Tolima</span>
    </div>
  </div>
</aside>

<div class="breadcrumb-bar">
  <div class="breadcrumb-path">
    <span>Portal CORE</span>
    <?php if (!empty($titulo)): ?>
      <span class="sep">/</span>
      <span class="current"><?= e($titulo) ?></span>
    <?php endif; ?>
  </div>
  <div class="breadcrumb-date" id="breadcrumbDate"></div>
</div>

<div class="app-shell">

  <?php require __DIR__ . '/sidebar.php'; ?>

  <div class="content">