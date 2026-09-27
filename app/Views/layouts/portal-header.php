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
$foto    = $_SESSION['usuario_foto'] ?? '';
// Sede única y real de COREDUCACIÓN (ver la landing pública) — no una
// columna por usuario, es el mismo dato fijo para todos.
$sede    = 'Honda, Tolima';
$partes  = explode(' ', trim($nombre));
if (!function_exists('ui_page_header')) require ROOT_PATH . '/app/Views/components/ui.php';
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
<script>
  // sparkline(el, valores, opts): mini gráfica de línea en SVG, sin
  // librería (todo el proyecto evita dependencias de gráficas). Se define
  // acá arriba —no en paneles.js, que carga al final del body— porque el
  // script propio de cada página (que la llama) corre antes que ese.
  window.sparkline = function (el, valores, opts) {
    if (!el || !valores || valores.length < 2) return;
    opts = opts || {};
    var w = opts.width || 64, h = opts.height || 24;
    var color = opts.color || 'var(--color-accent)';
    var min = Math.min.apply(null, valores), max = Math.max.apply(null, valores);
    var rango = (max - min) || 1;
    var paso = w / (valores.length - 1);
    var puntos = valores.map(function (v, i) {
      var x = i * paso;
      var y = h - ((v - min) / rango) * h;
      return x.toFixed(1) + ',' + y.toFixed(1);
    }).join(' ');
    // opts.fill (opcional): además de la línea, rellena el área debajo
    // con el mismo color a baja opacidad — para KPIs donde la tendencia
    // merece más presencia visual que una línea sola.
    var area = '';
    if (opts.fill) {
      area = '<polygon points="0,' + h + ' ' + puntos + ' ' + w + ',' + h + '" fill="' + color + '" opacity=".14"/>';
    }
    el.innerHTML =
      '<svg viewBox="0 0 ' + w + ' ' + h + '" width="' + w + '" height="' + h + '" preserveAspectRatio="none">' +
        area +
        '<polyline points="' + puntos + '" fill="none" stroke="' + color + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
      '</svg>';
  };

  // tendenciaSintetica(valorTexto): arma 6 puntos crecientes que terminan
  // en el valor mostrado, para dibujar un sparkline sin inventar un
  // histórico "real" en datos de ejemplo — la usan las páginas de módulo.
  window.tendenciaSintetica = function (valorTexto) {
    var n = parseFloat(String(valorTexto).replace(/\./g, '').replace(',', '.')) || 0;
    var base = n * 0.82;
    var paso = (n - base) / 5;
    var puntos = [];
    for (var i = 0; i < 6; i++) puntos.push(base + paso * i);
    return puntos;
  };
</script>
<title><?= isset($titulo) ? e($titulo) . ' - ' : '' ?>Portal CORE</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/logo/favicon-core.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Bootstrap Icons: único set de íconos del Portal (Font Awesome se
     retiró en la Fase 2 del rediseño para no cargar dos librerías). -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet" integrity="sha384-CK2SzKma4jA5H/MXDUU7i1TqZlCFaD4T01vtyDFvPlD97JQyS+IsSh1nI2EFbpyk" crossorigin="anonymous">

