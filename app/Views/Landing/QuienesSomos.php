<?php
/**
 * Landing pública "Quiénes somos" — sin sesión. $direccionesInfo,
 * $directivos, $responsablesPorDireccion vienen de
 * QuienesSomosController.php (todo real, nada quemado). $csrf/BASE_URL/
 * e()/v() ya vienen listos desde public/index.php.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quiénes somos — Portal Core COREDUCACIÓN</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= v('/assets/web/css/inicio.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/web/css/quienes-somos.css') ?>">
</head>
<body>

<nav class="navbar">
  <div class="container nav-row">
    <a href="<?= BASE_URL ?>/" class="brand">
      <svg width="26" height="21" viewBox="0 0 60 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs><linearGradient id="mg" x1="0" y1="0" x2="60" y2="48" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#F15A29"/><stop offset="1" stop-color="#9E1F63"/></linearGradient></defs>
        <path d="M6 6 L30 20 L30 28 L6 42 Z" fill="url(#mg)"/>
        <path d="M34 4 L58 18" stroke="url(#mg)" stroke-width="7" stroke-linecap="round"/>
        <path d="M34 24 L58 24" stroke="url(#mg)" stroke-width="7" stroke-linecap="round"/>
        <path d="M34 44 L58 30" stroke="url(#mg)" stroke-width="7" stroke-linecap="round"/>
      </svg>
      <strong>Portal Core</strong>
    </a>
    <nav class="links" aria-label="Navegación principal">
      <a href="#modulos">El portal</a>
      <a href="#directivos">Directivos</a>
      <a href="#encargados">Encargados</a>
    </nav>
    <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-dark">Iniciar sesión</a>
  </div>
</nav>

<section class="qs-hero">
  <canvas id="qsCanvas" class="qs-canvas" aria-hidden="true"></canvas>
  <div class="container qs-hero-content">
    <span class="hero-kicker qs-fade">Portal institucional de COREDUCACIÓN</span>
    <h1 class="qs-titulo qs-fade">Quiénes somos</h1>
    <p class="hero-desc qs-fade">COREDUCACIÓN es una institución educativa con sede en Honda, Tolima. Portal Core es el sistema que organiza su información, procesos y personas en un solo lugar — así está construido por dentro.</p>
  </div>
</section>

<section class="qs-modulos" id="modulos">
  <div class="container">
    <div class="modules-head">
      <div><h2>Cómo está organizado el portal</h2></div>
      <p>Cada dirección institucional tiene su propio espacio dentro de Portal Core.</p>
    </div>
    <div class="qs-modulos-grid">
      <?php foreach ($direccionesInfo as $d): ?>
        <div class="qs-tilt qs-reveal">
          <div class="qs-modulo-card">
            <span class="qs-modulo-kicker"><?= e($d['kicker']) ?></span>
            <h3><?= e($d['titulo']) ?></h3>
            <p><?= e($d['descripcion']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="qs-directivos" id="directivos">
  <div class="container">
    <div class="modules-head">
      <div><h2>Nuestros directivos</h2></div>
      <p>Quiénes lideran cada dirección institucional hoy.</p>
    </div>
    <div class="qs-personas-grid">
      <?php foreach ($directivos as $p): ?>
        <div class="qs-tilt qs-reveal">
          <div class="qs-persona-card">
            <div class="qs-avatar">
              <?php if ($p['foto']): ?>
                <img src="<?= BASE_URL . e($p['foto']) ?>" alt="<?= e($p['nombre'] ?: $p['cargo']) ?>">
              <?php else: ?>
                <i class="bi bi-person-fill"></i>
              <?php endif; ?>
            </div>
            <strong><?= $p['nombre'] ? e($p['nombre']) : 'Vacante' ?></strong>
            <span><?= e($p['cargo']) ?></span>
            <span class="qs-persona-direccion"><?= e($p['direccion']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="qs-encargados" id="encargados">
  <div class="container">
    <div class="modules-head">
      <div><h2>Encargados por área</h2></div>
      <p>El equipo detrás de cada dirección.</p>
    </div>
    <?php foreach ($responsablesPorDireccion as $direccionTitulo => $personas): ?>
      <h4 class="qs-area-titulo qs-reveal"><?= e($direccionTitulo) ?></h4>
      <div class="qs-personas-grid qs-personas-grid-sm">
        <?php foreach ($personas as $p): ?>
          <div class="qs-tilt qs-reveal">
            <div class="qs-persona-card qs-persona-card-sm">
              <div class="qs-avatar qs-avatar-sm">
                <?php if ($p['foto']): ?>
                  <img src="<?= BASE_URL . e($p['foto']) ?>" alt="<?= e($p['nombre']) ?>">
                <?php else: ?>
                  <i class="bi bi-person-fill"></i>
                <?php endif; ?>
              </div>
              <strong><?= e($p['nombre']) ?></strong>
              <span><?= e($p['cargo']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= v('/assets/web/js/quienes-somos.js') ?>"></script>
</body>
</html>