<!-- Sistema de diseño (tokens → componentes) y luego el layout del Portal. -->
<link rel="stylesheet" href="<?= v('/assets/core/tokens.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/core/components.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/layouts/css/paneles.css') ?>">
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
  <button type="button" class="btn btn-ghost btn-icon" id="btnToggleNav" aria-label="Mostrar u ocultar el menú" aria-controls="sidebar">
    <i class="bi bi-list" aria-hidden="true"></i>
  </button>

  <a class="brand" href="<?= BASE_URL ?>/" aria-label="Portal CORE — Inicio">
    <span class="brand-logo"><img src="<?= BASE_URL ?>/uploads/logo/logo-core.png" alt=""></span>
    <span>
      <span class="brand-name">PORTAL CORE</span>
      <span class="brand-sub">Coreducación</span>
    </span>
  </a>

  <div class="topbar-clock" aria-hidden="true"><i class="bi bi-clock"></i><span id="topbarClock"></span></div>

  <div class="right-actions">
    <button type="button" class="btn btn-ghost btn-icon" id="btnTheme" aria-label="Cambiar entre modo claro y oscuro">
      <i class="bi bi-moon icon-claro" aria-hidden="true"></i>
      <i class="bi bi-sun icon-oscuro" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-ghost btn-icon btn-bell" id="btnBell" aria-label="Notificaciones">
      <i class="bi bi-bell" aria-hidden="true"></i><span class="bell-dot"></span>
    </button>
    <button type="button" class="profile" id="btnProfile" data-open="profileDrawer" aria-haspopup="dialog" aria-label="Abrir mi perfil">
      <span class="avatar"><?php if ($foto): ?><img src="<?= BASE_URL . e($foto) ?>" alt=""><?php else: ?><?= e($inicial) ?><?php endif; ?></span>
      <span class="profile-text">
        <span class="profile-name"><?= e($nombre) ?></span>
        <span class="profile-role"><?= e($cargo) ?></span>
      </span>
      <i class="bi bi-chevron-down profile-chevron" aria-hidden="true"></i>
    </button>
  </div>
</header>

<!-- ── Mi perfil (drawer del sistema de diseño, ver core/components.css) ──
     Cargo y Sede son de solo lectura a propósito: Cargo lo asigna el
     administrador (si fuera autoeditable, cualquiera podría ponerse un
     cargo falso) y Sede es un dato fijo de la institución. Solo nombre y
     foto se editan acá (ver PerfilController.php). -->
<dialog class="drawer drawer-sm" id="profileDrawer" aria-labelledby="profileDrawerTitle">
  <header class="modal-header">
    <div class="modal-heading">
      <h2 class="modal-title" id="profileDrawerTitle">Mi perfil</h2>
      <p class="modal-desc" id="perfilDesc">Tu información en el Portal CORE.</p>
    </div>
    <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
  </header>

  <div class="modal-body" id="perfilVista">
    <div class="profile-summary">
      <span class="profile-summary-avatar"><?php if ($foto): ?><img src="<?= BASE_URL . e($foto) ?>" alt=""><?php else: ?><?= e($inicial) ?><?php endif; ?></span>
      <div>
        <p class="profile-summary-name"><?= e($nombre) ?></p>
        <p class="profile-summary-role"><?= e($cargo ?: 'Usuario') ?> · COREDUCACIÓN</p>
      </div>
    </div>
    <dl class="profile-fields">
      <div><dt>Cargo</dt><dd><?= e($cargo ?: '—') ?></dd></div>
      <div><dt>Correo</dt><dd><?= e($correo ?: '—') ?></dd></div>
      <div><dt>Sede</dt><dd><?= e($sede) ?></dd></div>
    </dl>
  </div>
  <footer class="modal-footer" id="perfilVistaAcciones">
    <button type="button" class="btn" data-close>Cerrar</button>
    <button type="button" class="btn btn-primary" id="btnEditarPerfil"><i class="bi bi-pencil" aria-hidden="true"></i> Editar perfil</button>
  </footer>

  <form id="perfilForm" action="<?= BASE_URL ?>/perfil" method="post" enctype="multipart/form-data" hidden>
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <div class="modal-body form-stack">
      <div class="profile-photo-edit">
        <span class="profile-summary-avatar profile-drawer-avatar-img"><?php if ($foto): ?><img src="<?= BASE_URL . e($foto) ?>" alt=""><?php else: ?><?= e($inicial) ?><?php endif; ?></span>
        <div class="field">
          <span class="field-label">Foto de perfil</span>
          <label class="btn btn-sm file-btn" for="perfilFoto"><i class="bi bi-camera" aria-hidden="true"></i> Cambiar foto
            <input type="file" id="perfilFoto" name="foto" accept="image/png,image/jpeg,image/webp">
          </label>
          <p class="field-hint">JPG, PNG o WEBP · máximo 2 MB</p>
        </div>
      </div>
      <div class="field">
        <label class="field-label" for="perfilNombre">Nombre <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" id="perfilNombre" name="nombre" value="<?= e($nombre) ?>" required maxlength="100" autocomplete="name">
      </div>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" id="btnCancelarPerfil">Cancelar</button>
      <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </footer>
  </form>
</dialog>

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
